<?php

namespace App\Domains\Product\Services;

use App\Domains\Landing\Models\LandingPage;
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

                // Variant deletion is intentionally handled via a separate explicit bulk action or endpoint if ever needed.
            }

            return $product->load('variants');
        });
    }

    public function toggleActiveStatus(Product $product): Product
    {
        $product->update([
            'is_active' => !$product->is_active,
        ]);

        return $product->fresh();
    }

    /**
     * Toggle the landing-page flag and keep the LandingPage table in sync.
     *
     * ENABLE:
     *   1. updateOrCreate a LandingPage row keyed on the slug, reusing any
     *      existing content the admin already filled in (is_active → true).
     *   2. Persist the slug on the Product and flip is_landing_enabled = true.
     *
     * DISABLE:
     *   1. Find the matching LandingPage by slug and set is_active = false.
     *   2. Flip is_landing_enabled = false. Slug is intentionally kept in both
     *      tables so re-enabling is instant with all content intact.
     *
     * The whole operation is wrapped in a single transaction — both records
     * either succeed together or neither changes.
     */
    public function toggleLandingStatus(Product $product, ?string $landingSlug = null): Product
    {
        $enabling = !$product->is_landing_enabled;

        return DB::transaction(function () use ($product, $enabling, $landingSlug): Product {

            if ($enabling) {
                // Slug is required when enabling (validated at controller layer).
                $slug = $landingSlug;

                // Reuse an existing LandingPage row (preserving any content
                // the admin already edited: title, hero_image, blade_template, …)
                // or scaffold a clean default row if none exists yet.
                $landing = LandingPage::firstOrNew(['slug' => $slug]);

                if (! $landing->exists) {
                    // Brand-new row — apply all scaffold defaults.
                    $landing->type           = LandingPage::TYPE_PRODUCT;
                    $landing->product_id     = $product->id;
                    $landing->title          = $product->name;
                    $landing->blade_template = 'product-default';
                }

                // Always sync these two fields regardless of whether the row is new or existing.
                $landing->product_id = $product->id; // keep foreign key correct if slug was ever reused
                $landing->is_active  = true;
                $landing->save();

                $product->update([
                    'landing_slug'       => $slug,
                    'is_landing_enabled' => true,
                ]);

            } else {
                // Deactivate the matching LandingPage (if it exists).
                // We match on the stored slug so the landing page can't be
                // reached via redirect or direct URL while the product flag is off.
                if ($product->landing_slug) {
                    LandingPage::where('slug', $product->landing_slug)
                        ->update(['is_active' => false]);
                }

                $product->update(['is_landing_enabled' => false]);
                // landing_slug is intentionally left intact.
            }

            return $product->fresh();
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
