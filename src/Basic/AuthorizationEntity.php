<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Basic;

use InvalidArgumentException;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;

final class AuthorizationEntity implements AuthorizationEntityInterface
{
    public function __construct(
        private string $type,
        private string $id,
        private array $attributes = []
    ) {
        if (empty(trim($type))) {
            throw new InvalidArgumentException('Type of AuthorizationEntity cannot be empty.');
        }
        if (empty(trim($id))) {
            throw new InvalidArgumentException('ID of AuthorizationEntity cannot be empty.');
        }
    }

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
