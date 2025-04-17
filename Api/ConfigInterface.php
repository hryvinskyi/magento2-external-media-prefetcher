<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\ExternalMediaPrefetcher\Api;

interface ConfigInterface
{
    /**
     * Check if the external media prefetcher is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Get the external media URL.
     *
     * @return string|null
     */
    public function getExternalMediaUrl(): string;
}