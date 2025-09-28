<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth;

/**
 * Implementations of this provide generic wrappers around domain entities to
 * allow the authorization system to interact with them, both as a subject and
 * as a resource.
 */
interface AuthorizationEntityInterface
{
    public function getId(): string;
    public function getType(): string;
    public function getAttributes(): array;
}
