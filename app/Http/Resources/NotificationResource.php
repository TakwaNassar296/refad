<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            'type' => $this->type,
            'notificationTitle' => $this->notification_title, 
            'message' => $this->data, 
            'isRead' => !is_null($this->read_at),
            'readAt' => $this->read_at?->format('Y-m-d H:i:s'),
            'createdAt' => $this->created_at->format('Y-m-d H:i:s'),
            'timeAgo' => $this->created_at->diffForHumans(),
            
            
            'user' => [
                'id' => $this->notifiable->id,
                'name' => $this->notifiable->name,
                'role' => $this->notifiable->role,
            ],
        ];
    }
}
