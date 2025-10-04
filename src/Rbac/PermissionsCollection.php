<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Rbac;

use IteratorAggregate;
use jschreuder\MiddleAuth\Util\CollectionTrait;

final class PermissionsCollection implements IteratorAggregate
{
    use CollectionTrait;

    public function __construct(
        PermissionInterface ...$permissions
    ) {
        $this->collection = $permissions;
    }
}
