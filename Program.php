<?php

namespace Application;

use Artister\Web\Hosting\WebHost;
use Artister\Web\Hosting\IWebHostBuilder;

class Program
{
    public static function main(array $args = [])
    {
        self::createWebHostBuilder($args)->build()->run();
    }

    public static function createWebHostBuilder(array $args) : IWebHostBuilder
    {
        return WebHost::createBuilder($args)
            ->useStartup(Startup::class);
    }
}