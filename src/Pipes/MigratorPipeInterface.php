<?php

namespace Seatplus\Seat3Migrator\Pipes;

use Closure;
use Illuminate\Console\Command;
use Seatplus\Seat3Migrator\Commands\Seat3MigratorCommand;
use Seatplus\Seat3Migrator\DataTransferObjects\GroupObject;

interface MigratorPipeInterface
{
    //public function handle(Seat3MigratorCommand $command, Closure $next);
    public function handle(GroupObject $groupObject, Closure $next);
}
