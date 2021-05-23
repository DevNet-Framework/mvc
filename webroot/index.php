<?php declare(strict_types = 1);

use DevNet\System\Runtime\launcher;
use Application\Program;

$autoloadPath = __DIR__ . "/../vendor/autoload.php";
$projectPath  = "../project.phproj";

$project = new SimpleXMLElement("<project></project>");

if (file_exists($projectPath))
{
    $project = simplexml_load_file($projectPath);
}

if (!file_exists((string)$project->autoload->path."/autoload.php"))
{
    $path = dirname(exec("devnet --path"));
    
    if (!empty($path))
    {
        $project->autoload->path = $path;
        $dom                     = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = true;

        $dom->loadXML($project->asXML());
        $dom->save($projectPath);
    }
}

if (file_exists((string)$project->autoload->path."/autoload.php"))
{
    require (string)$project->autoload->path."/autoload.php";
}

if (file_exists($autoloadPath))
{
    require $autoloadPath;
}

$launcher = launcher::getLauncher();
$launcher->workspace(dirname(__DIR__));
$launcher->entryPoint(Program::class);
$launcher->launch();
