<?php

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
 * This class use for upload file to server using move_uploaded_file() function,
 * make with easy use and many items, every one can use and modify this class to improve performance.
 *
 * @author sonypradana@gmail.com
 */
abstract class AbstractUpload
{
    /** @var array<string, array<int, string>|string> Cath files form upload file. */
    protected array $files;

    /** @var bool File upload status. */
    protected bool $success = false;

    /** @var bool File has executed to upload. */
    protected bool $isset = false;

    /** @var bool Detect test mode. */
    protected bool $test = false;

    /** @var bool Detect single or multi upload files. True if multi file upload. */
    protected bool $isMulti = false;

    /** @var string[] */
    protected array $fileName;

    /** @var string[] Original file category */
    protected array $fileType;

    /** @var string[] Original file temp location */
    protected array $fileTmp;

    /** @var int[] Original file error status code */
    protected array $fileError;

    /** @var int[] Original file size in byte */
    protected array $fileSize;

    /** @var string[] Original file extension */
    protected array $fileExtension;

    /** @var string Upload file name (without extension) */
    protected string $uploadName;

    /** @var string Upload file to save location */
    protected string $uploadLocation = '/';

    /** @var array<int, string> Upload allow file extension */
    protected array $uploadTypes = ['jpg', 'jpeg', 'png'];

    /** @var array<int, string> Upload allow file mime type */
    protected array $uploadMime = ['image/jpg', 'image/jpeg', 'image/png'];

    /** @var int Upload max file size */
    protected int $uploadSizeMax = 50000;

    /** @var string Provide error message. */
    protected string $errorMessage = '';

    /**
     * Set file name (without extension).
     * File name will convert to allow string url.
     *
     * @param string $fileName File name (without extension)
     * @return self
     */
    abstract public function setFileName(string $fileName): self;

    /**
     * File to save/upload location (server folder),
     * Warning:: not creat new folder if location not exists.
     *
     * @param string $folderLocation Upload file to save location
     * @return self
     */
    abstract public function setFolderLocation(string $folderLocation): self;

    /**
     * List allow file extension to upload.
     *
     * @param array<int, string> $extensions list extension file
     * @return self
     */
    abstract public function setFileTypes(array $extensions): self;

    /**
     * List allow file mime type to upload.
     *
     * @param array<int, string> $mimes list mime type file.
     * @return self
     */
    abstract public function setMimeTypes(array $mimes): self;

    /**
     * Max file size to upload (in byte).
     *
     * @param int $byte max file size upload
     * @return self
     */
    abstract public function setMaxFileSize(int $byte): self;

    /**
     * If true, upload determinate using `copy` instance of `move_uploaded_file`.
     *
     * @param bool $markUploadTest true use copy file
     * @return self
     */
    abstract public function markTest(bool $markUploadTest): self;

    /**
     * File Upload status.
     *
     * @return bool True on file upload success
     */
    public function success(): bool
    {
        return $this->success;
    }

    /**
     * Error message file upload status.
     *
     * @return string Give url file location
     */
    public function getError(): string
    {
        return $this->errorMessage;
    }

    /**
     * Get uploaded file types.
     *
     * @return array<int, string>
     */
    public function getFileTypes(): array
    {
        return $this->uploadTypes;
    }

    /**
     * Creat New file upload to server.
     *
     * @param array<string, string|int|array<string>|array<int>> $files Super global FILE (single array)
     */
    public function __construct(array $files)
    {
        // random files name by default
        $this->uploadName = uniqid('uploaded_'); // files name without extension

        $this->files = $files;
    }

    /**
     * Helper to validate file upload base on configure
     * - check file error
     * - check extension / mime (optional)
     * - check max size.
     *
     * also return error message
     *
     * @return bool True on error found not found
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
     * Upload file to server using move_uploaded_file.
     *
     * @return string[] File location on success upload file, sting empty when unsuccess upload
     */
    protected function stream(): array
    {
        // isset property, enable when data has been validated
        $this->isset = true;
        $destinations = [];

        if (!$this->validate()) {
            return $destinations;
        }

        if ($this->test) {
            foreach ($this->fileExtension as $key => $extension) {
                $suffix        = $this->isMulti ? $key : '';
                $destination   = $this->uploadLocation . $this->uploadName . $suffix . '.' . $extension;
                $this->success = copy($this->fileTmp[$key], $destination);

                $destinations[] = $destination;
            }

            return $destinations;
        }

        if ($this->test === false) {
            foreach ($this->fileExtension as $key => $extension) {
                $suffix        = $this->isMulti ? $key : '';
                $destination   = $this->uploadLocation . $this->uploadName . $suffix . '.' . $extension;
                $this->success = move_uploaded_file($this->fileTmp[$key], $destination);

                $destinations[] = $destination;
            }

            return $destinations;
        }
    }

    /**
     * Helper to delete file if needed.
     *
     * @param string $url
     * @return bool True on success deleted file
     */
    public function delete(string $url): bool
    {
        /** @noinspection PhpTernaryExpressionCanBeReplacedWithConditionInspection */
        return file_exists($url)
            ? unlink($url)
            : false;
    }

    /**
     * Helper to creat new folder if needed.
     *
     * @return bool True on success created folder
     */
    public function creatFolder(string $path): bool
    {
        return !file_exists($path) && mkdir($path, 0777, true);
    }
}
