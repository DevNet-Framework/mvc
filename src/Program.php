<?php

namespace Application;

use DevNet\Web\Extensions\ApplicationBuilderExtensions;
use DevNet\Web\Extensions\ServiceCollectionExtensions;
use DevNet\Web\Hosting\WebHost;

class Program
{
    public static function main(array $args = [])
    {
        $builder = WebHost::createDefaultBuilder($args);
        $builder->register(function ($services) {
            $services->addAuthentication(function ($builder) {
                $builder->addCookie();
            });

            $services->addAuthorization();
            $services->addAntiforgery();
        });

        $host = $builder->build();
        $host->start(function ($app) {
            if (!$app->Environment->isDevelopment()) {
                $app->UseExceptionHandler("/error");
            }

            $app->useRouter();
            $app->useAuthentication();

            $app->useEndpoint(function ($routes) {
                $routes->mapControllers();
            });
        });
    }
}
