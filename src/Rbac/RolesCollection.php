<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Rbac;

use Countable;
use IteratorAggregate;
use jschreuder\MiddleAuth\Util\CollectionTrait;

final class RolesCollection implements IteratorAggregate, Countable
{
    use CollectionTrait;

    public function __construct(
        RoleInterface ...$roles
    ) {
        $this->collection = $roles;
    }
}
