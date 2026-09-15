<?php

namespace App\Domains\Category\DTOs;

final class CreateCategoryDTO 
{
    public function __construct(
        public readonly string $name,
        public readonly ?int $level = 0,
        public readonly bool $isActive = true,
        public readonly ?int $parentId = null,
        public readonly ?string $slug = null,
        public readonly ?int $sortOrder = 0,
    ){}

    public static function fromRequest(array $data): self 
    {
        return new self(
            name: $data['name'],
            level: $data['level'] ?? 0,
            isActive: (bool) ($data['is_active'] ?? true),
            parentId: $data['parent_id'] ?? null,
            slug: $data['slug'] ?? null,
            sortOrder: $data['sort_order'] ?? 0,
        );
    }
}