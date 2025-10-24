<?php

declare(strict_types=1);

namespace Omega\Database\Schema;

use function array_filter;
use function implode;

abstract class Query
{
    /** @var SchemaConnectionInterface PDO property */
    protected SchemaConnectionInterface $pdo;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->builder();
    }

    /**
     * @return string
     */
    protected function builder(): string
    {
        return '';
    }

    /**
     * @return bool
     */
    public function execute(): bool
    {
        return $this->pdo->query($this->builder())->execute();
    }

    /**
     * Helper: join condition into string.
     *
     * @param string[] $array
     * @param string   $separator
     * @return string
     */
    protected function join(array $array, string $separator = ' '): string
    {
        return implode(
            $separator,
            array_filter($array, fn ($item) => $item !== '')
        );
    }
}
