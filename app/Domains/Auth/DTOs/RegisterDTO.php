<?php

namespace App\Domains\Auth\DTOs;

use App\Domains\User\DTOs\CreateUserDTO;

final class RegisterDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
        );
    }

    public function toCreateUserDTO(): CreateUserDTO
    {
        return new CreateUserDTO(
            name: $this->name,
            email: $this->email,
            password: $this->password,
        );
    }
}
