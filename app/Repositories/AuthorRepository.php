<?php

namespace App\Repositories;

use App\Models\Author;

class AuthorRepository
{
    public function getAll(array $fields)
    {
        $authors = Author::select($fields)->get();
        return $authors;
    }

    public function getById(int $id, array $fields) {
        $author = Author::with('books')->select($fields)->findOrFail($id);
        return $author;
    }

    public function create(array $data) {
        $author = Author::create($data);
        return $author;
    }

    public function update(int $id, array $data) {
        $author = Author::findOrFail($id);
        $author->update($data);
        return $author;
    }
}
