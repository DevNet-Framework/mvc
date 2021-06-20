<?php

namespace Application;

use DevNet\Core\Hosting\WebHost;
use DevNet\Core\Hosting\IWebHostBuilder;

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
