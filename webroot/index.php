<?php declare(strict_types = 1);

use DevNet\System\Runtime\launcher;
use Application\Program;

if (PHP_OS_FAMILY == 'Windows')
{
    $path = getenv('path');
    $paths = explode(';', $path);
}
else
{
    $path = getenv('PATH');
    $paths = explode(':', $path);
}

foreach ($paths as $path)
{
    if (file_exists($path.'/../autoload.php'))
    {
        require $path.'/../autoload.php';
        break;
    }
}

if (file_exists(__DIR__ . "/../vendor/autoload.php"))
{
    require __DIR__ . "/../vendor/autoload.php";
}

$launcher = launcher::getLauncher();
$launcher->workspace(dirname(__DIR__));
$launcher->entryPoint(Program::class);
$launcher->launch();
