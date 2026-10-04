<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;

class BookResource extends JsonApiResource
{
    /**
     * The resource's attributes.
     */
    public $attributes = [
        'title',
        'author_id',
        'publisher_id',
        'publication_year',
        'shelf_id',
        'description',
        'isbn',
        'physical_description'
    ];

    /**
     * The resource's relationships.
     */
    public $relationships = [
        'author' => AuthorResource::class,
        'publisher' => PublisherResource::class,
        'shelf' => ShelfResource::class,
        'categories' => CategoryResource::class,
        'exemplars' => BookExemplarResource::class
    ];
}
