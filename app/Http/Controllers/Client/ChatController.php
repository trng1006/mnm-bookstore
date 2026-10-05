<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use App\Models\Book;

class ChatController extends Controller
{
    public function respond(Request $request)
    {
        $message = $request->input('message');

        if (!$message) {
            return response()->json(['reply' => 'Xin lỗi, tôi chưa nhận được tin nhắn của bạn.']);
        }

        // Lấy dữ liệu sách từ Database để đưa cho AI làm ngữ cảnh (Context)
        $books = Book::with(['author', 'category'])->get();
        $bookContext = "DANH SÁCH SÁCH TRONG CỬA HÀNG:\n";
        foreach ($books as $book) {
            $authorName = $book->author ? $book->author->name : 'Chưa rõ';
            $categoryName = $book->category ? $book->category->name : 'Chưa rõ';
            $price = number_format($book->price_current, 0, ',', '.') . ' VNĐ';
            $bookContext .= "- \"{$book->title}\" | Tác giả: {$authorName} | Thể loại: {$categoryName} | Giá: {$price} | Tồn kho: {$book->stock} quyển\n";
        }

        // System Prompt định hình tính cách và giới hạn của AI
        $systemPrompt = "Bạn là nhân viên tư vấn sách của MNM Bookstore. \n";
        $systemPrompt .= "QUY TẮC NGHIÊM NGẶT CỦA BẠN:\n";
        $systemPrompt .= "1. BẠN CHỈ ĐƯỢC PHÉP trả lời các thông tin liên quan đến MNM Bookstore và các cuốn sách có trong DANH SÁCH SÁCH bên dưới.\n";
        $systemPrompt .= "2. Nếu khách hỏi những chủ đề KHÔNG LIÊN QUAN đến sách hoặc cửa hàng (ví dụ: toán học, lập trình, chính trị, thời tiết, tư vấn tình cảm...), bạn PHẢI TỪ CHỐI khéo léo và lái câu chuyện về sách.\n";
        $systemPrompt .= "3. Trả lời ngắn gọn, súc tích, thân thiện bằng tiếng Việt. Tuyệt đối không bịa đặt thông tin không có trong danh sách.\n\n";
        $systemPrompt .= $bookContext . "\n\n";

        // Lấy lịch sử chat từ Session
        $history = Session::get('chat_history', []);
        
        // Cấu trúc history theo chuẩn của Gemini: role (user/model), parts -> text
        $history[] = [
            'role' => 'user',
            'parts' => [['text' => $message]]
        ];

        // Giữ lại 10 tin nhắn gần nhất để không bị đầy bộ nhớ
        if (count($history) > 10) {
            $history = array_slice($history, -10);
        }

        /* 
         * TÙY CHỌN 2: Dùng API (Gemini/OpenAI)
         */
        $apiKey = env('GEMINI_API_KEY');
        if ($apiKey) {
            try {
                $payload = [
                    'systemInstruction' => [
                        'parts' => [['text' => $systemPrompt]]
                    ],
                    'contents' => $history
                ];

                // Dùng model gemini-flash-lite-latest để chạy nhanh hơn và ít bị lỗi 503 (High Demand)
                $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent?key={$apiKey}", $payload);

                if ($response->successful()) {
                    $reply = $response->json('candidates.0.content.parts.0.text');
                    
                    // Thêm câu trả lời của AI vào lịch sử
                    $history[] = [
                        'role' => 'model',
                        'parts' => [['text' => $reply]]
                    ];
                    Session::put('chat_history', $history);

                    return response()->json(['reply' => $reply]);
                } else {
                    // Xóa tin nhắn user vừa gửi nếu API lỗi để không làm hỏng chuỗi hội thoại
                    array_pop($history);
                }
            } catch (\Exception $e) {
                array_pop($history);
            }
        }

        /* 
         * TÙY CHỌN 3: Fallback Logic Cơ Bản (Regex/Keywords)
         */
        $messageLower = mb_strtolower($message, 'UTF-8');
        $reply = "Xin lỗi, hiện tại tôi chưa hiểu ý bạn. Bạn có thể liên hệ hotline để được tư vấn thêm.";

        if (str_contains($messageLower, 'xin chào') || str_contains($messageLower, 'hi') || str_contains($messageLower, 'hello')) {
            $reply = "Xin chào! MNM Bookstore có thể giúp gì cho bạn?";
        } elseif (str_contains($messageLower, 'sách') || str_contains($messageLower, 'mua') || str_contains($messageLower, 'tìm')) {
            $reply = "Cửa hàng đang có rất nhiều đầu sách hay và chương trình Freeship cho đơn từ 300k. Bạn đang tìm thể loại nào?";
        } elseif (str_contains($messageLower, 'giá') || str_contains($messageLower, 'nhiêu')) {
            $reply = "Giá sách được niêm yết rõ trên từng sản phẩm. Hiện tại đang có mã giảm giá GIAM10 đó ạ!";
        } elseif (str_contains($messageLower, 'tạm biệt') || str_contains($messageLower, 'cảm ơn') || str_contains($messageLower, 'bye')) {
            $reply = "Cảm ơn bạn! Chúc bạn một ngày tốt lành.";
        }

        return response()->json(['reply' => $reply]);
    }
}
