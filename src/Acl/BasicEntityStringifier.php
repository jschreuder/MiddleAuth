<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Acl;

use jschreuder\MiddleAuth\AuthorizationEntityInterface;

final class BasicEntityStringifier implements EntityStringifierInterface
{
    public function stringifyEntity(AuthorizationEntityInterface $entity): string
    {
        return $entity->getType() . '::' . $entity->getId();
    }
}
