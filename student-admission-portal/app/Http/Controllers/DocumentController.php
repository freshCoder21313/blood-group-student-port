<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function __construct(
        private \App\Services\DocumentService $documentService
    ) {}

    public function download(Document $document)
    {
        $this->authorize('view', $document);

        if (! Storage::disk('private')->exists($document->path)) {
            abort(404);
        }

        return Storage::disk('private')->download(
            $document->path,
            $document->original_name
        );
    }

    public function destroy(Document $document)
    {
        $this->authorize('delete', $document);

        $this->documentService->delete($document);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Document deleted']);
        }

        return back()->with('status', 'document-deleted');
    }
}
