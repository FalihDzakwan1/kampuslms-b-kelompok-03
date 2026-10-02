<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'sks' => $this->sks,
            'status' => $this->status,
            'lecturer_id' => $this->lecturer_id,
            'lecturer' => new UserResource($this->whenLoaded('lecturer')),
            'materials_count' => $this->whenCounted('materials'),
            'assignments_count' => $this->whenCounted('assignments'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
