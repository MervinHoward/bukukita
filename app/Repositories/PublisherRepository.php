<?php

namespace App\Repositories;

use App\Models\Publisher;

class PublisherRepository
{
    public function getAll(array $fields)
    {
        $publishers = Publisher::select($fields)->get();
        return $publishers;
    }

    public function getById(int $id, array $fields) {
        $publisher = Publisher::with('books')->select($fields)->findOrFail($id);
        return $publisher;
    }

    public function create(array $data) {
        $publisher = Publisher::create($data);
        return $publisher;
    }

    public function update(int $id, array $data) {
        $publisher = Publisher::findOrFail($id);
        $publisher->update($data);
        return $publisher;
    }
}
