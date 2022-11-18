<?php

declare (strict_types=1);

namespace MakinaCorpus\ViteBundle\Manifest;

class ManifestRegistry
{
    /** @var array<string,Manifest> */
    private array $apps;

    public function __construct(array $apps)
    {
        $this->apps = $apps;
    }

    public function getEntry(string $app, string $entry): ?string
    {
        try {
            return $this->get($app)->getEntryPath($entry);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    public function get(string $app): Manifest
    {
        if ($ret = ($this->apps[$app] ?? null)) {
            return $ret;
        }
        throw new \InvalidArgumentException(\sprintf("App %s is not registered in configuration.", $app));
    }
}
