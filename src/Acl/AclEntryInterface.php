<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Acl;

use jschreuder\MiddleAuth\AuthorizationEntityInterface;

interface AclEntryInterface
{
    public function matchesActor(AuthorizationEntityInterface $actor): bool;
    public function matchesResource(AuthorizationEntityInterface $resource): bool;
    public function matchesAction(string $action): bool;
    public function matchesContext(?array $context): bool;
}
