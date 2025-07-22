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

use function copy;
use function file_exists;
use function in_array;
use function mkdir;
use function move_uploaded_file;
use function uniqid;
use function unlink;

/**
 * AbstractUpload
 *
 * An abstract class for handling file uploads using PHP's native `move_uploaded_file()` function.
 * Designed to simplify and standardize file upload handling in the Omega framework, this class provides
 * common properties and mechanisms for processing both single and multiple file uploads.
 *
 * Subclasses should implement the actual upload logic based on their specific requirements.
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
abstract class AbstractUpload implements UploadInterface
{
    /** @var array<string, array<int, string>|string> Captured uploaded files from the global $_FILES array. */
    protected array $files;

    /** @var bool Indicates whether the upload was successful. */
    protected bool $success = false;

    /** @var bool Indicates whether the upload process has been executed. */
    protected bool $isset = false;

    /** @var bool Indicates if the class is running in test mode. */
    protected bool $test = false;

    /** @var bool True if handling multiple file uploads, false for single file uploads. */
    protected bool $isMulti = false;

    /** @var string[] The uploaded file names. */
    protected array $fileName;

    /** @var string[] The MIME types of the uploaded files. */
    protected array $fileType;

    /** @var string[] Temporary locations of the uploaded files. */
    protected array $fileTmp;

    /** @var int[] Upload error codes as defined by the PHP upload system. */
    protected array $fileError;

    /** @var int[] Sizes of the uploaded files in bytes. */
    protected array $fileSize;

    /** @var string[] File extensions of the uploaded files. */
    protected array $fileExtension;

    /** @var string The base name (without extension) of the target file. */
    protected string $uploadName;

    /** @var string The directory path where files should be saved. */
    protected string $uploadLocation = '/';

    /** @var array<int, string> Allowed file extensions for upload. */
    protected array $uploadTypes = ['jpg', 'jpeg', 'png'];

    /** @var array<int, string> Allowed MIME types for upload. */
    protected array $uploadMime = ['image/jpg', 'image/jpeg', 'image/png'];

    /** @var int Maximum allowed file size in bytes. */
    protected int $uploadSizeMax = 50000;

    /** @var string Error message set during the upload process, if any. */
    protected string $errorMessage = '';

    /**
     * Constructs the upload handler instance and prepares internal structures.
     *
     * @param array<string, string|int|array<string>|array<int>> $files The `$_FILES` array for a single input field.
     * @return void
     */
    public function __construct(array $files)
    {
        $this->uploadName = uniqid('uploaded_'); // files name without extension

        $this->files = $files;
    }

    /**
     * Validates the uploaded files based on:
     * - file error status,
     * - file extension,
     * - MIME type,
     * - file size.
     *
     * If a validation error occurs, an appropriate error message is set.
     *
     * @return bool True if the files passed all validation checks; false otherwise.
     */
    protected function validate(): bool
    {
        // check file error
        foreach ($this->fileError as $error) {
            $fileError = $error === 4;
            if ($fileError) {
                $this->errorMessage = 'no file upload';

                return false;
            }
        }

        // check file type (upload_type must set)
        foreach ($this->fileExtension as $extension) {
            $extensionError = !in_array($extension, $this->uploadTypes);
            if ($extensionError) {
                $this->errorMessage = 'file type not support';

                return false;
            }
        }

        // check mime type (upload_mime must set)
        foreach ($this->fileType as $type) {
            $mimeError = !in_array($type, $this->uploadMime);
            if ($mimeError) {
                $this->errorMessage = 'file type not support';

                return false;
            }
        }

        // check file size
        foreach ($this->fileSize as $size) {
            $isSizeError = $size > $this->uploadSizeMax;
            if ($isSizeError) {
                $this->errorMessage = 'file size too large';

                return false;
            }
        }

        $this->errorMessage = 'success';

        return true;
    }

    /**
     * Uploads files to the server using `move_uploaded_file()` or `copy()` in test mode.
     *
     * This method validates the uploaded files before attempting to store them.
     * The resulting file paths are returned on success.
     *
     * @return array<int, string> List of destination paths for successfully uploaded files.
     */
    protected function stream(): array
    {
        // isset property, enable when data has been validated
        $this->isset  = true;
        $destinations = [];

        if (!$this->validate()) {
            return $destinations;
        }

        if ($this->test) {
            foreach ($this->fileExtension as $key => $extension) {
                $suffix        = $this->isMulti ? $key : '';
                $destination   =  $this->uploadLocation . $this->uploadName . $suffix . '.' . $extension;
                $this->success = copy($this->fileTmp[$key], $destination);

                $destinations[] = $destination;
            }

            return $destinations;
        }

        if ($this->test === false) {
            foreach ($this->fileExtension as $key => $extension) {
                $suffix        = $this->isMulti ? $key : '';
                $destination   =  $this->uploadLocation . $this->uploadName . $suffix . '.' . $extension;
                $this->success = move_uploaded_file($this->fileTmp[$key], $destination);

                $destinations[] = $destination;
            }

            return $destinations;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $url): bool
    {
        return file_exists($url) && unlink($url);
    }

    /**
     * {@inheritdoc}
     */
    public function createFolder(string $path): bool
    {
        return !file_exists($path) && mkdir($path, 0777, true);
    }

    /**
     * {@inheritdoc}
     */
    public function success(): bool
    {
        return $this->success;
    }

    /**
     * {@inheritdoc}
     */
    public function getError(): string
    {
        return $this->errorMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function getFileTypes(): array
    {
        return $this->uploadTypes;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function setFileName(string $fileName): self;

    /**
     * {@inheritdoc}
     */
    abstract public function setFolderLocation(string $folderLocation): self;

    /**
     * {@inheritdoc}
     */
    abstract public function setFileTypes(array $extensions): self;

    /**
     * {@inheritdoc}
     */
    abstract public function setMimeTypes(array $mimes): self;

    /**
     * {@inheritdoc}
     */
    abstract public function setMaxFileSize(int $byte): self;

    /**
     * {@inheritdoc}
     */
    abstract public function markTest(bool $markUploadTest): self;
}
