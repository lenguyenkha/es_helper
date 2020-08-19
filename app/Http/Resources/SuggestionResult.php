<?php

namespace App\Http\Resources;

/**
 * Class SuggestionResult
 * @package App\Http\Resources
 *
 */
class SuggestionResult extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return $this->resource;
    }
}
