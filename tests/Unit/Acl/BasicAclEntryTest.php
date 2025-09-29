<?php

use jschreuder\MiddleAuth\Acl\BasicAclEntry;
use jschreuder\MiddleAuth\AuthorizationEntityInterface;

beforeEach(function () {
    $this->actor = Mockery::mock(AuthorizationEntityInterface::class);
    $this->actor->shouldReceive('getType')->andReturn('user');
    $this->actor->shouldReceive('getId')->andReturn('123');
    $this->resource = Mockery::mock(AuthorizationEntityInterface::class);
    $this->resource->shouldReceive('getType')->andReturn('post');
    $this->resource->shouldReceive('getId')->andReturn('456');
    $this->action = 'view';
    $this->context = ['key' => 'value'];
});

describe('BasicAclEntry', function () {
    it('matches actor with exact match', function () {
        $entry = new BasicAclEntry('user::123', 'post::456', $this->action, null);
        expect($entry->matchesActor($this->actor))->toBeTrue();
    });

    it('matches actor with wildcard', function () {
        $entry = new BasicAclEntry('user::*', 'post::456', $this->action, null);
        expect($entry->matchesActor($this->actor))->toBeTrue();
    });

    it('does not match actor with different type', function () {
        $entry = new BasicAclEntry('admin::*', 'post::456', $this->action, null);
        expect($entry->matchesActor($this->actor))->toBeFalse();
    });

    it('matches resource with exact match', function () {
        $entry = new BasicAclEntry('user::123', 'post::456', $this->action, null);
        expect($entry->matchesResource($this->resource))->toBeTrue();
    });

    it('matches resource with wildcard', function () {
        $entry = new BasicAclEntry('user::123', 'post::*', $this->action, null);
        expect($entry->matchesResource($this->resource))->toBeTrue();
    });

    it('does not match resource with different type', function () {
        $entry = new BasicAclEntry('user::123', 'comment::*', $this->action, null);
        expect($entry->matchesResource($this->resource))->toBeFalse();
    });

    it('matches action with exact match', function () {
        $entry = new BasicAclEntry('user::123', 'post::456', $this->action, null);
        expect($entry->matchesAction($this->action))->toBeTrue();
    });

    it('matches action with wildcard', function () {
        $entry = new BasicAclEntry('user::123', 'post::456', '*', null);
        expect($entry->matchesAction('any_action'))->toBeTrue();
    });

    it('does not match action with different action', function () {
        $entry = new BasicAclEntry('user::123', 'post::456', 'edit', null);
        expect($entry->matchesAction($this->action))->toBeFalse();
    });

    it('matches context with no context matcher', function () {
        $entry = new BasicAclEntry('user::123', 'post::456', $this->action, null);
        expect($entry->matchesContext($this->context))->toBeTrue();
    });

    it('matches context with context matcher returning true', function () {
        $contextMatcher = function (array $context) { return true; };

        $entry = new BasicAclEntry('user::123', 'post::456', $this->action, $contextMatcher);
        expect($entry->matchesContext($this->context))->toBeTrue();
    });

    it('does not match context with context matcher returning false', function () {
        $contextMatcher = function (array $context) { return false; };

        $entry = new BasicAclEntry('user::123', 'post::456', $this->action, $contextMatcher);
        expect($entry->matchesContext($this->context))->toBeFalse();
    });
});