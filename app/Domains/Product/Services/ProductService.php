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

            if (isset($data['thumbnail'])) {
                $data['thumbnail'] = $data['thumbnail']->store($this->path, 'public');
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

            if (isset($data['thumbnail'])) {
                if ($product->thumbnail) Storage::disk('public')->delete($product->thumbnail);
                $data['thumbnail'] = $data['thumbnail']->store($this->path, 'public');
            }

            $variants = $data['variants'] ?? null;
            unset($data['variants']);

            $product->update($data);

            if ($variants !== null) {
                $existingIds = [];

                foreach ($variants as $variantData) {
                    if (isset($variantData['id'])) {
                        $product->allVariants()->where('id', $variantData['id'])->update($variantData);
                        $existingIds[] = $variantData['id'];
                    } else {
                        $new = $product->allVariants()->create($variantData);
                        $existingIds[] = $new->id;
                    }
                }

                // Only delete variants that were explicitly removed
                $product->allVariants()->whereNotIn('id', $existingIds)->delete();
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
        if ($product->thumbnail) Storage::disk('public')->delete($product->thumbnail);
        $product->delete();
    }
}
