<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AboutUsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'pageType' => $this->page_type,
            'title'       => $this->getTranslations('title'),
            'description' => $this->getTranslations('description'),
            'image'       => $this->image ? asset('storage/' . $this->image) : null,
            'second_image'       => $this->second_image ? asset('storage/' . $this->second_image) : null,
            'file'       => $this->file ? asset('storage/' . $this->file) : null,
        ];
    }
}
