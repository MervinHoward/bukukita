<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class BookExemplarResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'book_id',
        'edition',
        'status'
    ];

    /**
     * The resource's relationships.
     */
    public $relationships = [
        // ...
    ];
}
