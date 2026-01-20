# Student Admission Portal (PHP Web Service)

Hệ thống cổng thông tin tuyển sinh trực tuyến, tích hợp với hệ thống nội bộ ASP.NET. Dự án này cung cấp API RESTful cho việc đăng ký nhập học, nộp hồ sơ, và đồng bộ dữ liệu hai chiều với hệ thống quản lý đào tạo (ASP System).

## 🚀 Yêu Cầu Hệ Thống

Đảm bảo máy của bạn đã cài đặt các công cụ sau:

*   **PHP**: >= 8.2 (Khuyên dùng 8.3)
*   **Composer**: Công cụ quản lý dependency cho PHP.
*   **Database**: MySQL 8.0+ hoặc MariaDB 10.11+.
*   **Web Server**: Nginx/Apache hoặc dùng PHP built-in server.
*   **Extensions**: `bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `curl`.

## 📦 Cài Đặt & Cấu Hình

Làm theo các bước sau để thiết lập dự án trên môi trường local:

### 1. Clone và Cài đặt Dependencies

Di chuyển vào thư mục dự án và cài đặt các thư viện PHP:

```bash
cd student-admission-portal
composer install
```

### 2. Cấu Hình Môi Trường (.env)

Sao chép file cấu hình mẫu và tạo key ứng dụng:

```bash
cp .env.example .env
php artisan key:generate
```

Mở file `.env` và cấu hình thông tin Database:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=student_admission
DB_USERNAME=root
DB_PASSWORD=your_password
```

Cấu hình tích hợp ASP System (Nếu cần test API nội bộ):

```ini
ASP_API_BASE_URL=https://internal-asp.school.local/api
ASP_API_KEY=your_test_key
ASP_API_SECRET=your_test_secret
```

### 3. Khởi Tạo Cơ Sở Dữ Liệu

Chạy migration để tạo các bảng trong database (Users, Applications, Students, etc.):

```bash
php artisan migrate
```

### 4. Seed Dữ Liệu Mẫu (Tùy chọn)

Nếu bạn muốn có dữ liệu mẫu để test (Chương trình học, Khối nhập học):

```bash
php artisan db:seed
```

## 🛠️ Chạy Ứng Dụng

Khởi chạy server phát triển local:

```bash
php artisan serve
```

Ứng dụng sẽ chạy tại: `http://localhost:8000`

## 🔌 Tài Liệu API

Hệ thống cung cấp các nhóm API chính:

### 1. Authentication (Public)
*   `POST /api/register`: Đăng ký tài khoản mới.
*   `POST /api/login`: Đăng nhập lấy Token.
*   `POST /api/verify-otp`: Xác thực OTP.

### 2. Internal Sync API (Dành cho ASP System)
*Requires Headers:* `X-API-Key`, `X-Timestamp`, `X-Signature`

*   `GET /api/v1/students`: Lấy danh sách hồ sơ (Filter theo status, date).
*   `GET /api/v1/students/{id}`: Lấy chi tiết hồ sơ.
*   `POST /api/v1/update-status`: Cập nhật trạng thái hồ sơ (Approved/Rejected).
*   `POST /api/v1/bulk-update-status`: Cập nhật hàng loạt.

### 3. Student Data (Proxy to ASP)
*   `GET /api/v1/students/{code}/grades`: Tra cứu điểm.
*   `GET /api/v1/students/{code}/fees`: Tra cứu công nợ.

## 📂 Cấu Trúc Dự Án Chính

*   `app/Models`: Chứa các Entity (User, Student, Application...).
*   `app/Http/Controllers/Api/V1`: Controllers xử lý logic API chính.
*   `app/Services/Integration`: Service giao tiếp với ASP System.
*   `app/Http/Middleware/ApiAuthentication.php`: Middleware bảo mật xác thực HMAC cho internal API.
*   `database/migrations`: Định nghĩa cấu trúc Database.

## 🧪 Testing

Chạy Unit Test và Feature Test:

```bash
php artisan test
```

---
**Lưu ý:** Dự án này sử dụng Laravel 11.x. Vui lòng tham khảo [Laravel Documentation](https://laravel.com/docs) để biết thêm chi tiết về Framework.