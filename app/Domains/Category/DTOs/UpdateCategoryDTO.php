<?php

namespace App\Domains\Category\DTOs;

final class UpdateCategoryDTO 
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $slug = null,
    ){}

    public static function fromRequest(array $data): self 
    {
        return new self(
            name: $data['name'] ?? null,
            slug: $data['slug'] ?? null,
        );
    }

    // Chỉ trả field khác null -> phục vụ partial update
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'slug' => $this->slug,
        ], fn($value) => $value !== null);
    }
}