<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Basic;

use jschreuder\MiddleAuth\AuthorizationRequestInterface;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;

final class AuthorizationRequest implements AuthorizationRequestInterface
{
    public function __construct(
        private AuthorizationEntityInterface $subject,
        private AuthorizationEntityInterface $resource,
        private string $action,
        private array $context
    )
    {
    }

    public function getSubject(): AuthorizationEntityInterface
    {
        return $this->subject;
    }

    public function getResource(): AuthorizationEntityInterface
    {
        return $this->resource;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
