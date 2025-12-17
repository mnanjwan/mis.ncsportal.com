<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficerDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'document_type' => $this->document_type,
            'file_name' => $this->file_name,
            'file_url' => $this->file_path ? url($this->file_path) : null,
            'file_size' => $this->file_size,
            'mime_type' => $this->mime_type,
            'uploaded_at' => $this->uploaded_at?->toIso8601String(),
        ];
    }
}

