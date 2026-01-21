<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Application;
use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    public function store(Application $application, UploadedFile $file, string $type): Document
    {
        // Remove existing document of same type if exists
        $existing = Document::where('application_id', $application->id)
                            ->where('type', $type)
                            ->first();

        if ($existing) {
            $this->delete($existing);
        }

        $path = $file->store('documents', 'private');
        
        return Document::create([
            'application_id' => $application->id,
            'type' => $type,
            'path' => $path, // path returned by store() is relative to root of disk
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    public function delete(Document $document): void
    {
        if ($document->path) {
            Storage::disk('private')->delete($document->path);
        }
        $document->delete();
    }

    public function getUrl(Document $document): string
    {
        // Route is not yet defined, but this is the intention
        // We will define route later in Controller task
        return route('documents.show', $document);
    }
}
