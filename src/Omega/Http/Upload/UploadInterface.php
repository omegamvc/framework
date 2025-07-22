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

/**
 * Interface UploadInterface
 *
 * Defines the contract for file upload components within the Omega MVC framework.
 * Any class that implements this interface is expected to provide methods for configuring,
 * executing, and managing the file upload process.
 *
 * This includes setting upload constraints (e.g. allowed file types, size limits),
 * managing file destinations, retrieving upload status and error messages, and handling
 * utility operations such as deleting files or creating directories.
 *
 * This interface is designed to abstract away the implementation details of
 * single or multiple file uploads, ensuring a consistent API for all file
 * upload components regardless of the underlying mechanism (native PHP, test simulation, etc.).
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
interface UploadInterface
{
    /**
     * Deletes a file from the filesystem.
     *
     * @param string $url The full file path to delete.
     * @return bool True if the file was successfully deleted; false otherwise.
     */
    public function delete(string $url): bool;

    /**
     * Creates a directory if it does not already exist.
     *
     * @param string $path The directory path to create.
     * @return bool True if the directory was created; false if it already exists or creation failed.
     */
    public function createFolder(string $path): bool;

    /**
     * Returns the status of the upload.
     *
     * @return bool True if the file was successfully uploaded; false otherwise.
     */
    public function success(): bool;

    /**
     * Retrieves the latest error message related to the upload process.
     *
     * @return string The error message, or an empty string if no error occurred.
     */
    public function getError(): string;

    /**
     * Gets the list of allowed file extensions for upload.
     *
     * @return array<int, string> List of permitted file extensions (e.g., ['jpg', 'png']).
     */
    public function getFileTypes(): array;

    /**
     * Sets the base name of the file to be uploaded, excluding the extension.
     * The file name will be sanitized to ensure it is safe for use in URLs and file systems.
     *
     * @param string $fileName The file name without extension.
     * @return $this Fluent interface for method chaining.
     */
    public function setFileName(string $fileName): self;

    /**
     * Sets the destination folder path where the uploaded file will be saved.
     *
     * Note: This method does not create the directory if it does not exist.
     *
     * @param string $folderLocation The absolute or relative folder path.
     * @return $this Fluent interface for method chaining.
     */
    public function setFolderLocation(string $folderLocation): self;

    /**
     * Defines the allowed file extensions for upload (e.g., jpg, png, pdf).
     *
     * @param array<int, string> $extensions A list of permitted file extensions.
     * @return $this Fluent interface for method chaining.
     */
    public function setFileTypes(array $extensions): self;

    /**
     * Defines the allowed MIME types for upload (e.g., image/jpeg, application/pdf).
     *
     * @param array<int, string> $mimes A list of permitted MIME types.
     * @return $this Fluent interface for method chaining.
     */
    public function setMimeTypes(array $mimes): self;

    /**
     * Sets the maximum file size allowed for upload.
     *
     * @param int $byte The maximum file size in bytes.
     * @return $this Fluent interface for method chaining.
     */
    public function setMaxFileSize(int $byte): self;

    /**
     * Enables test mode for the upload.
     *
     * When enabled, the upload will use `copy()` instead of `move_uploaded_file()` for simulation purposes.
     * Useful in test environments or CLI uploads.
     *
     * @param bool $markUploadTest True to enable test mode, false to use native upload behavior.
     * @return $this Fluent interface for method chaining.
     */
    public function markTest(bool $markUploadTest): self;
}
