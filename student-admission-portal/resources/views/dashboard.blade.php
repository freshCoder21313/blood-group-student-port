<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Student Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <style>body { font-family: 'Instrument Sans', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-800">

    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <span class="font-bold text-xl text-blue-600">Student Portal Demo</span>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-500">Sinh viên: Nguyễn Văn A (Demo)</span>
                <a href="/" class="text-sm text-gray-600 hover:text-blue-600">Thoát</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        
        <!-- Section 1: Thông tin hồ sơ (Admission Module) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="bg-blue-100 text-blue-600 p-1.5 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></span>
                Trạng thái Hồ sơ
            </h2>
            <div class="grid md:grid-cols-3 gap-6">
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <p class="text-sm text-gray-500">Mã hồ sơ</p>
                    <p class="font-mono font-bold text-lg">APP-2024-001</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <p class="text-sm text-gray-500">Trạng thái hiện tại</p>
                    <span id="app-status" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mt-1">
                        pending_payment
                    </span>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <p class="text-sm text-gray-500">Ngành đăng ký</p>
                    <p class="font-medium">Công nghệ thông tin</p>
                </div>
            </div>
        </div>

        <!-- Section 2: Thanh toán (Payment Module) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="bg-green-100 text-green-600 p-1.5 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg></span>
                Nộp lệ phí xét tuyển
            </h2>
            <div class="grid md:grid-cols-2 gap-8">
                <div>
                    <p class="text-gray-600 text-sm mb-4">Vui lòng chuyển khoản <b>500,000 VNĐ</b> và tải lên biên lai thanh toán.</p>
                    <form id="payment-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Mã giao dịch ngân hàng</label>
                            <input type="text" name="transaction_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border p-2" placeholder="VD: FT230101..." required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ảnh biên lai</label>
                            <input type="file" name="receipt_image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                        </div>
                        <input type="hidden" name="amount" value="500000">
                        <input type="hidden" name="payment_method" value="bank_transfer">
                        <!-- Giả lập Application ID = 1 -->
                        <input type="hidden" name="application_id" value="1"> 
                        
                        <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Gửi xác nhận thanh toán
                        </button>
                    </form>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 border border-dashed border-gray-300 flex items-center justify-center text-gray-400 text-sm" id="receipt-preview">
                    Khu vực hiển thị biên lai sau khi upload
                </div>
            </div>
        </div>

        <!-- Section 3: Webhook Simulation (Admin/Dev Tool) -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 border-l-4 border-l-purple-500">
            <h2 class="text-lg font-bold mb-4 flex items-center gap-2">
                <span class="bg-purple-100 text-purple-600 p-1.5 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg></span>
                ASP System Simulator (Dev Tool)
            </h2>
            <p class="text-sm text-gray-600 mb-4">Giả lập việc Hệ thống nội bộ (ASP) gọi Webhook để cập nhật trạng thái hồ sơ.</p>
            
            <div class="flex gap-4">
                <button onclick="simulateWebhook('approved')" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">Giả lập: Duyệt Hồ Sơ</button>
                <button onclick="simulateWebhook('rejected')" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700">Giả lập: Từ chối</button>
                <button onclick="simulateWebhook('request_info')" class="px-4 py-2 bg-yellow-500 text-white rounded-md text-sm hover:bg-yellow-600">Giả lập: Yêu cầu bổ sung</button>
            </div>
            <div id="webhook-log" class="mt-4 p-2 bg-gray-900 text-green-400 font-mono text-xs rounded h-24 overflow-y-auto">
                > Ready to simulate...
            </div>
        </div>

    </main>

    <script>
        // Xử lý form thanh toán
        document.getElementById('payment-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const btn = this.querySelector('button');
            const originalText = btn.innerText;
            btn.innerText = 'Đang xử lý...';
            btn.disabled = true;

            const formData = new FormData(this);

            try {
                // Gọi API Payment
                const response = await axios.post('/api/v1/payments/submit', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });

                alert('Thanh toán thành công! Mã: ' + response.data.data.transaction_code);
                
                // Update UI status giả lập
                document.getElementById('app-status').innerText = 'pending_approval';
                document.getElementById('app-status').className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mt-1';
                
            } catch (error) {
                console.error(error);
                alert('Lỗi: ' + (error.response?.data?.message || error.message));
            } finally {
                btn.innerText = originalText;
                btn.disabled = false;
            }
        });

        // Xử lý Webhook Simulation
        async function simulateWebhook(status) {
            const log = document.getElementById('webhook-log');
            log.innerHTML += `\n> Sending webhook: ${status}...`;

            try {
                // Gọi API Webhook (Giả lập)
                const response = await axios.post('/api/v1/webhooks/status-changed', {
                    application_id: 1, // Hardcode ID demo
                    status: status,
                    reason: 'Admin approved via Simulator',
                    processed_by: 'Dev User'
                });

                log.innerHTML += `\n> Success: ${response.data.message}`;
                
                // Update UI status
                document.getElementById('app-status').innerText = status;
                const colors = {
                    'approved': 'bg-green-100 text-green-800',
                    'rejected': 'bg-red-100 text-red-800',
                    'request_info': 'bg-yellow-100 text-yellow-800'
                };
                document.getElementById('app-status').className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colors[status]} mt-1`;

            } catch (error) {
                log.innerHTML += `\n> Error: ${error.response?.data?.message || error.message}`;
                // Note: Webhook có thể lỗi nếu Application ID 1 chưa tồn tại trong DB
                if(error.response?.status === 404) {
                    log.innerHTML += `\n> Hint: Bạn cần tạo Application ID 1 trong DB trước.`;
                }
            }
        }
    </script>
</body>
</html>
