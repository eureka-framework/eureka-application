<?php

/*
 * Copyright (c) Romain Cottard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Application\Service\Twig;

use Symfony\Component\Routing\Router;

class TwigHelper
{
    /** @var string[] $assetsManifest */
    private array $assetsManifest;

    /**
     * @throws \JsonException
     */
    public function __construct(private readonly Router $router, string $webAssetsPath)
    {
        $this->initializeAssetsManifest($webAssetsPath);
    }

    /**
     * @throws \JsonException
     */
    private function initializeAssetsManifest(string $webAssetsPath): void
    {
        $manifestFile = $webAssetsPath . '/manifest.json';

        if (!is_readable($manifestFile)) {
            throw new \RuntimeException('manifest.json file is not readable.');
        }

        /** @var string[] $json */
        $json = json_decode(
            (string) file_get_contents($manifestFile),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assetsManifest = $json;
    }

    /**
     * @return callable[]
     */
    public function getCallbackFunctions(): array
    {
        return [
            'path'  => $this->path(...),
            'image' => $this->image(...),
            'asset' => $this->asset(...),
        ];
    }

    /**
     * @param  string $routeName
     * @param  array<string, string|int|float|bool|null> $params
     * @return string
     */
    public function path(string $routeName, array $params = []): string
    {
        return $this->router->generate($routeName, $params);
    }

    public function image(string $filename, string $baseUrl = '/assets/images'): string
    {
        return $this->getRealAssetPath($filename, $baseUrl);
    }

    public function asset(string $filename, string $baseUrl = '/assets'): string
    {
        return $this->getRealAssetPath($filename, $baseUrl);
    }

    private function getRealAssetPath(string $filename, string $baseUrl): string
    {
        $filePath = trim($baseUrl, ' /') . '/' . ltrim($filename, '/');
        if (!isset($this->assetsManifest[$filePath])) {
            return '';
        }

        return $this->assetsManifest[$filePath];
    }
}
