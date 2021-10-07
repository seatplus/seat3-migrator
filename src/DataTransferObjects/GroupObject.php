<?php

namespace Seatplus\Seat3Migrator\DataTransferObjects;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\DataTransferObject\DataTransferObject;

class GroupObject extends DataTransferObject
{
    public Command $command;
    public ?int $group_id;
    public ?Collection $character_ids;

    public function getCharacterIds()
    {
        if (! isset($this->character_ids)) {
            $this->character_ids = DB::connection('seat3_backup')
                ->table('users')
                ->when($this->group_id, fn (Builder $query) => $query->where('group_id', $this->group_id))
                ->pluck('id');
        }

        return $this->character_ids;
    }
}
