<?php

namespace Seatplus\Seat3Migrator\Commands;

use Illuminate\Console\Command;
use Illuminate\Pipeline\Pipeline;
use Seatplus\Seat3Migrator\DataTransferObjects\GroupObject;
use Seatplus\Seat3Migrator\Pipes\MigrateRefreshTokenPipe;
use Seatplus\Seat3Migrator\Pipes\MigrateUserPipe;

class Seat3MigratorCommand extends Command
{
    public $signature = 'seatplus:migrate {group_id?}';

    public $description = 'Migrate data from seat3 backup';

    public function handle()
    {
        $group_id = $this->argument('group_id');

        if (! $group_id) {
            if (! $this->confirm('You did not select any specific seat group to migrate, is this correct?')) {
                return;
            }
        } else {
            $this->info("Migrating seat user group with id ${group_id}");
        }

        $this->alert('Start migration of a seat3 database into seatplus');

        if (! $this->confirm('Do you wish to continue?')) {
            $this->error('migration did not start');

            return;
        }

        app(Pipeline::class)
            ->send(new GroupObject([
                'group_id' => $group_id ? (int) $group_id : null,
                'command' => $this,
            ]))
            ->through([
                MigrateUserPipe::class,
                MigrateRefreshTokenPipe::class,
            ])
            ->then(function () {
                $this->newLine();
                $this->info('processed all pipes');
            });
    }
}
