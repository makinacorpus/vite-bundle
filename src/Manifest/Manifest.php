<?php

declare (strict_types=1);

namespace MakinaCorpus\ViteBundle\Manifest;

class Manifest
{
    private string $filename;
    private ?string $publicDirectory = null;
    private ?array $entries = [];

    public function __construct(string $filename, ?string $publicDirectory, ?array $entries = null)
    {
        $this->filename = $filename;
        $this->entries = $entries;
        $this->publicDirectory = $publicDirectory;
    }

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
            $entries[$fileName] = $relativeFileDirectory . '/' . $data['file'];
        }

        return $entries;
    }

    private function checkState(): void
    {
        if (null === $this->entries) {
            $this->entries = self::parseManifestFile($this->filename, $this->publicDirectory);
        }
    }

    public function getEntryPath(string $entry): ?string
    {
        $this->checkState();

        return $this->entries[$entry] ?? null;
    }
}
