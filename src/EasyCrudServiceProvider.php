<?php
namespace EasyCrud;
use Carbon\Laravel\ServiceProvider;
use EasyCrud\console\commands\MakeCrudCommand;

class EasyCrudServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeCrudCommand::class
            ]);
        }
    }

    /**
     * @return void
     */
    public function register(): void
    {

    }

}
