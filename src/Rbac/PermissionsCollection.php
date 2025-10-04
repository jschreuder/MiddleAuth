<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Rbac;

use Countable;
use IteratorAggregate;
use jschreuder\MiddleAuth\Util\CollectionTrait;

final class PermissionsCollection implements IteratorAggregate, Countable
{
    use CollectionTrait;

    public function __construct(
        PermissionInterface ...$permissions
    ) {
        $this->collection = $permissions;
    }
}
