<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DayResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $anniversaryDate = $this->anniv_at ? Carbon::parse($this->anniv_at) : null;

        return [
            'id' => $this->id,
            'entity_id' => $this->entity_id,
            'name' => $this->name,
            'desc' => $this->desc,
            'anniv_at' => $this->anniv_at,
            'diff_days' => $this->diff_days,
            'formatted_date' => $anniversaryDate ? $anniversaryDate->format('Y年m月d日') : null,
            'is_future' => $anniversaryDate ? $anniversaryDate->isFuture() : null,
            'is_today' => $anniversaryDate ? $anniversaryDate->isToday() : null,
            'entity' => new EntityResource($this->whenLoaded('entity')),
        ];
    }
}
