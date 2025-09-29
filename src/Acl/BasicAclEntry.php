<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Acl;

use Closure;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;

final class BasicAclEntry implements AclEntryInterface
{
    public function __construct(
        private string $actorMatcher,
        private string $resourceMatcher,
        private string $actionMatcher,
        private ?Closure $contextMatcher
    )
    {
    }

    /**
     * Can match in 3 ways: a single '*' matches everything, ending on '::*' 
     * means it will only have to match the type, otherwise it needs to be a
     * full match.
     */
    public function matchesActor(AuthorizationEntityInterface $actor): bool
    {
        if ($this->actorMatcher === '*') {
            return true;
        } elseif (substr($this->actorMatcher, -3, 3) === '::*') {
            return $actor->getType() === substr($this->actorMatcher, 0, -3);
        }
        return $actor->getType().'::'.$actor->getId() === $this->actorMatcher;
    }

    /**
     * Can match in 3 ways: a single '*' matches everything, ending on '::*' 
     * means it will only have to match the type, otherwise it needs to be a
     * full match.
     */
    public function matchesResource(AuthorizationEntityInterface $resource): bool
    {
        if ($resource === '*') {
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

    /**
     * Finally the context can add aditional constraints if all the previous
     * checks match.
     */
    public function matchesContext(?array $context): bool
    {
        if (!is_null($this->contextMatcher)) {
            return ($this->contextMatcher)($context);
        }
        return true;
    }
}
