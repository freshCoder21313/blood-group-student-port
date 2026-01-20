<!DOCTYPE html>
<html>
<head>
    <title>Thông báo kết quả xét tuyển</title>
</head>
<body>
    <h1>Xin chào {{ $name }},</h1>

    @if($status === 'approved')
        <p style="color: green; font-weight: bold;">Chúc mừng bạn! Hồ sơ đăng ký vào chương trình {{ $program }} của bạn đã được DUYỆT.</p>
        <p>Vui lòng đăng nhập vào cổng thông tin để hoàn tất thủ tục nhập học.</p>
    @else
        <p style="color: red;">Rất tiếc, hồ sơ của bạn vào chương trình {{ $program }} chưa đạt yêu cầu hoặc cần bổ sung thông tin.</p>
        <p>Vui lòng kiểm tra lại thông tin hoặc liên hệ phòng tuyển sinh.</p>
    @endif

    <p>Trân trọng,<br>Phòng Tuyển Sinh</p>
</body>
</html>
