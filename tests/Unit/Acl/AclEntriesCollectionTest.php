<?php declare(strict_types=1);

use jschreuder\MiddleAuth\Acl\AclEntryInterface;
use jschreuder\MiddleAuth\Acl\AclEntriesCollection;

describe('Acl\AclEntriesCollection', function () {
    afterEach(function () {
        Mockery::close();
    });

    it('can be created empty', function () {
        $collection = new AclEntriesCollection();

        expect($collection->isEmpty())->toBeTrue()
            ->and($collection->count())->toBe(0);
    });

    it ('is countable', function () {
        $aclEntry1 = Mockery::mock(AclEntryInterface::class);
        $aclEntry2 = Mockery::mock(AclEntryInterface::class);
        $aclEntry3 = Mockery::mock(AclEntryInterface::class);
        $collection = new AclEntriesCollection($aclEntry1, $aclEntry2, $aclEntry3);

        expect(count($collection))->toBe(3);
    });

    it('can be created with ACL entries', function () {
        $aclEntry1 = Mockery::mock(AclEntryInterface::class);
        $aclEntry2 = Mockery::mock(AclEntryInterface::class);

        $collection = new AclEntriesCollection($aclEntry1, $aclEntry2);

        expect($collection->isEmpty())->toBeFalse()
            ->and($collection->count())->toBe(2);
    });

    it('is iterable', function () {
        $aclEntry1 = Mockery::mock(AclEntryInterface::class);
        $aclEntry2 = Mockery::mock(AclEntryInterface::class);

        $collection = new AclEntriesCollection($aclEntry1, $aclEntry2);

        $iterations = 0;
        foreach ($collection as $aclEntry) {
            expect($aclEntry)->toBeInstanceOf(AclEntryInterface::class);
            $iterations++;
        }

        expect($iterations)->toBe(2);
    });

    it('returns array of ACL entries', function () {
        $aclEntry1 = Mockery::mock(AclEntryInterface::class);
        $aclEntry2 = Mockery::mock(AclEntryInterface::class);

        $collection = new AclEntriesCollection($aclEntry1, $aclEntry2);

        $array = $collection->toArray();

        expect($array)->toBeArray()
            ->and($array)->toHaveCount(2)
            ->and($array[0])->toBe($aclEntry1)
            ->and($array[1])->toBe($aclEntry2);
    });
});
