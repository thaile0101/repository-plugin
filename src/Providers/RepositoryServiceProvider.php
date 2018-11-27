<?php


namespace Elidev\Repository\Providers;

use Illuminate\Support\ServiceProvider;
use Elidev\Repository\Generators\Commands\RepositoryCommand;

class RepositoryServiceProvider extends ServiceProvider
{

    /**
     * Commands
     * @var $commands
     */
    protected $commands = [
        RepositoryCommand::class,
    ];

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../resources/config/repository.php' => config_path('elidev-repository.php'),
            __DIR__ . '/../../resources/config/Repositories' => app_path('Repositories'),
        ]);

        $this->mergeConfigFrom(__DIR__ . '/../../resources/config/repository.php', 'elidev-repository');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands($this->commands);
    }

}