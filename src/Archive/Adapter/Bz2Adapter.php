<?php

declare(strict_types=1);

namespace Omega\Archive;

class Bz2Adapter implements ArchiveAdapterInterface
{
    protected string $bz2File;

    public function __construct(string $bz2File)
    {
        $this->bz2File = $bz2File;
    }

    public function open(string $file): void
    {
        $this->bz2File = $file;
    }

    public function close(): void
    {
        // BZ2 non richiede chiusura esplicita
    }

    public function read(string $key): string|bool
    {
        $content = bzdecompress(file_get_contents($this->bz2File));

        return $content ?: false;
    }

    public function write(string $key, string $content): int|bool
    {
        $compressedContent = bzcompress($content);
        if (file_put_contents($this->bz2File, $compressedContent) === false) {
            return false;
        }

        return strlen($compressedContent);
    }

    public function delete(string $key): bool
    {
        // Non supportato per BZ2, richiede gestione esterna
        return false;
    }

    public function exists(string $key): bool
    {
        return file_exists($this->bz2File);
    }

    public function keys(): array
    {
        // Non supportato per BZ2, richiede gestione esterna
        return [];
    }

    public function isDirectory(string $key): bool
    {
        return false; // Non applicabile per BZ2
    }

    public function mtime(string $key): int|bool
    {
        return filemtime($this->bz2File);
    }

    public function rename(string $sourceKey, string $targetKey): bool
    {
        return rename($sourceKey, $targetKey);
    }
}
