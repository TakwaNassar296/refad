<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'pageType' => $this->type,
            'title' => $this->getTranslation('title', app()->getLocale()),
            'description' => $this->getTranslation('description', app()->getLocale()),
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'file' => $this->file ? asset('storage/' . $this->file) : null,
            'isActive' => $this->is_active,
        ];
    }
}
