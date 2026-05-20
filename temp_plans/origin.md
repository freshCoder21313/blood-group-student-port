CHI TIẾT WORKFLOW (LUỒNG HOẠT ĐỘNG)
​Tôi chia làm 3 giai đoạn chính: Đăng ký, Xét duyệt (Đồng bộ) và Sau nhập học.
​Giai đoạn 1: Đăng ký & Nộp hồ sơ (Trên PHP Portal)
​Đăng ký tài khoản (Register):
​User nhập Email hoặc SĐT.
​Hệ thống gửi OTP (qua Email hoặc SMS) để xác thực (tránh spam tài khoản rác).
​User tạo mật khẩu \rightarrow Đăng nhập thành công.
​Trạng thái tài khoản: New (Chưa có hồ sơ).
​Điền hồ sơ (Application Form):
​User điền form chia làm các bước (Step-form):
​B1: Thông tin cá nhân.
​B2: Thông tin phụ huynh/người bảo hộ.
​B3: Chọn ngành nghề & Khối học.
​B4: Upload giấy tờ (Bằng cấp 2, Giấy khám sức khỏe...).
​Thanh toán & Nộp (Submission):
​Hệ thống hiển thị thông tin thanh toán (Số Paybill/Till Number).
​User chuyển khoản M-Pesa \rightarrow Nhập Mã giao dịch \rightarrow Upload ảnh bằng chứng.
​User bấm "NỘP HỒ SƠ".
​Trạng thái tài khoản: Pending Approval (Chờ xét duyệt).
​Hệ thống: Gửi email xác nhận "Đã nhận hồ sơ".
​Giai đoạn 2: Xét duyệt & Đồng bộ (Kết nối PHP \leftrightarrow ASP)
​Đây là bước quan trọng nhất để nối 2 hệ thống.
​Đồng bộ dữ liệu (Sync):
​Hệ thống ASP (trong LAN) chạy một tác vụ định kỳ (Cronjob) hoặc nhân viên bấm nút "Lấy hồ sơ mới".
​ASP gọi API: GET /api/v1/students?status=pending từ PHP Portal.
​PHP trả về danh sách JSON (gồm cả link ảnh chứng từ).
​Xét duyệt (Review tại ASP):
​Ban tuyển sinh xem hồ sơ trên phần mềm nội bộ.
​Kế toán đối soát mã M-Pesa.
​Tình huống 1: Hồ sơ OK.
​Nhân viên chuyển trạng thái trên ASP thành Approved.
​ASP gọi API ngược lại PHP: POST /api/v1/update-status (Set status = Approved, cấp Mã Sinh Viên).
​Tình huống 2: Hồ sơ lỗi (ảnh mờ, thiếu giấy).
​Nhân viên note lý do.
​ASP gọi API: POST /api/v1/update-status (Set status = Request Info).
​PHP Portal gửi email thông báo học sinh vào sửa.
​Giai đoạn 3: Sau nhập học (Student Portal)
​Kích hoạt sinh viên:
​Khi trạng thái là Approved, giao diện khi đăng nhập vào PHP Portal sẽ thay đổi.
​Ẩn form đăng ký tuyển sinh.
​Hiện Dashboard: Thời khóa biểu, Điểm số, Công nợ.
​Tra cứu dữ liệu:
​Khi sinh viên xem điểm/học phí, PHP Portal sẽ gọi API sang ASP (hoặc database trung gian) để lấy dữ liệu realtime.