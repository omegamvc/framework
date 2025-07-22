<?php

/**
 * Part of Omega - Http Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Omega\Http\Upload;

use Omega\Http\Exceptions\FileNotExistsException;
use Omega\Http\Exceptions\FileNotUploadedException;
use Omega\Http\Exceptions\FolderNotExistsException;

use function end;
use function explode;
use function file_get_contents;
use function is_array;
use function is_dir;
use function strtolower;
use function urlencode;

/**
 * Handles multiple file uploads.
 *
 * This class extends the abstract upload functionality to support the upload and retrieval
 * of multiple files at once. It automatically detects whether a single or multiple files
 * were submitted and initializes internal data accordingly.
 *
 * @category   Omega
 * @package    Http
 * @subpackage Upload
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version    2.0.0
 */
class UploadMultiFile extends AbstractUpload
{
    /**
     * Initializes a new UploadMultiFile instance with uploaded file(s).
     *
     * Automatically detects whether multiple files were submitted and populates the internal
     * file metadata arrays (name, type, tmp_name, error, size, extension).
     *
     * @param array<string, mixed> $files The `$_FILES` array entry representing the uploaded files.
     * @return void
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
     * Retrieves the contents of all uploaded files.
     *
     * Throws an exception if the upload was not successful or if any file cannot be read.
     *
     * @throws FileNotUploadedException If the upload was not successful.
     * @throws FileNotExistsException If any file does not exist on disk.
     * @return string[] An array containing the contents of each uploaded file.
     */
    public function getAll(): array
    {
        if (!$this->success) {
            throw new FileNotUploadedException();
        }

        $contents = [];

        foreach ($this->fileExtension as $key => $extension) {
            $destination    =  $this->uploadLocation . $this->uploadName . $key . '.' . $extension;
            $content        = file_get_contents($destination);

            if (false === $content) {
                throw new FileNotExistsException($destination);
            }
            $contents[] = $content;
        }

        return $contents;
    }

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
     *
     * @throws FolderNotexistsException in folder not exists.
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
}
