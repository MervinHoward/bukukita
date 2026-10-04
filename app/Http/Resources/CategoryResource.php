<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class CategoryResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'name'
    ];

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'books' => BookResource::class
    ];
}
