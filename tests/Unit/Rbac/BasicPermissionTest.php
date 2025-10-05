<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Rbac\BasicPermission;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;

afterEach(function () {
    Mockery::close();
});

beforeEach(function () {
    $this->actor = Mockery::mock(AuthorizationEntityInterface::class);
    $this->resource = Mockery::mock(AuthorizationEntityInterface::class);
    $this->resource->shouldReceive('getType')->andReturn('post');
    $this->resource->shouldReceive('getId')->andReturn('456');
    $this->action = 'view';
});

describe('BasicPermission', function () {
    it('matches resource with exact match', function () {
        $permission = new BasicPermission('post::456', $this->action);
        expect($permission->matchesResource($this->resource))->toBeTrue();
    });

    it('matches resource with ID wildcard', function () {
        $permission = new BasicPermission('post::*', $this->action);
        expect($permission->matchesResource($this->resource))->toBeTrue();
    });

    it('matches resource with full wildcard', function () {
        $permission = new BasicPermission('*', $this->action);
        expect($permission->matchesResource($this->resource))->toBeTrue();
    });

    it('does not match resource with different type', function () {
        $permission = new BasicPermission('comment::*', $this->action);
        expect($permission->matchesResource($this->resource))->toBeFalse();
    });

    it('matches action with exact match', function () {
        $permission = new BasicPermission('post::456', $this->action);
        expect($permission->matchesAction($this->action))->toBeTrue();
    });

    it('matches action with wildcard', function () {
        $permission = new BasicPermission('post::456', '*');
        expect($permission->matchesAction('any_action'))->toBeTrue();
    });

    it('does not match action with different action', function () {
        $permission = new BasicPermission('post::456', 'edit');
        expect($permission->matchesAction($this->action))->toBeFalse();
    });
});
