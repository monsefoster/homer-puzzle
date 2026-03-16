<?php

declare(strict_types=1);

namespace App\Tests\Browser;

use Symfony\Component\Panther\PantherTestCase;

abstract class BrowserTestCase extends PantherTestCase
{
    protected static function ensureBrowserRuntime(): void
    {
        $projectDir = dirname(__DIR__, 2);
        $chromeDriver = $projectDir.'/drivers/chromedriver';
        $chromeBinary = $_SERVER['PANTHER_CHROME_BINARY'] ?? null;

        if (!is_file($chromeDriver) || !is_executable($chromeDriver)) {
            self::markTestSkipped('Panther browser tests require drivers/chromedriver. Run vendor/bin/bdi detect drivers.');
        }

        if (!is_string($chromeBinary) || !is_file($chromeBinary)) {
            self::markTestSkipped('Panther browser tests require Google Chrome to be installed.');
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        self::stopWebServer();
    }
}
