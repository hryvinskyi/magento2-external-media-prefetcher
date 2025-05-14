<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\ExternalMediaPrefetcher\Test\Unit\Model;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\Response;
use Hryvinskyi\ExternalMediaPrefetcher\Model\FileDownloader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Io\File;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

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
     * @var ClientFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $clientFactoryMock;

    /**
     * @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $loggerMock;

    /**
     * @var Client|\PHPUnit\Framework\MockObject\MockObject
     */
    private $clientMock;

    /**
     * @var FileDownloader
     */
    private $fileDownloader;

    protected function setUp(): void
    {
        $this->fileMock = $this->createMock(File::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->mediaDirectoryMock = $this->createMock(WriteInterface::class);
        $this->clientFactoryMock = $this->createMock(ClientFactory::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->clientMock = $this->createMock(Client::class);

        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->mediaDirectoryMock);

        $this->clientFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->clientMock);

        $this->fileDownloader = new FileDownloader(
            $this->fileMock,
            $this->filesystemMock,
            $this->clientFactoryMock,
            $this->loggerMock
        );
    }

    public function testDownload(): void
    {
        $sourceUrl = 'https://example.com/media/image.jpg';
        $destinationPath = '/path/to/dest/media/image.jpg';
        $responseContent = 'image content';

        // Mock response and stream
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->expects($this->once())
            ->method('getContents')
            ->willReturn($responseContent);

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);
        $responseMock->expects($this->once())
            ->method('getBody')
            ->willReturn($streamMock);

        // Mock client request to return response
        $this->clientMock->expects($this->once())
            ->method('request')
            ->with('GET', $sourceUrl, [
                'timeout' => 30,
                'connect_timeout' => 10,
                'http_errors' => false
            ])
            ->willReturn($responseMock);

        // Mock file write to return true
        $this->fileMock->expects($this->once())
            ->method('write')
            ->with($destinationPath, $responseContent)
            ->willReturn(true);

        $this->assertTrue(
            $this->fileDownloader->download($sourceUrl, $destinationPath)
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