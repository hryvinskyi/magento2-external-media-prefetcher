<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\ExternalMediaPrefetcher\Test\Unit\Model;

use Hryvinskyi\ExternalMediaPrefetcher\Model\FileDownloader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Io\File;
use PHPUnit\Framework\TestCase;

class FileDownloaderTest extends TestCase
{
    /**
     * @var File|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fileMock;

    /**
     * @var Filesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filesystemMock;

    /**
     * @var WriteInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $mediaDirectoryMock;

    /**
     * @var FileDownloader
     */
    private $fileDownloader;

    protected function setUp(): void
    {
        $this->fileMock = $this->createMock(File::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->mediaDirectoryMock = $this->createMock(WriteInterface::class);

        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectoryMock);

        $this->fileDownloader = new FileDownloader($this->fileMock, $this->filesystemMock);
    }

    public function testDownload(): void
    {
        $this->fileMock->expects($this->once())
            ->method('read')
            ->with('https://example.com/image.jpg', '/path/to/dest/image.jpg')
            ->willReturn(true);

        $this->assertTrue(
            $this->fileDownloader->download('https://example.com/image.jpg', '/path/to/dest/image.jpg')
        );
    }

    public function testPrepareDestinationDirectory(): void
    {
        $this->mediaDirectoryMock->expects($this->once())
            ->method('getAbsolutePath')
            ->willReturn('/var/www/html/pub/media/');

        $this->fileMock->expects($this->once())
            ->method('checkAndCreateFolder')
            ->with('/var/www/html/pub/media/catalog/product')
            ->willReturn(true);

        $result = $this->fileDownloader->prepareDestinationDirectory('catalog/product/image.jpg');
        $this->assertEquals('/var/www/html/pub/media/catalog/product', $result);
    }
}