<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class ShelfResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'code'
    ];

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'books' => BookResource::class
    ];
}
