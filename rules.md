# Quy tắc phát triển đồ án môn học (Mã Nguồn Mở)

Dựa trên Đề cương học phần "Lập trình mã nguồn mở" (0101101978) của trường.
AI Assistant (Antigravity/Fen) khi code phải tuân thủ tuyệt đối các quy tắc sau để đảm bảo sinh viên không bị "vượt rào" kiến thức khi đi bảo vệ đồ án.

## 1. Công nghệ & Ngôn ngữ cho phép (Trong Syllabus)
- **Backend:** PHP (OOP cơ bản), PDO, Laravel Framework (theo mô hình chuẩn MVC).
- **Frontend:** HTML, CSS (Tailwind CSS - do project đã setup sẵn), JavaScript thuần (Vanilla JS).
- **Cơ sở dữ liệu:** MySQL (tương tác qua Eloquent ORM hoặc PDO).
- **Công cụ:** Laragon, VS Code, Git, Composer, Artisan CLI.

## 2. Các kỹ thuật bắt buộc & Khuyến khích (Nằm trong chương trình)
- **Xử lý Form:** GET/POST, Upload file, Validation.
- **Quản lý trạng thái:** Session, Cookie.
- **Tương tác Frontend-Backend:** Sử dụng JavaScript thao tác DOM, bắt sự kiện (Event Handling).
- **AJAX:** **ĐƯỢC PHÉP VÀ KHUYẾN KHÍCH** (Nằm ở mục 2.2.6: Sử dụng AJAX để tương tác bất đồng bộ với server-side). Dùng `fetch` API hoặc `XMLHttpRequest`.
- **Laravel Features:** Routing, Controller, Blade Template, Eloquent ORM, Migration, Seeder, Factory, Middleware, Authentication cơ bản.
- **Nâng cao được phép:** Phân trang (Pagination - Mục 3.4.1), Tìm kiếm (Searching - Mục 3.4.2).

## 3. CÁC KỸ THUẬT CẤM SỬ DỤNG (Ngoài vùng kiến thức)
- **Cấm xài Frontend Framework/Library:** KHÔNG dùng React, Vue, Angular, Svelte.
- **Cấm xài TALL Stack chuyên sâu:** KHÔNG dùng Livewire, KHÔNG dùng Alpine.js (chỉ xài Vanilla JS).
- **Kiến trúc quá phức tạp:** KHÔNG dùng Repository Pattern, Service Pattern, CQRS, DTOs... trừ khi sinh viên yêu cầu. Bám sát mô hình MVC cơ bản (Controller gọi trực tiếp Model/Eloquent).
- **Cấm lạm dụng jQuery (nếu không cần thiết):** Ưu tiên Vanilla JS vì đề cương nhấn mạnh học JS thuần (DOM Manipulation).
- **Tránh các hàm/collection methods quá ngách:** Tránh lạm dụng các hàm Laravel Collection quá mới/hiếm gặp khiến sinh viên khó giải thích. Dùng vòng lặp `foreach` hoặc các hàm map/filter cơ bản, dễ hiểu.

## 4. Coding Standard (Tiêu chí chấm điểm)
- Code sạch, đúng cú pháp, dễ đọc, thụt lề chuẩn.
- Tên biến, tên hàm, tên class phải gợi nhớ, theo chuẩn (CamelCase, snake_case tùy ngôn ngữ).
- Có comment giải thích các đoạn code logic (để sinh viên dễ hiểu và dễ báo cáo).
- Chia nhỏ logic hợp lý trong Controller, không nhồi nhét quá nhiều vào Route hay View.
