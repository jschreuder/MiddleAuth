<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Acl;

use Closure;

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
    public function matchesActor(string $actor): bool
    {
        if ($actor === '*') {
            return true;
        } elseif (substr($this->actorMatcher, -3, 3) === '::*') {
            [$actor, ] = explode('::', $actor, 2);
            return $actor.'::*' === $this->actorMatcher;
        }
        return $actor === $this->actorMatcher;
    }

    /**
     * Can match in 3 ways: a single '*' matches everything, ending on '::*' 
     * means it will only have to match the type, otherwise it needs to be a
     * full match.
     */
    public function matchesResource(string $resource): bool
    {
        if ($resource === '*') {
            return true;
        } elseif (substr($this->resourceMatcher, -3, 3) === '::*') {
            [$resource, ] = explode('::', $resource, 2);
            return $resource.'::*' === $this->resourceMatcher;
        }
        return $resource === $this->resourceMatcher;
    }

    /**
     * Can match in 2 ways: either a single '*' matches everything, or it needs
     * to be a full match.
     */
    public function matchesAction(string $action): bool
    {
        if ($action === '*') {
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
