<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Rbac\BasicRoleProvider;
use jschreuder\MiddleAuth\Rbac\RoleInterface;
use jschreuder\MiddleAuth\Rbac\RolesCollection;
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
        $rolesCollection = new RolesCollection($role1, $role2);

        $roleMap = [
            'user::123' => $rolesCollection,
        ];

        $provider = new BasicRoleProvider($roleMap);
        $roles = $provider->getRolesForActor($actor);

        expect($roles)->toBe($rolesCollection);
    });

    it('returns empty collection when actor has no roles', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $actor->shouldReceive('getType')->once()->andReturn('user');
        $actor->shouldReceive('getId')->once()->andReturn('456');

        $roleMap = [
            'user::123' => new RolesCollection(Mockery::mock(RoleInterface::class)),
        ];

        $provider = new BasicRoleProvider($roleMap);
        $roles = $provider->getRolesForActor($actor);

        expect($roles)->toBeInstanceOf(RolesCollection::class)
            ->and($roles->isEmpty())->toBeTrue();
    });

    it('returns empty collection when role map is empty', function () {
        $actor = Mockery::mock(AuthorizationEntityInterface::class);
        $actor->shouldReceive('getType')->once()->andReturn('user');
        $actor->shouldReceive('getId')->once()->andReturn('123');

        $provider = new BasicRoleProvider([]);
        $roles = $provider->getRolesForActor($actor);

        expect($roles)->toBeInstanceOf(RolesCollection::class)
            ->and($roles->isEmpty())->toBeTrue();
    });
});
