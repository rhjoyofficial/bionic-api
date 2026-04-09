<?php

namespace App\Domains\Product\Services;

use App\Domains\Product\Models\Product;
use Illuminate\Http\UploadedFile;
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

            if (isset($data['thumbnail']) && $data['thumbnail'] instanceof UploadedFile) {
                $data['thumbnail'] = $data['thumbnail']->store($this->path, 'public');
            }

            // Handle gallery uploads
            $data['gallery'] = $this->uploadGallery($data['gallery'] ?? [], []);

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

            if (isset($data['thumbnail']) && $data['thumbnail'] instanceof UploadedFile) {
                if ($product->thumbnail) Storage::disk('public')->delete($product->thumbnail);
                $data['thumbnail'] = $data['thumbnail']->store($this->path, 'public');
            }

            // Handle gallery: remove flagged + upload new
            $currentGallery = $product->gallery ?? [];

            if (!empty($data['gallery_remove'])) {
                foreach ($data['gallery_remove'] as $removePath) {
                    Storage::disk('public')->delete($removePath);
                }
                $currentGallery = array_values(
                    array_filter($currentGallery, fn($p) => !in_array($p, $data['gallery_remove']))
                );
            }
            unset($data['gallery_remove']);

            $newGalleryFiles = $data['gallery'] ?? [];
            $data['gallery'] = $this->uploadGallery($newGalleryFiles, $currentGallery);

            $variants = $data['variants'] ?? null;
            unset($data['variants']);

            $product->update($data);

            if ($variants !== null) {
                $existingIds = [];

                foreach ($variants as $variantData) {
                    if (!empty($variantData['id'])) {
                        $variantId = $variantData['id'];
                        $updateData = array_filter(
                            $variantData,
                            fn($key) => $key !== 'id',
                            ARRAY_FILTER_USE_KEY
                        );
                        $product->allVariants()->where('id', $variantId)->update($updateData);
                        $existingIds[] = $variantId;
                    } else {
                        unset($variantData['id']);
                        $new = $product->allVariants()->create($variantData);
                        $existingIds[] = $new->id;
                    }
                }

                $product->allVariants()->whereNotIn('id', $existingIds)->delete();
            }

            return $product->load('variants');
        });
    }

    private function uploadGallery(array $files, array $existing): array
    {
        $newPaths = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $newPaths[] = $file->store($this->path . '/gallery', 'public');
            }
        }
        return array_merge($existing, $newPaths);
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

        foreach ($product->gallery ?? [] as $galleryPath) {
            Storage::disk('public')->delete($galleryPath);
        }

        $product->delete();
    }
}
