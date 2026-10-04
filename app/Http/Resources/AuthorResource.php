<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class AuthorResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'bio'
    ];

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'books' => BookResource::class
    ];
}
