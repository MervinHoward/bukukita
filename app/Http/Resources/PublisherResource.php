<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class PublisherResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'name',
        'address',
        'phone',
        'email'
    ];

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'books' => BookResource::class
    ];
}
