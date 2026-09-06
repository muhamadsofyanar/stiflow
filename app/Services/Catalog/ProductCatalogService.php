<?php

namespace App\Services\Catalog;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductCatalogService
{
    public function publish(Product $product): Product
    {
        $product->is_published = true;
        if ($product->published_at === null) {
            $product->published_at = now();
        }
        if (empty($product->slug)) {
            $product->slug = $this->generateUniqueSlug($product->name);
        }
        $product->save();

        return $product;
    }

    public function unpublish(Product $product): Product
    {
        $product->is_published = false;
        $product->save();

        return $product;
    }

    public function updateCatalogFields(Product $product, array $fields): Product
    {
        $safe = [
            'name', 'description', 'meta_description',
            'sort_order', 'is_catalog_visible',
        ];

        foreach ($safe as $key) {
            if (array_key_exists($key, $fields)) {
                $product->$key = $fields[$key];
            }
        }

        if (empty($product->slug) && ! empty($product->name)) {
            $product->slug = $this->generateUniqueSlug($product->name, $product->id);
        }

        $product->save();

        return $product;
    }

    public function bulkReorder(array $orders): void
    {
        foreach ($orders as $item) {
            if (empty($item['id']) || ! isset($item['sort_order'])) {
                continue;
            }
            Product::query()
                ->where('id', $item['id'])
                ->update(['sort_order' => (int) $item['sort_order']]);
        }
    }

    public function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $base = Str::slug($name, '-');
        if ($base === '') {
            $base = 'product-'.Str::lower(Str::random(6));
        }

        $slug = $base;
        $counter = 1;
        do {
            $query = Product::query()->where('slug', $slug);
            if ($excludeId !== null) {
                $query->where('id', '!=', $excludeId);
            }
            $exists = $query->exists();
            if ($exists) {
                $slug = $base.'-'.$counter;
                $counter++;
            }
        } while ($exists);

        return $slug;
    }
}
