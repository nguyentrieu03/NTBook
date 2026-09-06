<?php

namespace App\Domains\Category\DTOs;

final class CreateCategoryDTO 
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly ?int $level = 0,
        public readonly ?int $sortOrder = 0,
        public readonly ?int $isActive = 1,
        public readonly ?int $parentId = null,
    ){}

    // Tạo DTO từ dữ liệu đã validate của Form Request
    public static function fromRequest(array $data): self 
    {
        return new self(
            name: $data['name'],
            slug: $data['slug'],
            level: $data['level'] ?? 0,
            sortOrder: $data['sortOrder'] ?? 0,
            isActive: $data['isActive'] ?? 1,
            parentId: $data['parentId'] ?? null,
        );
    }
}