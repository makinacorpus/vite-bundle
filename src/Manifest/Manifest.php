<?php

declare (strict_types=1);

namespace MakinaCorpus\ViteBundle\Manifest;

class Manifest
{
    public function __construct(
        /**
         * manifest.json filename.
         */
        private string $filename,
        /**
         * Symfony public assets directory.
         */
        private ?string $publicDirectory,
        private ?array $entries = null,
        /**
         * Dev server URL if specified, set to null to disable. Setting null
         * here will completly deactivate the dev mode.
         */
        private ?string $devServerUrl = 'http://localhost:5173',
    ) {}

    public static function validateManifestFile(string $manifestPath, string $publicPath): array
    {
        $publicPath = \rtrim($publicPath, '/');
        $rpPublicPath = \realpath($publicPath);

        if ('/' !== \substr($manifestPath, 0, 1)) {
            $manifestPath = $publicPath . '/' . $manifestPath;
        }

        $manifestPath = \realpath($manifestPath);
        if (!$manifestPath) {
            throw new \InvalidArgumentException(\sprintf("Could not realpath() file: %s", $manifestPath));
        }

        // Find root relative to public path.
        if (\substr($manifestPath, 0, \strlen($rpPublicPath)) !== $rpPublicPath) {
            throw new \InvalidArgumentException(\sprintf("File is not in \"%%kernel.project_dir%%/public/\" directory: %s", $manifestPath));
        }
        $relativeFileDirectory = '/' . \trim(\dirname(\substr($manifestPath, \strlen($rpPublicPath))), '/');

        // Older Vite generated build didn't contain the ".vite" folder, but
        // more recently now they are, and relative path for asset files
        // changes. We simply remove the extra ".vite/" path segment and it
        // should work gracefully.
        if (\str_contains($relativeFileDirectory, '/.vite')) {
            $relativeFileDirectory = \str_replace('/.vite', '', $relativeFileDirectory);
        } else if (\str_contains($relativeFileDirectory, '.vite/')) {
            $relativeFileDirectory = \str_replace('.vite/', '', $relativeFileDirectory);
        }

        if (!\file_exists($manifestPath)) {
            throw new \InvalidArgumentException(\sprintf("File does not exist: %s", $manifestPath));
        }

        return [$manifestPath, $relativeFileDirectory];
    }

    /**
     * Parse manifest file and return found entries list.
     */
    public static function parseManifestFile(string $manifestPath, string $publicPath): array
    {
        $entries = [];

        list ($manifestPath, $relativeFileDirectory) = self::validateManifestFile($manifestPath, $publicPath);

        $manifestContents = \file_get_contents($manifestPath);
        if (!$manifestContents) {
            throw new \InvalidArgumentException(\sprintf("Could not read file or file empty: %s", $manifestPath));
        }

        $decoded = \json_decode($manifestContents, true);
        if (!$decoded) {
            throw new \InvalidArgumentException(\sprintf("File contains invalid JSON: %s", $manifestPath));
        }

        foreach ($decoded as $fileName => $data) {
            if (empty($data['file'])) {
                throw new \InvalidArgumentException(\sprintf("File is not a valid Vite manifest.json file, entry '%s' is missing 'file' property in: %s", $fileName, $manifestPath));
            }

            $entries[$fileName] = new ManifestEntry(
                directory: $relativeFileDirectory,
                src: $data['src'] ?? $fileName,
                file: $data['file'],
                isEntry: (bool) ($data['isEntry'] ?? false),
                css: (array) ($data['css'] ?? [])
            );
        }

        return $entries;
    }

    /**
     * Get development server URL.
     */
    public function getDevServerUrl(): ?string
    {
        return $this->devServerUrl;
    }

    /**
     * Get default entry, either the first one found whose isEntry boolean
     * value is true, or the first one if none matched.
     */
    public function getDefaultEntry(): ?ManifestEntry
    {
        $this->checkState();

        $foundOrFirst = null;
        foreach ($this->entries as $entry) {
            \assert($entry instanceof ManifestEntry);
            if ($entry->isEntry || !$foundOrFirst) {
                $foundOrFirst = $entry;
            }
        }

        return $foundOrFirst;
    }

    /**
     * Get entry by name or default.
     */
    public function getEntry(?string $entry): ?ManifestEntry
    {
        $this->checkState();

        if (!$entry) {
            return $this->getDefaultEntry();
        }
        return $this->getEntry($entry);
    }

    /**
     * Get a single entry path.
     */
    public function getEntryPath(?string $entry): ?string
    {
        if (!$entry) {
            return $this->getDefaultEntry()?->computedFilename;
        }
        return $this->getEntry($entry)?->computedFilename;
    }

    /**
     * Initialize content by parsing it.
     */
    private function checkState(): void
    {
        if (null === $this->entries) {
            $this->entries = self::parseManifestFile($this->filename, $this->publicDirectory);
        }
    }
}
