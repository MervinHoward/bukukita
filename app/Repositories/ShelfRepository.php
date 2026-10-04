<?php

namespace App\Repositories;

use App\Models\Shelf;

class ShelfRepository
{
    public function getAll()
    {
        return Shelf::all();
    }

    public function getById(int $id)
    {
        $shelf = Shelf::with('books')->findOrFail($id);
        return $shelf;
    }

    public function create(array $data) {
        $shelf = Shelf::create($data);
        return $shelf;
    }

    public function update(int $id, array $data) {
        $shelf = Shelf::findOrFail($id);
        $shelf->update($data);
        return $shelf;
    }
}
