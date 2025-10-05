<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Acl\BasicAclEntry;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;

describe('BasicAclEntry', function () {
    beforeEach(function () {
        $this->actor = Mockery::mock(AuthorizationEntityInterface::class);
        $this->actor->shouldReceive('getType')->andReturn('user');
        $this->actor->shouldReceive('getId')->andReturn('123');
        $this->resource = Mockery::mock(AuthorizationEntityInterface::class);
        $this->resource->shouldReceive('getType')->andReturn('post');
        $this->resource->shouldReceive('getId')->andReturn('456');
        $this->action = 'view';
    });

    afterEach(function () {
        Mockery::close();
    });

    it('matches actor with exact match', function () {
        $entry = new BasicAclEntry('user::123', 'post::456', $this->action);
        expect($entry->matchesActor($this->actor))->toBeTrue();
    });

    it('matches actor with ID wildcard', function () {
        $entry = new BasicAclEntry('user::*', 'post::456', $this->action);
        expect($entry->matchesActor($this->actor))->toBeTrue();
    });

    it('matches actor with full wildcard', function () {
        $entry = new BasicAclEntry('*', 'post::456', $this->action);
        expect($entry->matchesActor($this->actor))->toBeTrue();
    });

    it('does not match actor with different type', function () {
        $entry = new BasicAclEntry('admin::*', 'post::456', $this->action);
        expect($entry->matchesActor($this->actor))->toBeFalse();
    });

    it('matches resource with exact match', function () {
        $entry = new BasicAclEntry('user::123', 'post::456', $this->action);
        expect($entry->matchesResource($this->resource))->toBeTrue();
    });

    it('matches resource with ID wildcard', function () {
        $entry = new BasicAclEntry('user::123', 'post::*', $this->action);
        expect($entry->matchesResource($this->resource))->toBeTrue();
    });

    it('matches resource with full wildcard', function () {
        $entry = new BasicAclEntry('user::123', '*', $this->action);
        expect($entry->matchesResource($this->resource))->toBeTrue();
    });

    it('does not match resource with different type', function () {
        $entry = new BasicAclEntry('user::123', 'comment::*', $this->action);
        expect($entry->matchesResource($this->resource))->toBeFalse();
    });

    it('matches action with exact match', function () {
        $entry = new BasicAclEntry('user::123', 'post::456', $this->action);
        expect($entry->matchesAction($this->action))->toBeTrue();
    });

    it('matches action with wildcard', function () {
        $entry = new BasicAclEntry('user::123', 'post::456', '*');
        expect($entry->matchesAction('any_action'))->toBeTrue();
    });

    it('does not match action with different action', function () {
        $entry = new BasicAclEntry('user::123', 'post::456', 'edit');
        expect($entry->matchesAction($this->action))->toBeFalse();
    });
});