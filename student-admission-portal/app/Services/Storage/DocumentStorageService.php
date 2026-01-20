<?php

namespace App\Services\Storage;

use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentStorageService
{
    private string $disk;
    private array $allowedMimeTypes;
    private int $maxFileSize;

    public function __construct()
    {
        // Ưu tiên S3 ở production, local dùng public disk
        $this->disk = app()->environment('production') ? 's3' : 'public';
        
        $this->allowedMimeTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
        ];
        $this->maxFileSize = 10 * 1024 * 1024; // 10MB
    }

    public function upload(UploadedFile $file, int $applicationId, string $documentType): Document
    {
        $this->validateFile($file);

        $filename = $this->generateSecureFilename($file);
        // Cấu trúc thư mục: applications/{id}/{type}/{hash}.ext
        $path = "applications/{$applicationId}/{$documentType}/{$filename}";

        // Upload lên disk đã chọn
        Storage::disk($this->disk)->put($path, file_get_contents($file));

        return Document::create([
            'application_id' => $applicationId,
            'document_type' => $documentType,
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'disk' => $this->disk, // Lưu disk để sau này truy xuất đúng chỗ
            'uploaded_at' => now(),
        ]);
    }

    public function getTemporaryUrl(Document $document, int $expiryMinutes = 60): string
    {
        // Nếu là local/public disk
        if ($document->disk === 'public') {
            return asset('storage/' . $document->file_path);
        }

        // Nếu là S3, tạo presigned URL
        return Storage::disk($document->disk)->temporaryUrl(
            $document->file_path,
            now()->addMinutes($expiryMinutes)
        );
    }

    public function delete(Document $document): bool
    {
        Storage::disk($document->disk)->delete($document->file_path);
        return $document->delete();
    }

    private function validateFile(UploadedFile $file): void
    {
        if (!in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new \Exception('File type not allowed. Accepted: JPG, PNG, PDF');
        }

        if ($file->getSize() > $this->maxFileSize) {
            throw new \Exception('File size exceeds maximum allowed (10MB)');
        }
    }

    private function generateSecureFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $hash = Str::random(40);
        return "{$hash}.{$extension}";
    }
}
