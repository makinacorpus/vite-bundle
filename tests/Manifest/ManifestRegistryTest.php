<?php

declare(strict_types=1);

namespace MakinaCorpus\ViteBundle\Tests\Manifest;

use MakinaCorpus\ViteBundle\Manifest\Manifest;
use MakinaCorpus\ViteBundle\Manifest\ManifestRegistry;
use PHPUnit\Framework\TestCase;

final class ManifestRegistryTest extends TestCase
{
    public function testRaiseErrorWhenNonExistingApp(): void
    {
        $registry = new ManifestRegistry([]);

        self::expectExceptionMessageMatches('/is not registered/');
        $registry->get('foo');
    }

    public function testWhenItWorks(): void
    {
        $manifest = new Manifest(__DIR__ . '/public/foo/manifest.json', __DIR__ . '/public');
        $registry = new ManifestRegistry(['my_app' => $manifest]);

        self::assertSame("/foo/assets/primeicons.8f9d2aaf.svg", $registry->getEntry('my_app', "node_modules/primeicons/fonts/primeicons.svg"));
        self::assertSame("/foo/assets/main.d2b010b8.js", $registry->getEntry('my_app', "src/main.ts"));
        self::assertSame("/foo/assets/style.276b072b.css", $registry->getEntry('my_app', "style.css"));
        self::assertNull($registry->getEntry('my_app', "non existing entry"));
        self::assertNull($registry->getEntry('nope', "src/main.ts"));
    }
}
