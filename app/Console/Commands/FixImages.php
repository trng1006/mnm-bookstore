<?php

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;

class FixImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix book cover images';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $images = [
            'nha-gia-kim' => 'https://covers.openlibrary.org/b/isbn/9780062315007-L.jpg',
            'rung-na-uy' => 'https://covers.openlibrary.org/b/isbn/9780375704024-L.jpg',
            'cay-cam-ngot-cua-toi' => 'https://covers.openlibrary.org/b/isbn/9780763690753-L.jpg',
            'su-im-lang-cua-bay-cuu' => 'https://covers.openlibrary.org/b/isbn/9780312924584-L.jpg',
            'bong-hinh-cua-gio' => 'https://covers.openlibrary.org/b/isbn/9780143034902-L.jpg',
            'muon-kiep-nhan-sinh' => 'https://covers.openlibrary.org/b/isbn/9780735211292-L.jpg',
            'toi-ac-va-hinh-phat' => 'https://covers.openlibrary.org/b/isbn/9780140449136-L.jpg',
            'chuyen-con-meo-day-hai-au-bay' => 'https://covers.openlibrary.org/b/isbn/9780143119395-L.jpg',
        ];

        foreach (Book::all() as $book) {
            if (isset($images[$book->slug])) {
                $book->cover_image = $images[$book->slug];
            } else {
                $book->cover_image = 'https://placehold.co/400x600/e2e8f0/1e293b?text='.urlencode($book->title);
            }
            $book->save();
        }

        $this->info('Images updated successfully!');
    }
}
