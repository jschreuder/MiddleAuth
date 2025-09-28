<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Acl\AccessControlListInterface;
use jschreuder\MiddleAuth\Acl\AclEntryInterface;
use jschreuder\MiddleAuth\Acl\BasicAccessControlList;

describe('BasicAccessControlList', function () {
    it('implements AccessControlListInterface', function () {
        $acl = new BasicAccessControlList();
        expect($acl)->toBeInstanceOf(AccessControlListInterface::class);
    });

    it('can be instantiated with AclEntryInterface objects', function () {
        $aclEntry = Mockery::mock(AclEntryInterface::class);
        $acl = new BasicAccessControlList($aclEntry);
        expect($acl)->toBeInstanceOf(BasicAccessControlList::class);
    });

    it('returns true when an AclEntry matches all conditions', function () {
        $aclEntry = Mockery::mock(AclEntryInterface::class);
        $aclEntry->shouldReceive('matchesActor')->with('user1')->andReturn(true);
        $aclEntry->shouldReceive('matchesResource')->with('resource1')->andReturn(true);
        $aclEntry->shouldReceive('matchesAction')->with('read')->andReturn(true);
        $aclEntry->shouldReceive('matchesContext')->with(null)->andReturn(true);

        $acl = new BasicAccessControlList($aclEntry);
        $result = $acl->hasAccess('user1', 'resource1', 'read');
        expect($result)->toBeTrue();
    });

    it('returns false when no AclEntry matches all conditions', function () {
        $aclEntry = Mockery::mock(AclEntryInterface::class);
        $aclEntry->shouldReceive('matchesActor')->with('user1')->andReturn(false);

        $acl = new BasicAccessControlList($aclEntry);
        $result = $acl->hasAccess('user1', 'resource1', 'read');
        expect($result)->toBeFalse();
    });

    it('returns true when any AclEntry matches all conditions with multiple entries', function () {
        $aclEntry1 = Mockery::mock(AclEntryInterface::class);
        $aclEntry1->shouldReceive('matchesActor')->with('user1')->andReturn(false);

        $aclEntry2 = Mockery::mock(AclEntryInterface::class);
        $aclEntry2->shouldReceive('matchesActor')->with('user1')->andReturn(true);
        $aclEntry2->shouldReceive('matchesResource')->with('resource1')->andReturn(true);
        $aclEntry2->shouldReceive('matchesAction')->with('read')->andReturn(true);
        $aclEntry2->shouldReceive('matchesContext')->with(null)->andReturn(true);

        $acl = new BasicAccessControlList($aclEntry1, $aclEntry2);
        $result = $acl->hasAccess('user1', 'resource1', 'read');
        expect($result)->toBeTrue();
    });
});
