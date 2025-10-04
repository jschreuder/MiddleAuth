<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Rbac;

interface RoleInterface
{
    public function getName(): string;
    public function getPermissions(): array;
}
