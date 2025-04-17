<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\ExternalMediaPrefetcher\Test\Unit\Plugin;

use Hryvinskyi\ExternalMediaPrefetcher\Api\ConfigInterface;
use Hryvinskyi\ExternalMediaPrefetcher\Api\FileDownloaderInterface;
use Hryvinskyi\ExternalMediaPrefetcher\Api\PathResolverInterface;
use Hryvinskyi\ExternalMediaPrefetcher\Plugin\SynchronizationPlugin;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\MediaStorage\Model\File\Storage\Synchronization;
use PHPUnit\Framework\TestCase;

class SynchronizationPluginTest extends TestCase
{
    /**
     * @var ConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    /**
     * @var PathResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $pathResolverMock;

    /**
     * @var FileDownloaderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $fileDownloaderMock;

    /**
     * @var Filesystem|\PHPUnit\Framework\MockObject\MockObject
     */
    private $filesystemMock;

    /**
     * @var WriteInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $directoryMock;

    /**
     * @var SynchronizationPlugin
     */
    private $plugin;

    /**
     * @var Synchronization|\PHPUnit\Framework\MockObject\MockObject
     */
    private $subjectMock;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->pathResolverMock = $this->createMock(PathResolverInterface::class);
        $this->fileDownloaderMock = $this->createMock(FileDownloaderInterface::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->directoryMock = $this->createMock(WriteInterface::class);
        $this->subjectMock = $this->createMock(Synchronization::class);

        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($this->directoryMock);

        $this->plugin = new SynchronizationPlugin(
            $this->configMock,
            $this->pathResolverMock,
            $this->fileDownloaderMock,
            $this->filesystemMock
        );
    }

    public function testAfterSynchronizeSkipsWhenDisabled(): void
    {
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(false);

        $this->pathResolverMock->expects($this->never())
            ->method('getCleanPath');

        $this->plugin->afterSynchronize($this->subjectMock, null, 'catalog/product/image.jpg');
    }

    public function testAfterSynchronizeDownloadsFile(): void
    {
        $path = 'catalog/product/image.jpg';
        $cleanPath = 'catalog/product/image.jpg';
        $externalUrl = 'https://example.com/media/catalog/product/image.jpg';
        $destDir = '/var/www/html/pub/media/catalog/product';

        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);

        $this->directoryMock->expects($this->once())
            ->method('isExist')
            ->with($path)
            ->willReturn(false);

        $this->pathResolverMock->expects($this->once())
            ->method('getCleanPath')
            ->with($path)
            ->willReturn($cleanPath);

        $this->pathResolverMock->expects($this->once())
            ->method('buildExternalUrl')
            ->with($cleanPath)
            ->willReturn($externalUrl);

        $this->fileDownloaderMock->expects($this->once())
            ->method('prepareDestinationDirectory')
            ->with($cleanPath)
            ->willReturn($destDir);

        $this->fileDownloaderMock->expects($this->once())
            ->method('download')
            ->with($externalUrl, $destDir . DIRECTORY_SEPARATOR . 'image.jpg');

        $this->plugin->afterSynchronize($this->subjectMock, null, $path);
    }
}