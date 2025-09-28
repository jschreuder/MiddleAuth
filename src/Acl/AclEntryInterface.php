<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Acl;

interface AclEntryInterface
{
    public function matchesActor(string $actor): bool;
    public function matchesResource(string $resource): bool;
    public function matchesAction(string $action): bool;
    public function matchesContext(?array $context): bool;
}
