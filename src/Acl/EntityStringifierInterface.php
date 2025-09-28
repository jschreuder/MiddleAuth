<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Acl;

use jschreuder\MiddleAuth\AuthorizationEntityInterface;

interface EntityStringifierInterface
{
    public function stringifyEntity(AuthorizationEntityInterface $entity): string;
}
