<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Basic;

use jschreuder\MiddleAuth\AuthorizationResponseInterface;

final class AuthorizationResponse implements AuthorizationResponseInterface
{
    public function __construct(
        private bool $permitted,
        private ?string $reason = null,
        private ?string $handler = null
    )
    {
    }

    public function isPermitted(): bool
    {
        return $this->permitted;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function getHandler(): ?string
    {
        return $this->handler;
    }
}
