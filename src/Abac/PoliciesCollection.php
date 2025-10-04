<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Abac;

use Countable;
use IteratorAggregate;
use jschreuder\MiddleAuth\Util\CollectionTrait;

final class PoliciesCollection implements IteratorAggregate, Countable
{
    use CollectionTrait;

    public function __construct(
        PolicyInterface ...$policies
    ) {
        $this->collection = $policies;
    }
}
