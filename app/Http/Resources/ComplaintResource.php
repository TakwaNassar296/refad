<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
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
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'campId' => $this->camp_id,
            'campName' => $this->camp ? $this->camp->name : null,
            'topic' => $this->topic,
            'message' => $this->message,
            'createdAt' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updatedAt' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
