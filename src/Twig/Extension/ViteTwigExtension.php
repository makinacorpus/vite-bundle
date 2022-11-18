<?php

declare (strict_types=1);

namespace MakinaCorpus\ViteBundle\Twig\Extension;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use MakinaCorpus\ViteBundle\Manifest\ManifestRegistry;

class ViteTwigExtension extends AbstractExtension
{
    private ManifestRegistry $registry;

    public function __construct(ManifestRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('vite_asset', [$this, 'getAsset'], ['is_safe' => ['html']]),
        ];
    }

    public function getAsset(string $app, string $entry): string
    {
        return $this->registry->getEntry($app, $entry) ?? '/missing-file.txt';
    }
}
