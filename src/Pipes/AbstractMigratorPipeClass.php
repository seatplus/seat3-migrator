<?php

namespace Seatplus\Seat3Migrator\Pipes;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Seatplus\Seat3Migrator\DataTransferObjects\GroupObject;

abstract class AbstractMigratorPipeClass implements MigratorPipeInterface
{
    protected GroupObject $groupObject;

    abstract public function execute(): void;

    final public function handle(GroupObject $groupObject, Closure $next)
    {
        $this->groupObject = $groupObject;

        $this->execute();

        return $next($groupObject);
    }

    protected function withProgressBar(iterable|int $totalSteps, Closure $closure)
    {
        $this->getCommand()->withProgressBar($totalSteps, $closure);
    }

    protected function alert(string $string)
    {
        $this->getCommand()->alert($string);
    }

    protected function info(string $string, int|string|null $verbosity = null): void
    {
        $this->getCommand()->info($string, $verbosity);
    }

    private function getCommand(): Command
    {
        return $this->groupObject->command;
    }

    protected function isMissingTable(string $name) : bool
    {
        return ! Schema::connection('seat3_backup')->hasTable($name);
    }
}
