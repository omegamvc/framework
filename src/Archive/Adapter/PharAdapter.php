<?php

declare(strict_types=1);

namespace Omega\Archive;

use Phar;

class PharAdapter implements ArchiveAdapterInterface
{
    protected Phar $phar;

    public function __construct(string $pharFile)
    {
        $this->phar = new Phar($pharFile);
    }

    public function open(string $file): void
    {
        $this->phar->startBuffering();
        $this->phar->buildFromDirectory(dirname($file));
        $this->phar->stopBuffering();
    }

    public function close(): void
    {
        // Phar gestisce la chiusura automaticamente
    }

    public function read(string $key): string|bool
    {
        return $this->phar->getContents($key) ?: false;
    }

    public function write(string $key, string $content): int|bool
    {
        $this->phar[$key] = $content;

        return strlen($content);
    }

    public function delete(string $key): bool
    {
        unset($this->phar[$key]);

        return true;
    }

    public function exists(string $key): bool
    {
        return isset($this->phar[$key]);
    }

    public function keys(): array
    {
        return array_keys($this->phar->getFiles());
    }

    public function isDirectory(string $key): bool
    {
        return isset($this->phar[$key]) && $this->phar[$key]->isDir();
    }

    public function mtime(string $key): int|bool
    {
        return $this->phar[$key]->getMTime();
    }

    public function rename(string $sourceKey, string $targetKey): bool
    {
        $this->phar[$targetKey] = $this->phar[$sourceKey];
        unset($this->phar[$sourceKey]);

        return true;
    }
}
