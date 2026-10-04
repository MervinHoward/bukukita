<?php

namespace App\Services;

use App\Repositories\CategoryRepository;

class CategoryService {
    private CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function getAll() {
        return $this->categoryRepository->getAll();
    }

    public function getById(int $id) {
        return $this->categoryRepository->getById($id);
    }

    public function create(array $data) {
        $category = $this->categoryRepository->create($data);
        return $category;
    }

    public function update(int $id, array $data) {
        $category = $this->categoryRepository->update($id, $data);
        return $category;
    }
}
