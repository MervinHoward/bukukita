<?php

namespace App\Repositories;

use App\Models\Category;

class CategoryRepository {
    public function getAll() {
        return Category::all();
    }

    public function getById(int $id) {
        return Category::with('books')->findOrFail($id);
    }

    public function create(array $data) {
        $category = Category::create($data);
        return $category;
    }

    public function update(int $id, array $data) {
        $category = Category::findOrFail($id);
        $category->update($data);
        return $category;
    }
}
