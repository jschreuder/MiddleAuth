<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Basic;

use jschreuder\MiddleAuth\AuthorizationEntityInterface;

final class AuthorizationEntity implements AuthorizationEntityInterface
{
    public function __construct(
        private string $id,
        private string $type,
        private array $attributes = []
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
