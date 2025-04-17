<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\ExternalMediaPrefetcher\Model;

use Hryvinskyi\ExternalMediaPrefetcher\Api\ConfigInterface;
use Hryvinskyi\ExternalMediaPrefetcher\Api\PathResolverInterface;

class PathResolver implements PathResolverInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function getCleanPath(string $filename): string
    {
        if (strrpos($filename, 'cache')) {
            $filename = preg_replace('/\/cache\/.{32}/', '', $filename);
        }

        if (strpos($filename, 'media/') === 0) {
            $filename = substr($filename, 6); // Length of "media/"
        }

        return $filename;
    }

    /**
     * @inheritDoc
     */
    public function buildExternalUrl(string $filename): string
    {
        return rtrim($this->config->getExternalMediaUrl(), '/') . '/' . ltrim($filename, '/');
    }
}