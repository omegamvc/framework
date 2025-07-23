<?php

/**
 * Part of Omega - Tests\Http Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Http\Upload;

use Omega\Http\Exceptions\FolderNotExistsException;
use Omega\Http\Upload\UploadFile;
use Omega\Http\Upload\UploadMultiFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function dirname;
use function file_exists;
use function filesize;
use function trim;
use function unlink;

/**
 * Test suite for the UploadFile and UploadMultiFile classes.
 *
 * This class tests various file upload scenarios including:
 * - Uploading valid single files.
 * - Handling invalid file types, sizes, mime types, and folder locations.
 * - Handling no file upload error state.
 * - Handling multi-file uploads both single and multiple files.
 *
 * It also verifies that exceptions such as FolderNotExistsException are properly thrown.
 * The tests are run in "test mode" to avoid actual move_uploaded_file calls.
 *
 * @category   Omega\Tests
 * @package    Htto
 * @subpackage Middleware
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(UploadFile::class)]
#[CoversClass(UploadMultiFile::class)]
#[CoversClass(FolderNotExistsException::class)]
class UploadFileTest extends TestCase
{
    /** @var string Directory path where files are uploaded */
    private string $uploadDir;

    /** @var string|array|false Uploaded file(s) info in $_FILES format */
    private string|array|false $files;

    /** @var UploadFile|null Instance of UploadFile for testing */
    private ?UploadFile $upload;

    /**
     * Set up the test environment before each test.
     *
     * This method is called before each test method is run.
     * Override it to initialize objects, mock dependencies, or reset state.
     *
     * @return void
     */
    protected function setUp(): void
    {
        if (!ini_get('file_uploads')) {
            $this->markTestSkipped('file_uploads is disabled in php.ini');
        }

        $this->uploadDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR . 'http' . DIRECTORY_SEPARATOR .  'upload1' . DIRECTORY_SEPARATOR;

        $this->files = [
            'file_1' => [
                'name'      => 'test123.txt',
                'type'      => 'file',
                'tmp_name'  => $this->uploadDir . 'test123.tmp',
                'error'     => 0,
                'size'      => filesize($this->uploadDir . 'test123.tmp'),
            ],
            'file_2' => [
                'name'      => ['test123.txt', 'test234.txt'],
                'type'      => ['file', 'file'],
                'tmp_name'  => [
                    $this->uploadDir . 'test123.tmp',
                    $this->uploadDir . 'test234.tmp',
                ],
                'error'     => [0, 0],
                'size'      => [
                    filesize($this->uploadDir . 'test123.tmp'),
                    filesize($this->uploadDir . 'test234.tmp'),
                ],
            ],
        ];

        $this->files['file_1']['type'] = filetype($this->files['file_1']['tmp_name']);

        $this->upload = new UploadFile($this->files['file_1']);
        $this->upload
            ->markTest(true)
            ->setFileName('success')
            ->setFileTypes(['txt', 'md'])
            ->setFolderLocation($this->uploadDir)
            ->setMaxFileSize(91)
            ->setMimeTypes(['file']);
    }

    /**
     * Clean up the test environment after each test.
     *
     * This method flushes and resets the application container
     * to ensure a clean state between tests.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $file = $this->uploadDir . 'success.txt';
        if (file_exists($file)) {
            unlink($file);
        }

        $this->upload = null;
    }

    /**
     * Test it can upload file valid.
     *
     * @return void
     */
    public function testItCanUploadFileValid(): void
    {
        $this->upload->upload();

        $this->assertTrue($this->upload->success());
        $this->assertEquals('success', $this->upload->getError());
        $this->assertEquals(
            'This is a story about something that happened long ago when your grandfather was a child.',
            trim($this->upload->get())
        );
    }

    /**
     * Test it can upload file invalid file type.
     *
     * @return void
     */
    public function testItCanUploadFileInvalidFileType(): void
    {
        $this->upload->setFileTypes(['md'])->upload();

        $this->assertFalse($this->upload->success());
    }

    /**
     * Test it can upload file invalid file folder.
     *
     * @return void
     */
    public function testItCantUploadFileInvalidFileFolder(): void
    {
        $this->expectException(FolderNotExistsException::class);

        $this->upload->setFolderLocation('/unknown');
    }

    /**
     * Test it con upload file invalid file size.
     *
     * @return void
     */
    public function testItCanUploadFileInvalidFileSize(): void
    {
        $this->upload->setMaxFileSize(89)->upload();

        $this->assertFalse($this->upload->success());
    }

    /**
     * Test it can upload file invalid mine.
     *
     * @return void
     */
    public function testItCanUploadFileInvalidMime(): void
    {
        $this->upload->setMimeTypes(['image/jpeg'])->upload();

        $this->assertFalse($this->upload->success());
    }

    /**
     * Test it can upload file invalid no file upload.
     *
     * @return void
     */
    public function testItCanUploadFileInvalidNoFileUpload(): void
    {
        $this->files['file_1']['error'] = 4;

        $upload = new UploadFile($this->files['file_1']);
        $upload
            ->markTest(true)
            ->setFileName('success')
            ->setFileTypes(['txt', 'md'])
            ->setFolderLocation($this->uploadDir)
            ->setMaxFileSize(91)
            ->setMimeTypes(['file']);

        $this->assertFalse($upload->success());

        // reset
        $this->files['file_1']['error'] = 0;
    }

    /**
     * Test it con multi upload file but single file.
     *
     * @return void
     */
    public function testItCanMultiUploadFileButSingleFile(): void
    {
        $upload = new UploadMultiFile($this->files['file_2']);
        $upload
            ->markTest(true)
            ->setFileName('multi_file_')
            ->setFileTypes(['txt', 'md'])
            ->setFolderLocation($this->uploadDir)
            ->setMaxFileSize(91)
            ->setMimeTypes(['file'])
            ->uploads();

        $this->assertTrue($upload->success());
        $this->assertFileExists($this->uploadDir . 'multi_file_0.txt');
        $this->assertFileExists($this->uploadDir . 'multi_file_1.txt');

        unlink($this->uploadDir . 'multi_file_0.txt');
        unlink($this->uploadDir . 'multi_file_1.txt');
    }

    /**
     * Test it can multi upload file.
     *
     * @return void
     */
    public function testItCanMultiUploadFile(): void
    {
        $upload = new UploadMultiFile($this->files['file_2']);
        $upload
            ->markTest(true)
            ->setFileName('multi_file_')
            ->setFileTypes(['txt', 'md'])
            ->setFolderLocation($this->uploadDir)
            ->setMaxFileSize(91)
            ->setMimeTypes(['file'])
            ->uploads();

        $this->assertTrue($upload->success());
        $this->assertFileExists($this->uploadDir . 'multi_file_0.txt');
        $this->assertFileExists($this->uploadDir . 'multi_file_1.txt');

        unlink($this->uploadDir . 'multi_file_0.txt');
        unlink($this->uploadDir . 'multi_file_1.txt');
    }
}
