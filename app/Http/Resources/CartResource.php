<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class CartResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'loaner_id'
    ];

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'details' => CartDetailResource::class
    ];
}
