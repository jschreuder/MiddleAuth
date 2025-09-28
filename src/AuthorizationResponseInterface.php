<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth;

/**
 * Designed as an analogue to PSR-7's responses
 */
interface AuthorizationResponseInterface
{
    public function isPermitted(): bool;
    public function getReason(): ?string;
    public function getHandler(): ?string;
}
