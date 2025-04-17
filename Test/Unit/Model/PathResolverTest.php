<?php
/**
 * Copyright (c) 2025. Volodymyr Hryvinskyi. All rights reserved.
 * Author: Volodymyr Hryvinskyi <volodymyr@hryvinskyi.com>
 * GitHub: https://github.com/hryvinskyi
 */

declare(strict_types=1);

namespace Hryvinskyi\ExternalMediaPrefetcher\Test\Unit\Model;

use Hryvinskyi\ExternalMediaPrefetcher\Api\ConfigInterface;
use Hryvinskyi\ExternalMediaPrefetcher\Model\PathResolver;
use PHPUnit\Framework\TestCase;
class PathResolverTest extends TestCase
{
    /**
     * @var ConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    /**
     * @var PathResolver
     */
    private $pathResolver;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->pathResolver = new PathResolver($this->configMock);
    }

    /**
     * @dataProvider pathCleaningProvider
     */
    public function testGetCleanPath(string $input, string $expected): void
    {
        $result = $this->pathResolver->getCleanPath($input);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider externalUrlProvider
     */
    public function testBuildExternalUrl(string $input, string $expected): void
    {
        $this->configMock->expects($this->once())
            ->method('getExternalMediaUrl')
            ->willReturn('https://example.com/media/');

        $result = $this->pathResolver->buildExternalUrl($this->pathResolver->getCleanPath($input));
        $this->assertEquals($expected, $result);
    }

    public function pathCleaningProvider(): array
    {
        return [
            'with cache path' => [
                '/cache/12345678901234567890123456789012/catalog/product/image.jpg',
                '/catalog/product/image.jpg'
            ],
            'without cache path' => [
                '/catalog/product/image.jpg',
                '/catalog/product/image.jpg'
            ]
        ];
    }

    public function externalUrlProvider(): array
    {
        return [
            'standard product image' => [
                'catalog/product/image.jpg',
                'https://example.com/media/catalog/product/image.jpg'
            ],
            'with media prefix' => [
                'media/catalog/product/image.jpg',
                'https://example.com/media/catalog/product/image.jpg'
            ],
            'store-specific placeholder' => [
                'catalog/product/placeholder/stores/3/Auraglow-logo-small_2_1.jpg',
                'https://example.com/media/catalog/product/placeholder/stores/3/Auraglow-logo-small_2_1.jpg'
            ],
            'store-specific placeholder with media prefix' => [
                'media/catalog/product/placeholder/stores/3/Auraglow-logo-small_2_1.jpg',
                'https://example.com/media/catalog/product/placeholder/stores/3/Auraglow-logo-small_2_1.jpg'
            ]
        ];
    }
}