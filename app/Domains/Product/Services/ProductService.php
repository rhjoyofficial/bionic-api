<?php

namespace App\Domains\Product\Services;

use App\Domains\Product\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    private string $path = 'products';

    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $data['slug'] = $this->generateUniqueSlug($data['name']);

            if (isset($data['image'])) {
                $data['image'] = $data['image']->store($this->path, 'public');
            }

            $variants = $data['variants'] ?? [];
            unset($data['variants']);

            $product = Product::create($data);
            $product->variants()->createMany($variants);

            return $product->load('variants');
        });
    }

    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            if (isset($data['name']) && $data['name'] !== $product->name) {
                $data['slug'] = $this->generateUniqueSlug($data['name'], $product->id);
            }

            if (isset($data['image'])) {
                if ($product->image) Storage::disk('public')->delete($product->image);
                $data['image'] = $data['image']->store($this->path, 'public');
            }

            $variants = $data['variants'] ?? null;
            unset($data['variants']);

            $product->update($data);

            if ($variants !== null) {
                $product->variants()->delete();
                $product->variants()->createMany($variants);
            }

            return $product->load('variants');
        });
    }

    private function generateUniqueSlug(string $name, int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $count = 1;

        while (Product::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $original . '-' . $count++;
        }
        return $slug;
    }

    public function delete(Product $product): void
    {
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();
    }
}
