<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'sources_count' => $this->when($this->relationLoaded('sources') || isset($this->sources_count),
                $this->sources_count ?? $this->sources->count()
            ),
            'sources' => SourceResource::collection($this->whenLoaded('sources')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
