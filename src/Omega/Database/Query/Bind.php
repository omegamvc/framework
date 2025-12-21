<?php

declare(strict_types=1);

namespace Omega\Database\Query;

final class Bind
{
    /** @var string Bind name (required) */
    private string $bind;

    /** @var mixed Bind value (required) */
    private mixed $bindValue;

    /** @var string Column name (required) */
    private string $columnName;

    /** @var string Set refix bind (bind name not same with column name). */
    private string $prefixBind;

    /**
     * @param string $bind
     * @param mixed  $value
     * @param string $columnName
     * @return void
     */
    public function __construct(string $bind, mixed $value, string $columnName = '')
    {
        $this->bind       = $bind;
        $this->bindValue  = $value;
        $this->columnName = $columnName;
        $this->prefixBind = ':';
    }

    /**
     * @param string $bind
     * @param mixed  $value
     * @param string $columnName
     * @return self
     */
    public static function set(string $bind, mixed $value, string $columnName = ''): self
    {
        return new Bind($bind, $value, $columnName);
    }

    /**
     * @param string $prefix
     * @return $this
     */
    public function prefixBind(string $prefix): self
    {
        $this->prefixBind = $prefix;

        return $this;
    }

    /**
     * @param string $bind
     * @return $this
     */
    public function setBind(string $bind): self
    {
        $this->bind = $bind;

        return $this;
    }

    /**
     * @param mixed $bindValue
     * @return $this
     */
    public function setValue(mixed $bindValue): self
    {
        $this->bindValue = $bindValue;

        return $this;
    }

    /**
     * @param string $columnName
     * @return $this
     */
    public function setColumnName(string $columnName): self
    {
        $this->columnName = $columnName;

        return $this;
    }

    /**
     * @return string
     */
    public function getBind(): string
    {
        return $this->prefixBind . $this->bind;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->bindValue;
    }

    /**
     * @return string
     */
    public function getColumnName(): string
    {
        return $this->columnName;
    }

    /**
     * @return bool
     */
    public function hasColumName(): bool
    {
        return '' !== $this->columnName;
    }

    /**
     * @return $this
     */
    public function markAsColumn(): self
    {
        $this->columnName = $this->bind;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasBind(): bool
    {
        return '' === $this->bind;
    }
}
