<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'total_ticket' => $this['total_ticket'],
            'actived_ticket' => $this['actived_ticket'],
            'resolved_ticket' => $this['resolved_ticket'],
            'avg_resolution_time' => $this['avg_resolution_time'],
            'status_distribution' =>  [
                'open' => $this['status_distribution']['open'],
                'on_progress' => $this['status_distribution']['on_progress'],
                'resolved' => $this['status_distribution']['resolved'],
                'rejected' => $this['status_distribution']['rejected'],
                'closed' => $this['status_distribution']['closed'],
            ],
        ];
    }
}
