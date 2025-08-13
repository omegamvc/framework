<?php

declare(strict_types=1);

namespace Omega\Http\Upload;

use Omega\Http\Exceptions\FileNotExistsException;
use Omega\Http\Exceptions\FileNotUploadedException;
use Omega\Http\Exceptions\FolderNotExistsException;
use function explode;
use function file_get_contents;
use function is_dir;
use function strtolower;
use function urlencode;

/** {@inheritDoc} */
final class UploadMultiFile extends AbstractUpload
{
    /**
     * {@inheritDoc}
     */
    public function setFileName(string $fileName): self
    {
        // file name without extension
        $fileName         = urlencode($fileName);
        $this->uploadName = $fileName;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setFolderLocation(string $folderLocation): self
    {
        if (!is_dir($folderLocation)) {
            throw new FolderNotExistsException($folderLocation);
        }

        $this->uploadLocation = $folderLocation;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setFileTypes(array $extensions): self
    {
        $this->uploadTypes = $extensions;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setMimeTypes(array $mimes): self
    {
        $this->uploadMime = $mimes;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setMaxFileSize(int $byte): self
    {
        $this->uploadSizeMax = $byte;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function markTest(bool $markUploadTest): self
    {
        $this->test = $markUploadTest;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function __construct(array $files)
    {
        parent::__construct($files);

        if (is_array($files['name'])) {
            $this->fileName  = $files['name'];
            $this->fileType  = $files['type'];
            $this->fileTmp   = $files['tmp_name'];
            $this->fileError = $files['error'];
            $this->fileSize  = $files['size'];
            // parse file extension
            foreach ($files['name'] as $name) {
                $extension             = explode('.', $name);
                $this->fileExtension[] = strtolower(end($extension));
            }
        } else {
            /** @noinspection DuplicatedCode */
            $this->fileName[]  = $files['name'];
            $this->fileType[]  = $files['type'];
            $this->fileTmp[]   = $files['tmp_name'];
            $this->fileError[] = $files['error'];
            $this->fileSize[]  = $files['size'];
            // parse files extension
            $extension             = explode('.', $files['name']);
            $this->fileExtension[] = strtolower(end($extension));
        }

        $this->isMulti = true;
    }

    /**
     * Upload file to server using move_uploaded_file.
     *
     * @return string[] File location on success upload file, sting empty when unsuccess upload
     */
    public function uploads(): array
    {
        return $this->stream();
    }

    /**
     * Get all uploaded files content.
     *
     * @return string[]
     */
    public function getAll(): array
    {
        if (!$this->success) {
            throw new FileNotUploadedException();
        }

        $contents = [];

        foreach ($this->fileExtension as $key => $extension) {
            $destination    = $this->uploadLocation . $this->uploadName . $key . '.' . $extension;
            $content        = file_get_contents($destination);

            if (false === $content) {
                throw new FileNotExistsException($destination);
            }
            $contents[] = $content;
        }

        return $contents;
    }
}
