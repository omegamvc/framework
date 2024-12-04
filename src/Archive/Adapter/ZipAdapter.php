<?php

declare(strict_types=1);

namespace Omega\Archive;

use ZipArchive;
use RuntimeException;

class ZipAdapter implements ArchiveAdapterInterface
{
    protected ZipArchive $zipArchive;

    protected string $zipFile;

    public function __construct(string $zipFile)
    {
        $this->zipFile    = $zipFile;
        $this->zipArchive = new ZipArchive();

        if ($this->zipArchive->open($this->zipFile, ZipArchive::CREATE) !== true) {
            throw new RuntimeException('Unable to open the zip file.');
        }
    }

    public function open(string $file): void
    {
        if ($this->zipArchive->open($file) !== true) {
            throw new RuntimeException('Unable to open the zip file.');
        }
    }

    public function close(): void
    {
        $this->zipArchive->close();
    }

    public function read(string $key): string|bool
    {
        return $this->zipArchive->getFromName($key) ?: false;
    }

    public function write(string $key, string $content): int|bool
    {
        if ($this->zipArchive->addFromString($key, $content) === false) {
            return false;
        }

        return strlen($content);
    }

    public function delete(string $key): bool
    {
        return $this->zipArchive->deleteName($key);
    }

    public function exists(string $key): bool
    {
        return $this->zipArchive->locateName($key) !== false;
    }

    public function keys(): array
    {
        $keys = [];
        for ($i = 0; $i < $this->zipArchive->numFiles; ++$i) {
            $keys[] = $this->zipArchive->getNameIndex($i);
        }

        return $keys;
    }

    public function isDirectory(string $key): bool
    {
        return str_ends_with($key, '/');
    }

    public function mtime(string $key): int|bool
    {
        $stat = $this->zipArchive->statName($key);

        return $stat['mtime'] ?? false;
    }

    public function rename(string $sourceKey, string $targetKey): bool
    {
        return $this->zipArchive->renameName($sourceKey, $targetKey);
    }
}
