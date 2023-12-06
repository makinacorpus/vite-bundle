<?php

declare (strict_types=1);

namespace MakinaCorpus\ViteBundle\Twig\Extension;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use MakinaCorpus\ViteBundle\Manifest\ManifestRegistry;

class ViteTwigExtension extends AbstractExtension
{
    private bool $dev = true;

    public function __construct(
        private ManifestRegistry $registry,
        private ?string $kernelEnv = null,
    ) {
        $this->dev = 'dev' === $kernelEnv;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('vite_app', [$this, 'getViteApp'], ['is_safe' => ['html']]),
            new TwigFunction('vite_head', [$this, 'getViteHead'], ['is_safe' => ['html']]),
            // @deprecated
            new TwigFunction('vite_asset', [$this, 'getAsset'], ['is_safe' => ['html']]),
        ];
    }

    public function getViteApp(
        string $app,
        ?string $entry = null,
        bool $crossOrigin = false,
        string $tagId = 'app',
        string $tagName = 'div',
    ): string {
        $output = '';
        $attributes = $crossOrigin ? ' crossorigin="anonymous"' : '';

        $manifest = $this->registry->get($app);
        $entry = $manifest->getEntry($entry);

        $escapedTag = \htmlentities($tagId);

        if ($this->dev && ($devUrl = $manifest->getDevServerUrl())) {
            $url = \rtrim($devUrl, '/') . '/' . $entry->src;
        } else {
            $url = $entry->getFilename();
        }

        $output .= \sprintf('<%s id="%s"></%s>', $escapedTag, \htmlentities($tagId), $escapedTag);
        $output .= '<script' . $attributes . ' type="module" src="' . $url . '"></script>';

        return $output;
    }

    public function getViteHead(
        string $app,
        ?string $entry = null,
        bool $crossOrigin = false,
    ): string {
        $output = '';
        $attributes = $crossOrigin ? ' crossorigin="anonymous"' : '';

        $manifest = $this->registry->get($app);
        $entry = $manifest->getEntry($entry);

        if (!$entry) {
            return '<!-- Missing entry file for app ' . $app . ': ' . $entry . ' -->';
        }

        if ($this->dev && ($devUrl = $manifest->getDevServerUrl())) {
            $url = \rtrim($devUrl, '/') . '/@vite/client';
        } else {
            $url = $entry->getFilename();
            foreach ($entry->getCssFilenames() as $cssUrl) {
                $output .= '<link' . $attributes . ' media="all" rel="stylesheet" href="' . $cssUrl . '" />';
            }
        }

        return $output . '<script' . $attributes . ' type="module" src="' . $url . '"></script>';
    }

    /**
     * @deprecated
     *   Kept for backward compatibility.
     */
    public function getAsset(
        string $app,
        ?string $entry = null
    ): string {
        return $this->registry->get($app)->getEntryPath($entry) ?? '/missing-file.txt';
    }
}
