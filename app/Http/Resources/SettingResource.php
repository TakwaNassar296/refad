<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
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
            'siteName' => $this->getTranslations('site_name'), 
            'siteLogo' => $this->site_logo ? asset('storage/' . $this->site_logo) : null,
            'favicon' => $this->favicon ? asset('storage/' . $this->favicon) : null,
            'phone' => $this->phone,
            'email' => $this->email,
            'facebook' => $this->facebook,
            'twitter' => $this->twitter,
            'instagram' => $this->instagram,
            'linkedin' => $this->linkedin,
            'youtube' => $this->youtube,
            'whatsapp' => $this->whatsapp,
        ];
    }
}
