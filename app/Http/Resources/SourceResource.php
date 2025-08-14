<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SourceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'url' => $this->url,
            'author' => $this->author,
            'rss_url' => $this->fetcher_source,
            'active' => $this->active,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Only include deactivation reason if source is inactive
            'deactivation_reason' => $this->when(!$this->active, $this->why_deactivated),
        ];
    }
}
