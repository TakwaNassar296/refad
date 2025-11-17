<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'backupPhone' => $this->backup_phone,
            'idNumber' => $this->id_number,
            'role' => $this->role,
            'adminPosition' => $this->admin_position,
            'licenseNumber' => $this->license_number,
            'acceptTerms' => (bool) $this->accept_terms,
            'status' => $this->status,
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
