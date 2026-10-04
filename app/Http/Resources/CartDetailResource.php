<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class CartDetailResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'cart_id',
        'book_exemplar_id'
    ];

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'exemplar' => BookExemplarResource::class
    ];
}
