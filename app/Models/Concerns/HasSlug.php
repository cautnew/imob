<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Generates a unique, URL-friendly slug from a source column whenever the
 * `slug` attribute is empty — on creation, or on update if a user clears it.
 *
 * Models using this trait must implement `slugSourceColumn()`. They may
 * optionally override `slugScope()` (extra where-constraints for uniqueness,
 * e.g. scoping per company) and `reservedSlugs()` (words the slug must never
 * collide with, e.g. other top-level route segments). A caller-supplied slug
 * (create or update) is respected as-is and never auto-regenerated.
 */
trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::saving(function ($model): void {
            if (! $model->slug) {
                $model->slug = $model->generateUniqueSlug();
            }
        });
    }

    protected function generateUniqueSlug(): string
    {
        $base = Str::slug($this->{$this->slugSourceColumn()}) ?: Str::slug(class_basename($this));
        $slug = $base;
        $i = 2;

        while ($this->slugExists($slug)) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    protected function slugExists(string $slug): bool
    {
        if (in_array($slug, $this->reservedSlugs(), true)) {
            return true;
        }

        return static::query()
            ->where($this->slugScope())
            ->where('slug', $slug)
            ->when($this->exists, fn ($query) => $query->where($this->getKeyName(), '!=', $this->getKey()))
            ->exists();
    }

    /**
     * The column the slug is generated from.
     */
    abstract protected function slugSourceColumn(): string;

    /**
     * Extra where-constraints scoping slug uniqueness (e.g. per company).
     *
     * @return array<string, mixed>
     */
    protected function slugScope(): array
    {
        return [];
    }

    /**
     * Words the generated slug must never collide with.
     *
     * @return array<int, string>
     */
    protected function reservedSlugs(): array
    {
        return [];
    }
}
