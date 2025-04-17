<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\ExternalMediaPrefetcher\Model;

use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use Hryvinskyi\ExternalMediaPrefetcher\Api\FileDownloaderInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Io\File;
use Psr\Log\LoggerInterface;

class FileDownloader implements FileDownloaderInterface
{
    /**
     * @var File
     */
    private $file;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var ClientFactory
     */
    private $clientFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        File $file,
        Filesystem $filesystem,
        ClientFactory $clientFactory,
        LoggerInterface $logger
    ) {
        $this->file = $file;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->clientFactory = $clientFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function download(string $sourceUrl, string $destinationPath): bool
    {
        try {
            $client = $this->clientFactory->create();
            $response = $client->request('GET', $sourceUrl, [
                'timeout' => 30,
                'connect_timeout' => 10,
                'http_errors' => false
            ]);

            $status = $response->getStatusCode();
            if ($status >= 200 && $status < 300) {
                $content = $response->getBody()->getContents();
                if ($content) {
                    $this->file->write($destinationPath, $content);
                    return true;
                }
            } else {
                $this->logger->warning(
                    sprintf('Failed to download file from %s. Status: %d', $sourceUrl, $status)
                );
            }

            return false;
        } catch (GuzzleException $e) {
            $this->logger->error(
                sprintf('Error downloading file from %s: %s', $sourceUrl, $e->getMessage())
            );
            return false;
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('Unexpected error downloading file from %s: %s', $sourceUrl, $e->getMessage())
            );
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function prepareDestinationDirectory(string $filename): string
    {
        $destinationDir = $this->mediaDirectory->getAbsolutePath()
            . ltrim(dirname($filename), DirectoryList::MEDIA . DIRECTORY_SEPARATOR);

        $this->file->checkAndCreateFolder($destinationDir);

        return $destinationDir;
    }
}