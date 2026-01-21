<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Document;
use App\Services\Storage\DocumentStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DocumentController extends Controller
{
    public function __construct(
        private DocumentStorageService $storageService
    ) {}

    /**
     * Get document list for an application
     */
    public function index(int $applicationId): JsonResponse
    {
        $application = Application::with('documents')->findOrFail($applicationId);
        
        $documents = $application->documents->map(function ($doc) {
            return [
                'id' => $doc->id,
                'type' => $doc->document_type,
                'original_name' => $doc->original_name,
                'url' => $this->storageService->getTemporaryUrl($doc),
                'uploaded_at' => $doc->uploaded_at
            ];
        });

        return response()->json(['success' => true, 'data' => $documents]);
    }

    /**
     * Upload new document
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'application_id' => 'required|exists:applications,id',
            'type' => 'required|string',
            'file' => 'required|file|mimes:jpeg,png,pdf|max:10240'
        ]);

        try {
            $document = $this->storageService->upload(
                $request->file('file'),
                $request->application_id,
                $request->type
            );

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => [
                    'id' => $document->id,
                    'url' => $this->storageService->getTemporaryUrl($document)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Download (redirect to presigned url)
     */
    public function download(int $id)
    {
        $document = Document::findOrFail($id);
        $url = $this->storageService->getTemporaryUrl($document);
        
        return response()->json(['url' => $url]);
    }
}
