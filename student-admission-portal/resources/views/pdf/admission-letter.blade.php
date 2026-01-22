<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admission Letter</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0;
        }
        .content {
            margin-bottom: 20px;
        }
        .details {
            width: 100%;
            margin-bottom: 20px;
        }
        .details th, .details td {
            text-align: left;
            padding: 5px;
            vertical-align: top;
        }
        .details th {
            width: 120px;
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
        .signature {
            margin-top: 40px;
            border-top: 1px dashed #333;
            width: 200px;
            text-align: center;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Tech University</h1>
        <p>123 Education Road, Nairobi, Kenya</p>
        <p>Email: admissions@techuni.ac.ke | Phone: +254 700 000 000</p>
    </div>

    <div class="content">
        <p style="text-align: right;">Date: {{ now()->format('d M, Y') }}</p>
        <p><strong>Ref: {{ $student->student_code ?? 'PENDING' }}</strong></p>

        <h3>OFFICIAL ADMISSION LETTER</h3>

        <p>Dear <strong>{{ $student->first_name }} {{ $student->last_name }}</strong>,</p>

        <p>We are pleased to inform you that you have been admitted to <strong>Tech University</strong> to pursue the following program:</p>

        <div style="background: #f0f0f0; padding: 10px; border: 1px solid #ddd; margin: 10px 0;">
            <h2 style="margin: 0; text-align: center;">{{ $program->name }}</h2>
            <p style="text-align: center; margin: 5px 0;">Code: {{ $program->code }} | Duration: {{ $program->duration }}</p>
        </div>

        <p>Your admission is subject to the following details:</p>

        <table class="details">
            <tr>
                <th>Student Name:</th>
                <td>{{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }}</td>
            </tr>
            <tr>
                <th>National ID:</th>
                <td>{{ $student->national_id }}</td>
            </tr>
            <tr>
                <th>Address:</th>
                <td>{{ $student->address }}, {{ $student->city }}</td>
            </tr>
            <tr>
                <th>Admission Date:</th>
                <td>{{ $application->approved_at ? $application->approved_at->format('d M, Y') : 'N/A' }}</td>
            </tr>
        </table>

        <p>Please report to the Admissions Office on <strong>{{ isset($block->start_date) ? \Carbon\Carbon::parse($block->start_date)->format('d M, Y') : 'N/A' }}</strong> for registration and orientation. Bring your original academic certificates and National ID.</p>

        <p>Congratulations on your achievement.</p>
    </div>

    <div class="signature">
        <p><strong>Registrar (Academic)</strong></p>
        <p>Tech University</p>
    </div>

    <div class="footer">
        <p>This is a system-generated document. Valid without a signature.</p>
        <p>Generated on: {{ now()->format('d M Y H:i:s') }}</p>
    </div>
</body>
</html>
