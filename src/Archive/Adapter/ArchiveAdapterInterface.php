<?php

declare(strict_types=1);

namespace Omega\Archive;

interface ArchiveAdapterInterface
{
    public function open(string $file): void;

    public function close(): void;

    public function read(string $key): string|bool;

    public function write(string $key, string $content): int|bool;

    public function delete(string $key): bool;

    public function exists(string $key): bool;

    public function keys(): array;

    public function isDirectory(string $key): bool;

    public function mtime(string $key): int|bool;

    public function rename(string $sourceKey, string $targetKey): bool;
}
