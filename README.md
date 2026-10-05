# 📚 MNM Bookstore (Đồ án Lập trình Mã nguồn mở)

MNM Bookstore là dự án website bán sách trực tuyến được xây dựng dựa trên framework Laravel. Đây là đồ án môn học "Lập trình mã nguồn mở", áp dụng các kiến thức về mô hình MVC, thao tác với Cơ sở dữ liệu, quản lý phiên và tương tác bất đồng bộ (AJAX).

## 🚀 Công nghệ sử dụng
- **Backend:** PHP 8.x, Laravel Framework, Eloquent ORM.
- **Frontend:** HTML5, CSS3, Tailwind CSS (đóng gói qua Vite), JavaScript thuần (Vanilla JS / Fetch API).
- **Database:** SQLite (Mặc định cho Dev) / MySQL (Production).
- **Kiến trúc:** Chuẩn mô hình MVC (Model - View - Controller).

## ✨ Chức năng nổi bật
- **Giao diện chuẩn chỉ:** Thiết kế Responsive chuẩn Web Design Guidelines tối giản, hiện đại với phong cách Warm Humanist.
- **Quản lý danh mục & Sản phẩm:** Hiển thị danh sách sách mới, sách bán chạy với dữ liệu mẫu phong phú.
- **Tìm kiếm, Lọc và Phân trang:** 
  - Tính năng lọc và sắp xếp theo giá, tên, độ phổ biến, mới nhất.
  - Phân trang AJAX và tìm kiếm bằng Fetch API không cần tải lại toàn bộ trang, tối ưu trải nghiệm người dùng.
- **Quy trình giỏ hàng & Thanh toán an toàn:** Hỗ trợ xử lý trừ kho an toàn (chống Race Condition).
- **Xác thực & Phân quyền:** Quản lý quyền truy cập phân cấp (Admin, Staff, Customer).

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

**Bước 2: Chạy lệnh cài đặt tự động (Cực kỳ đơn giản)**
Nhờ tính năng tự động hóa, bạn chỉ cần gõ **duy nhất 1 câu lệnh** sau đây. Lệnh này sẽ tự động tải thư viện, cấu hình môi trường (.env), tạo CSDL và bơm toàn bộ dữ liệu mẫu (Sách, tài khoản...) vào máy bạn:
```bash
composer setup
```

**Bước 3: Khởi chạy Server**
Mở 2 Terminal (cửa sổ lệnh) riêng biệt trong thư mục dự án và chạy đồng thời 2 lệnh sau:

*Terminal 1 (Chạy Backend Laravel):*
```bash
php artisan serve
```

*Terminal 2 (Chạy Frontend Vite để biên dịch Tailwind CSS):*
```bash
npm run dev
```

**Bước 4: Trải nghiệm**
Mở trình duyệt và truy cập vào địa chỉ: **http://localhost:8000**

---
*Đồ án thuộc học phần Lập trình Mã nguồn mở.*
