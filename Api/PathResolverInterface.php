<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\ExternalMediaPrefetcher\Api;

interface PathResolverInterface
{
    /**
     * Remove cache path from filename if present
     *
     * @param string $filename
     * @return string
     */
    public function getCleanPath(string $filename): string;

    /**
     * Build external URL for media file
     *
     * @param string $filename
     * @return string
     */
    public function buildExternalUrl(string $filename): string;
}