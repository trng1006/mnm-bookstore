# 📚 MNM Bookstore (Đồ án Lập trình Mã nguồn mở)

MNM Bookstore là dự án website bán sách trực tuyến được xây dựng dựa trên framework Laravel. Đây là đồ án môn học "Lập trình mã nguồn mở", áp dụng các kiến thức về mô hình MVC, thao tác với Cơ sở dữ liệu và tương tác bất đồng bộ (AJAX).

## 🚀 Công nghệ sử dụng
- **Backend:** PHP 8.x, Laravel Framework, Eloquent ORM.
- **Frontend:** HTML5, CSS3, Tailwind CSS (đóng gói qua Vite), JavaScript thuần (Vanilla JS / Fetch API).
- **Database:** SQLite (Mặc định) / MySQL.
- **Kiến trúc:** Chuẩn mô hình MVC (Model - View - Controller).

## ✨ Chức năng nổi bật
- Hiển thị danh sách Sách mới, Sách bán chạy.
- Tính năng **Lọc và Sắp xếp** (theo giá, tên, độ phổ biến, mới nhất).
- **Phân trang AJAX** (chuyển trang và lọc không cần tải lại toàn bộ trang, tối ưu trải nghiệm người dùng).
- Dữ liệu mẫu phong phú (Được crawl trực tiếp từ các trang thương mại điện tử thực tế).
- Giao diện Responsive chuẩn Web Design Guidelines tối giản, hiện đại.

## ⚙️ Yêu cầu hệ thống
- PHP >= 8.1
- Composer
- Node.js & npm (để build CSS/JS)
- Git

## 🛠️ Hướng dẫn cài đặt và khởi chạy 

Để chạy dự án này trên máy cá nhân, bạn vui lòng làm theo các bước sau:

**Bước 1: Clone mã nguồn về máy**
```bash
git clone https://github.com/trng1006/mnm-bookstore.git
cd mnm-bookstore
```

**Bước 2: Cài đặt các thư viện (Dependencies)**
```bash
composer install
npm install
```

**Bước 3: Cấu hình môi trường (Environment)**
Tạo file `.env` từ file mẫu:
```bash
cp .env.example .env
```
Khởi tạo App Key cho dự án Laravel:
```bash
php artisan key:generate
```

**Bước 4: Thiết lập Cơ sở dữ liệu (Database)**
*Mặc định dự án sử dụng SQLite để dễ dàng chạy thử. Bạn cần tạo file database trống trước:*
```bash
# Dành cho macOS/Linux:
touch database/database.sqlite

# Dành cho Windows (Powershell):
ni database/database.sqlite -ItemType File
```
Sau đó, chạy lệnh sau để tạo các bảng (tables) và nạp dữ liệu mẫu (sách, tác giả, danh mục):
```bash
php artisan migrate --seed
```

**Bước 5: Khởi chạy Server**
Mở 2 Terminal (cửa sổ lệnh) riêng biệt trong thư mục dự án và chạy đồng thời 2 lệnh sau:

*Terminal 1 (Chạy Backend Laravel):*
```bash
php artisan serve
```

*Terminal 2 (Chạy Frontend Vite để biên dịch Tailwind CSS):*
```bash
npm run dev
```

**Bước 6: Trải nghiệm**
Mở trình duyệt và truy cập vào địa chỉ: **http://localhost:8000**

---
*Đồ án thuộc học phần Lập trình Mã nguồn mở.*
