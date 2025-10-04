<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Acl;

use Countable;
use IteratorAggregate;
use jschreuder\MiddleAuth\Util\CollectionTrait;

final class AclEntriesCollection implements IteratorAggregate, Countable
{
    use CollectionTrait;

    public function __construct(
        AclEntryInterface ...$aclEntries
    ) {
        $this->collection = $aclEntries;
    }
}
