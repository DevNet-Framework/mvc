<?php

namespace Application;

use DevNet\Web\Configuration\IConfiguration;
use DevNet\Web\Dependency\IServiceCollection;
use DevNet\Web\Dispatcher\IApplicationBuilder;
use DevNet\Web\Extensions\ServiceCollectionExtensions;
use DevNet\Web\Extensions\ApplicationBuilderExtensions;

class Startup
{
    private IConfiguration $Configuration;

    public function __construct(IConfiguration $configuration)
    {
        $this->Configuration = $configuration;
    }

    public function configureServices(IServiceCollection $services)
    {
        $services->addMvc();
        
        $services->addAntiforgery();
        
        $services->addAuthentication();

        $services->addAuthorisation();
    }

    public function configure(IApplicationBuilder $app)
    {
        $app->UseExceptionHandler();

        $app->useRouter();

        $app->useAuthentication();

        $app->useAuthorization();
        
        $app->useEndpoint(function($routes)
        {
            $routes->mapRoute("default", "{controller=Home}/{action=Index}/{id?}");
        });
    }
}