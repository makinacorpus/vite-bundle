<?php

declare(strict_types=1);

namespace MakinaCorpus\ViteBundle\Tests\Manifest;

use MakinaCorpus\ViteBundle\Manifest\Manifest;
use PHPUnit\Framework\TestCase;

final class ManifestTest extends TestCase
{
    public function testRaiseErrorWhenNonExistingFile(): void
    {
        $manifest = new Manifest(__DIR__ . '/public/non_existing_file.json', __DIR__ . '/public');

        self::expectExceptionMessageMatches('/Could not realpath/');
        $manifest->getEntryPath('foo');
    }

    public function testRaiseErrorWhenFileNotInPublic(): void
    {
        $manifest = new Manifest(__DIR__ . '/non-public/manifest.json', __DIR__ . '/public');

        self::expectExceptionMessageMatches('/File is not in/');
        $manifest->getEntryPath('foo');
    }

    public function testRaiseErrorWhenInvalidJson(): void
    {
        $manifest = new Manifest(__DIR__ . '/public/manifest-invalid.json', __DIR__ . '/public');

        self::expectExceptionMessageMatches('/File is not a valid Vite manifest.json file/');
        $manifest->getEntryPath('foo');
    }

    public function testRaiseErrorWhenNonJson(): void
    {
        $manifest = new Manifest(__DIR__ . '/public/manifest-nonjson.json', __DIR__ . '/public');

        self::expectExceptionMessageMatches('/File contains invalid JSON/');
        $manifest->getEntryPath('foo');
    }

    public function testWhenItWorks(): void
    {
        $manifest = new Manifest(__DIR__ . '/public/foo/manifest.json', __DIR__ . '/public');

        $manifest->getEntryPath('foo');

        self::assertSame("/foo/assets/primeicons.8f9d2aaf.svg", $manifest->getEntryPath("node_modules/primeicons/fonts/primeicons.svg"));
        self::assertSame("/foo/assets/main.d2b010b8.js", $manifest->getEntryPath("src/main.ts"));
        self::assertSame("/foo/assets/style.276b072b.css", $manifest->getEntryPath("style.css"));
        self::assertNull($manifest->getEntryPath("non existing entry"));
    }
}
