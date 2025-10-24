<?php

/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */

declare(strict_types=1);

namespace Omega\Database\Query;

use Omega\Database\ConnectionInterface;

use function array_chunk;
use function array_filter;
use function count;
use function implode;

class Insert extends AbstractExecute
{
    /**
     * @var array<string, string>
     */
    private ?array $duplicateKey = null;

    public function __construct(string $tableName, ConnectionInterface $pdo)
    {
        $this->table = $tableName;
        $this->pdo   = $pdo;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->builder();
    }

    /**
     *  Value query builder (key => value).
     *
     * @param array<string, string|int|bool|null> $values Insert values
     * @return self
     */
    public function values(array $values): self
    {
        foreach ($values as $key => $value) {
            $this->value($key, $value);
        }

        return $this;
    }

    /**
     * @param string               $bind
     * @param string|int|bool|null $value
     * @return self
     */
    public function value(string $bind, string|int|bool|null $value): self
    {
        $this->binds[] = Bind::set($bind, $value, $bind)->prefixBind(':bind_');

        return $this;
    }

    /**
     * Added multi rows (values).
     *
     * @param array<int, array<string, string|int|bool|null>> $rows
     * @return self
     */
    public function rows(array $rows): self
    {
        foreach ($rows as $index => $values) {
            foreach ($values as $bind => $value) {
                $this->binds[] = Bind::set($bind, $value, $bind)->prefixBind(':bind_' . $index . '_');
            }
        }

        return $this;
    }

    /**
     * On duplicate key update.
     *
     * @param string      $column
     * @param string|null $value
     * @return self
     */
    public function on(string $column, ?string $value = null): self
    {
        $this->duplicateKey[$column] = $value ?? "VALUES({$column})";

        return $this;
    }

    /**
     * @return string
     */
    protected function builder(): string
    {
        [$binds, ,$columns] = $this->bindsDestructor();

        $stringsBinds = [];
        /** @var array<int, array<int, string>> $chunk */
        $chunk = array_chunk($binds, count($columns), true);
        foreach ($chunk as $group) {
            $stringsBinds[] = '(' . implode(', ', $group) . ')';
        }

        $builds              = [];
        $builds['column']    = '(' . implode(', ', $columns) . ')';
        $builds['values']    = 'VALUES';
        $builds['binds']     = implode(', ', $stringsBinds);
        $builds['keyUpdate'] = $this->getDuplicateKeyUpdate();
        $stringBuild         = implode(' ', array_filter($builds, fn ($item) => $item !== ''));

        $this->query = "INSERT INTO {$this->table} {$stringBuild}";

        return $this->query;
    }

    /**
     * @return string
     */
    private function getDuplicateKeyUpdate(): string
    {
        if (null === $this->duplicateKey) {
            return '';
        }

        $keys = [];
        foreach ($this->duplicateKey as $key => $value) {
            $keys[] = "{$key} = {$value}";
        }

        return 'ON DUPLICATE KEY UPDATE ' . implode(', ', $keys);
    }
}
