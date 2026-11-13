# MNM Bookstore (Đồ án Lập trình Mã nguồn mở)

MNM Bookstore là hệ thống website bán sách trực tuyến xây dựng trên Laravel theo mô hình MVC, áp dụng Eloquent ORM, Session/Cookie, AJAX (Fetch API), phân quyền RBAC và tích hợp thanh toán VietQR qua cổng payOS.

## Công nghệ sử dụng

- **Backend:** PHP >= 8.3, Laravel Framework 13, Eloquent ORM
- **Phân quyền & Tiện ích:** `spatie/laravel-permission`, `barryvdh/laravel-dompdf`, `owen-it/laravel-auditing`, `laravel/sanctum`
- **Frontend:** Blade Template, Tailwind CSS (Vite), Vanilla JavaScript (Fetch API), thư viện `qrcode`
- **Cơ sở dữ liệu:** SQLite (mặc định cho môi trường Dev/Demo) hoặc MySQL

## Chức năng chính

### Phân hệ Khách hàng (Client)
- **Trang chủ & Duyệt sách:** Banner nổi bật, sách bán chạy theo danh mục, sách mới, lọc theo danh mục/tác giả/giá/sắp xếp, tìm kiếm gợi ý AJAX, phân trang mượt.
- **Chi tiết sách & Đánh giá:** Thông tin ấn phẩm, kiểm tra tồn kho thực tế, sách liên quan; chỉ khách hàng đã mua và hoàn thành đơn mới được gửi đánh giá.
- **Giỏ hàng & Mã giảm giá:** Quản lý giỏ hàng (Session cho khách vãng lai, Database cho thành viên, tự động gộp giỏ khi đăng nhập), tính ngưỡng miễn phí vận chuyển (300.000 đ), áp dụng/gỡ mã giảm giá.
- **Đặt hàng & Thanh toán:** Hỗ trợ COD (thành viên) và chuyển khoản VietQR / payOS (khách vãng lai và thành viên), chống Race Condition bằng `lockForUpdate()`, đối soát trạng thái đơn hàng & webhook HMAC-SHA256, hoàn kho và lượt dùng coupon khi hủy đơn hợp lệ.
- **Tài khoản & Hỗ trợ:** Đăng ký, đăng nhập, cập nhật hồ sơ, đổi mật khẩu, xem lịch sử/hủy đơn chờ xác nhận, danh sách yêu thích (Wishlist), thông báo đơn hàng, tra cứu đơn hàng công khai (`/orders/tracking`), trang hỗ trợ (`/ho-tro`), chính sách (`/chinh-sach`) và trợ lý AI tư vấn sách (`/chat/respond`).

### Phân hệ Quản trị (`/admin`)
- **Phân quyền RBAC:** Tách quyền `admin` (toàn quyền) và `staff` (quản lý sách, danh mục, đơn hàng, mã giảm giá, xem dashboard).
- **Dashboard:** Thống kê doanh thu, đơn hàng và xuất báo cáo CSV.
- **Quản lý Sách, Danh mục, Tác giả:** CRUD đầy đủ, nhập sách hàng loạt qua file CSV, chặn xóa danh mục/tác giả đang có sách liên kết.
- **Quản lý Mã giảm giá (`/admin/coupons`):** Tạo, chỉnh sửa, kích hoạt/tạm khóa, giới hạn lượt dùng và ngày hết hạn.
- **Quản lý Đơn hàng:** Tìm kiếm AJAX, chuyển trạng thái theo luồng hợp lệ, đồng bộ tồn kho/coupon và xuất hóa đơn PDF.
- **Quản lý Người dùng:** Tạo/sửa tài khoản và đồng bộ vai trò Spatie (chỉ dành cho Admin).

## Yêu cầu hệ thống

- PHP >= 8.3 (kèm extension `pdo_sqlite` hoặc `pdo_mysql`, `mbstring`, `openssl`)
- Composer
- Node.js & npm
- Git

## Hướng dẫn cài đặt và khởi chạy

**Cách 1: Chạy nhanh một chạm (macOS)**

Nhấn đúp vào file `start.command` trong Finder (hoặc chạy `./start.command` trong Terminal). Script sẽ tự kiểm tra `.env`, tạo khóa ứng dụng, cài thư viện còn thiếu, chạy migration/seed nếu chưa có DB, build giao diện và tự mở trình duyệt tại `http://127.0.0.1:8000`.

**Cách 2: Cài đặt thủ công**

**Bước 1: Clone mã nguồn**
```bash
git clone https://github.com/trng1006/mnm-bookstore.git
cd mnm-bookstore
```

**Bước 2: Cài đặt tự động (tạo `.env`, key, SQLite DB, migrate + seed dữ liệu mẫu, build frontend)**
```bash
composer setup
```

*(Tùy chọn)* Nếu muốn bật thanh toán VietQR qua payOS hoặc chatbot Gemini, bổ sung các biến tương ứng vào file `.env`:
```env
PAYOS_CLIENT_ID=
PAYOS_API_KEY=
PAYOS_CHECKSUM_KEY=
GEMINI_API_KEY=
```

**Bước 3: Khởi chạy Server**

Mở 2 Terminal trong thư mục dự án và chạy:

Terminal 1 (Backend Laravel):
```bash
php artisan serve
```

Terminal 2 (Frontend Vite dev hoặc build sẵn):
```bash
npm run dev
```

Truy cập website tại `http://localhost:8000`.

## Tài khoản mẫu (sau khi seed)

| Vai trò | Email | Mật khẩu |
| :--- | :--- | :--- |
| Admin | `admin@gmail.com` | `123` |
| Staff | `staff@gmail.com` | `123` |
| Customer | `customer@gmail.com` | `123` |

## Kiểm thử tự động

Chạy toàn bộ Feature & Unit test bằng PHPUnit:
```bash
php artisan test
```

---
Đồ án thuộc học phần Lập trình Mã nguồn mở.
