<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\ExternalMediaPrefetcher\Api;

interface FileDownloaderInterface
{
    /**
     * Downloads a file from source URL and saves it to destination path
     *
     * @param string $sourceUrl
     * @param string $destinationPath
     * @return bool
     */
    public function download(string $sourceUrl, string $destinationPath): bool;

    /**
     * Prepare destination directory for file
     *
     * @param string $filename
     * @return string Directory path
     */
    public function prepareDestinationDirectory(string $filename): string;
}