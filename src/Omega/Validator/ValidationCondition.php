<?php

declare(strict_types=1);

namespace Omega\Validator;

class ValidationCondition
{
    /** @var array<int, string> */
    private array $error;

    /**
     * Helper for catch error validation from if_valid condition.
     *
     * @param false|array<int, string> $error Set error for else condition
     */
    public function __construct(array|false $error)
    {
        $this->error = $error === false ? [] : $error;
    }

    /**
     * Execute else condition closure,
     * when validation is false.
     *
     * Error message send using param closure
     *
     * @param callable(string): void $condition Execute condition
     */
    public function else(callable $condition): void
    {
        call_user_func($condition, $this->error);
    }
}
