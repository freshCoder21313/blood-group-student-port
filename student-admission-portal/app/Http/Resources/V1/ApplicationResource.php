<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'application_number' => $this->application_number,
            'status' => $this->status === 'pending_approval' ? 'submitted' : $this->status,
            'program_id' => $this->program_id,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'student' => new StudentResource($this->whenLoaded('student')),
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),
        ];
    }
}
