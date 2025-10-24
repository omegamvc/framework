<?php

declare(strict_types=1);

namespace Omega\Database\Query;

abstract class AbstractExecute extends AbstractQuery
{
    /**
     * @return bool
     */
    public function execute(): bool
    {
        $this->builder();

        if ($this->query != null) {
            $this->pdo->query($this->query);
            foreach ($this->binds as $bind) {
                if (!$bind->hasBind()) {
                    $this->pdo->bind($bind->getBind(), $bind->getValue());
                }
            }

            $this->pdo->execute();

            return $this->pdo->rowCount() > 0;
        }

        return false;
    }
}
