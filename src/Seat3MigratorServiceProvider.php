<?php

namespace Seatplus\Seat3Migrator;

use Illuminate\Support\ServiceProvider;
use Seatplus\Seat3Migrator\Commands\Seat3MigratorCommand;

class Seat3MigratorServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        // Add Commands
        $this->commands([
            Seat3MigratorCommand::class,
        ]);
    }

    public function register()
    {
        $this->mergeConfigurations();
    }

    private function mergeConfigurations()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/seat3-migrator.database.php',
            'database.connections'
        );

        $this->mergeConfigFrom(
            __DIR__. '/../config/seat3-migrator.config.php',
            'seat3-migrator.config'
        );
    }
}
