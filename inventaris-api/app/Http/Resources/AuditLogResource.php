<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $modelName = class_basename($this->model_type);
        
        return [
            'id' => $this->id,
            'user' => $this->when($this->user, function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }, null),
            'action' => $this->action,
            'model_type' => $modelName,
            'model_id' => $this->model_id,
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
            'url' => $this->url,
            'ip_address' => $this->ip_address,
            'created_at' => $this->created_at,
            'formatted_date' => $this->created_at->format('Y-m-d H:i:s'),
            'user_agent' => $this->user_agent,
        ];
    }
}