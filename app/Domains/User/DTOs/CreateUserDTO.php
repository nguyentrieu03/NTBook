<?php

namespace App\Domains\User\DTOs;

use App\Support\Enums\UserStatusEnum;

final class CreateUserDTO 
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly UserStatusEnum $status = UserStatusEnum::Active,
        public readonly ?string $phone = null,
    ){}

    // Tạo DTO từ dữ liệu đã validate của Form Request
    public static function fromRequest(array $data): self 
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            status: UserStatusEnum::from($data['status'] ?? 'active'),
            phone: $data['phone'] ?? null,
        );
    }
}