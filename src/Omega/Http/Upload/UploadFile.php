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
use Omega\Http\Exceptions\MutiFileUploadDetectException;

use function explode;
use function file_get_contents;
use function is_array;
use function is_dir;
use function strtolower;
use function urlencode;

/**
 * Handles single file uploads.
 *
 * This class extends the abstract upload logic to support single-file upload workflows.
 * It validates and processes the file provided in a standard `$_FILES` structure.
 *
 * Throws an exception if multiple files are detected.
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
class UploadFile extends AbstractUpload
{
    /**
     * Initializes a new UploadFile instance with a single uploaded file.
     *
     * @param array<string, mixed> $files The `$_FILES` entry for the uploaded file.
     * @return void
     * @throws MutiFileUploadDetectException If multiple files are detected in the input.
     */
    public function __construct(array $files)
    {
        parent::__construct($files);

        if (is_array($files['name'])) {
            throw new MutiFileUploadDetectException();
        }

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

    /**
     * Uploads the file to the target directory using either `move_uploaded_file()`
     * or `copy()` (if test mode is enabled).
     *
     * @return string The destination path of the uploaded file if successful; an empty string otherwise.
     */
    public function upload(): string
    {
        return $this->stream()[0] ?? '';
    }

    /**
     * Retrieves the contents of the uploaded file.
     *
     * This method returns the full content of the uploaded file as a string.
     * It throws an exception if the file was not successfully uploaded or if it cannot be found.
     *
     * @return string The contents of the uploaded file.
     * @throws FileNotUploadedException If the file upload was not marked as successful.
     * @throws FileNotExistsException If the uploaded file cannot be found on disk.
     */
    public function get(): string
    {
        $destination =  $this->uploadLocation . $this->uploadName . '.' . $this->fileExtension[0];

        if (!$this->success) {
            throw new FileNotUploadedException();
        }

        $content = file_get_contents($destination);
        if (false === $content) {
            throw new FileNotExistsException($destination);
        }

        return $content;
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
     * @throws FolderNotExistsException if the folder not exists.
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
