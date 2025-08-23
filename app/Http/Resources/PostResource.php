<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->url,
            'excerpt' => $this->excerpt,
            // Only include content for individual post requests (show endpoint)
            'content' => $this->when(
                $this->isShowRequest($request),
                $this->content
            ),
            'posted_at' => optional($this->posted_at)->toIso8601String(),
            'read' => (bool) $this->read,
            'uid' => $this->uid,
            'author' => $this->author,
            'time_ago' => $this->time_ago,
            'source' => new SourceResource($this->whenLoaded('source')),
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }

    /**
     * Determine if this is a show request (individual post)
     */
    private function isShowRequest($request)
    {
        // Check if this is a show request by looking at the URL pattern
        return $request->is('api/v1/posts/*') &&
               !$request->is('api/v1/posts') &&
               $request->isMethod('GET');
    }
}
