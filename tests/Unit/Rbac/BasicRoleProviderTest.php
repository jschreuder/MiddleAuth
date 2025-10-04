<?php

use jschreuder\MiddleAuth\Rbac\BasicRoleProvider;
use jschreuder\MiddleAuth\Rbac\RoleInterface;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;

afterEach(function () {
    Mockery::close();
});

describe('BasicRoleProvider', function () {
    it('returns roles for actor', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $actor->shouldReceive('getType')->once()->andReturn('user');
        $actor->shouldReceive('getId')->once()->andReturn('123');

        $role1 = Mockery::mock(RoleInterface::class);
        $role2 = Mockery::mock(RoleInterface::class);

        $roleMap = [
            'user::123' => [$role1, $role2],
        ];

        $provider = new BasicRoleProvider($roleMap);
        $roles = $provider->getRolesForActor($actor);

        expect($roles)->toBe([$role1, $role2]);
    });

    it('returns empty array when actor has no roles', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $actor->shouldReceive('getType')->once()->andReturn('user');
        $actor->shouldReceive('getId')->once()->andReturn('456');

        $roleMap = [
            'user::123' => [Mockery::mock(RoleInterface::class)],
        ];

        $provider = new BasicRoleProvider($roleMap);
        $roles = $provider->getRolesForActor($actor);

        expect($roles)->toBeArray()->toBeEmpty();
    });

    it('returns empty array when role map is empty', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $actor->shouldReceive('getType')->once()->andReturn('user');
        $actor->shouldReceive('getId')->once()->andReturn('123');

        $provider = new BasicRoleProvider([]);
        $roles = $provider->getRolesForActor($actor);

        expect($roles)->toBeArray()->toBeEmpty();
    });
});
