<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Acl;

use jschreuder\MiddleAuth\AuthorizationEntityInterface;

final class BasicAccessControlList implements AccessControlListInterface
{
    /** @var AclEntryInterface[] */
    private array $aclEntries;

    public function __construct(AclEntryInterface ...$aclEntries)
    {
        $this->aclEntries = $aclEntries;
    }

    public function hasAccess(
        AuthorizationEntityInterface $actor,
        AuthorizationEntityInterface $resource,
        string $action,
        ?array $context = null
    ): bool
    {
        foreach ($this->aclEntries as $aclEntry) {
            if (
                $aclEntry->matchesActor($actor)
                && $aclEntry->matchesResource($resource)
                && $aclEntry->matchesAction($action)
                && $aclEntry->matchesContext($context)
            ) {
                return true;
            }
        }
        return false;
    }
}
