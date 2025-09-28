<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth;

/**
 * Designed as an analogue to PSR-7's requests
 */
interface AuthorizationRequestInterface
{
    public function getSubject(): AuthorizationEntityInterface;
    public function getResource(): AuthorizationEntityInterface;
    public function getAction(): string;
    public function getContext(): array;
}
