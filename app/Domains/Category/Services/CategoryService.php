<?php

namespace App\Domains\Category\Services;

use App\Domains\Category\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    private string $path = 'categories';

    public function create(array $data): Category
    {
        $data['slug'] = $this->generateUniqueSlug($data['name']);

        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            $data['image'] = $data['image']->store($this->path, 'public');
        }

        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        if (isset($data['name']) && $data['name'] !== $category->name) {
            $data['slug'] = $this->generateUniqueSlug($data['name'], $category->id);
        }

        if (isset($data['image']) && $data['image'] instanceof \Illuminate\Http\UploadedFile) {
            // Delete old image if it exists
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $data['image']->store($this->path, 'public');
        }

        $category->update($data);
        return $category;
    }

    public function delete(Category $category): void
    {
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        $category->delete();
    }

    private function generateUniqueSlug(string $name, int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (Category::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $originalSlug . '-' . $count++;
        }

        return $slug;
    }
}
