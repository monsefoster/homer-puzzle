<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

$projectDir = dirname(__DIR__);
$chromeBinary = '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

if (is_file($chromeBinary)) {
    $_SERVER['PANTHER_CHROME_BINARY'] = $_ENV['PANTHER_CHROME_BINARY'] = $chromeBinary;
}

$driverDir = $projectDir.'/drivers';
if (is_dir($driverDir)) {
    $path = $driverDir.PATH_SEPARATOR.($_SERVER['PATH'] ?? getenv('PATH') ?: '');
    putenv(sprintf('PATH=%s', $path));
    $_SERVER['PATH'] = $_ENV['PATH'] = $path;
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
