<?php

namespace Application;

use DevNet\Web\Hosting\WebHost;
use DevNet\Web\Hosting\IWebHostBuilder;

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