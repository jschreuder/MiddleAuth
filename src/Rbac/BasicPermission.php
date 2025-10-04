<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Rbac;

use jschreuder\MiddleAuth\AuthorizationEntityInterface;

final class BasicPermission implements PermissionInterface
{
    public function __construct(
        private string $resourceMatcher,
        private string $actionMatcher
    )
    {
    }

    /**
     * Can match in 3 ways: a single '*' matches everything, ending on '::*'
     * means it will only have to match the type, otherwise it needs to be a
     * full match.
     */
    public function matchesResource(AuthorizationEntityInterface $resource): bool
    {
        if ($this->resourceMatcher === '*') {
            return true;
        } elseif (substr($this->resourceMatcher, -3, 3) === '::*') {
            return $resource->getType() === substr($this->resourceMatcher, 0, -3);
        }
        return $resource->getType().'::'.$resource->getId() === $this->resourceMatcher;
    }

    /**
     * Can match in 2 ways: either a single '*' matches everything, or it needs
     * to be a full match.
     */
    public function matchesAction(string $action): bool
    {
        if ($this->actionMatcher === '*') {
            return true;
        }
        return $action === $this->actionMatcher;
    }
}
