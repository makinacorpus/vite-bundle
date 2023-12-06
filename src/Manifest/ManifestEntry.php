<?php

declare (strict_types=1);

namespace MakinaCorpus\ViteBundle\Manifest;

class ManifestEntry
{
    public function __construct(
        public string $directory,
        public string $src,
        public string $file,
        public bool $isEntry,
        public array $css,
    ) {}

    /**
     * Get filename relative to webroot.
     */
    public function getFilename(): string
    {
        return $this->directory . '/' . \ltrim($this->file, '/');
    }

    /**
     * Get CSS filenames relative to webroot.
     */
    public function getCssFilenames(): array
    {
        $ret = [];
        foreach ($this->css as $file) {
            $ret[] = $this->directory . '/' . \ltrim($file, '/');
        }
        return $ret;
    }
}
