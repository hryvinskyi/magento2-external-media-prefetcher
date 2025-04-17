<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\ExternalMediaPrefetcher\Plugin;

use Hryvinskyi\ExternalMediaPrefetcher\Api\ConfigInterface;
use Hryvinskyi\ExternalMediaPrefetcher\Api\FileDownloaderInterface;
use Hryvinskyi\ExternalMediaPrefetcher\Api\PathResolverInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\MediaStorage\Model\File\Storage\Synchronization;

class SynchronizationPlugin
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var PathResolverInterface
     */
    private $pathResolver;

    /**
     * @var FileDownloaderInterface
     */
    private $fileDownloader;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    public function __construct(
        ConfigInterface $config,
        PathResolverInterface $pathResolver,
        FileDownloaderInterface $fileDownloader,
        Filesystem $filesystem
    ) {
        $this->config = $config;
        $this->pathResolver = $pathResolver;
        $this->fileDownloader = $fileDownloader;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
    }

    /**
     * Attempts to download a file from external source if it doesn't exist locally
     *
     * @param Synchronization $subject
     * @param mixed $result
     * @param string|null $relativeFileName
     * @return void
     */
    public function afterSynchronize(
        Synchronization $subject,
        $result,
        ?string $relativeFileName
    ): void {
        if (!$this->shouldDownloadFile($relativeFileName)) {
            return;
        }

        $cleanPath = $this->pathResolver->getCleanPath($relativeFileName);
        $this->downloadFile($cleanPath);
    }

    /**
     * Determines if a file should be downloaded
     */
    private function shouldDownloadFile(?string $relativeFileName): bool
    {
        return $relativeFileName !== null
            && $this->config->isEnabled()
            && !$this->mediaDirectory->isExist($relativeFileName);
    }

    /**
     * Downloads a file from the external media URL
     */
    private function downloadFile(string $originalFileName): void
    {
        $destinationDir = $this->fileDownloader->prepareDestinationDirectory($originalFileName);
        $newFilename = $destinationDir . DIRECTORY_SEPARATOR . basename($originalFileName);
        $imageUrl = $this->pathResolver->buildExternalUrl($originalFileName);
        $this->fileDownloader->download($imageUrl, $newFilename);
    }
}