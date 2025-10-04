<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Abac;

use IteratorAggregate;
use jschreuder\MiddleAuth\Util\CollectionTrait;

final class PoliciesCollection implements IteratorAggregate
{
    use CollectionTrait;

    public function __construct(
        PolicyInterface ...$policies
    ) {
        $this->collection = $policies;
    }
}
