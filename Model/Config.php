<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\ExternalMediaPrefetcher\Model;

use Hryvinskyi\ExternalMediaPrefetcher\Api\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Config implements ConfigInterface
{
    public const XML_PATH_GENERAL_ENABLED = 'external_media_prefetcher/general/enabled';
    public const XML_PATH_GENERAL_EXTERNAL_MEDIA_URL = 'external_media_prefetcher/general/external_media_url';

    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_GENERAL_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function getExternalMediaUrl(): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_GENERAL_EXTERNAL_MEDIA_URL);
    }
}