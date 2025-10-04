<?php declare(strict_types=1);

namespace jschreuder\MiddleAuth\Abac;

use jschreuder\MiddleAuth\AuthorizationEntityInterface;

final class BasicPolicy implements PolicyInterface
{
    public function __construct(
        private AccessEvaluatorInterface $evaluator,
        private string $description
    ) {}

    public function evaluate(
        AuthorizationEntityInterface $actor,
        AuthorizationEntityInterface $resource,
        string $action,
        array $context
    ): bool
    {
        return $this->evaluator->hasAccess($actor, $resource, $action, $context);
    }

    public function getDescription(): string
    {
        return $this->description;
    }
}
