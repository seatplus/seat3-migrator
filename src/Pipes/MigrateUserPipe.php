<?php

namespace Seatplus\Seat3Migrator\Pipes;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Seatplus\Auth\Models\CharacterUser;
use Seatplus\Auth\Models\User;

class MigrateUserPipe extends AbstractMigratorPipeClass
{
    public function execute() : void
    {
        $this->alert('Start migrating of users');

        $groups = $this->isMissingTable('users') ? collect() : DB::connection('seat3_backup')
            ->table('users')
            ->select('name', 'id', 'group_id', 'character_owner_hash')
            ->when($this->groupObject->group_id, fn (Builder $query) => $query->where('group_id', $this->groupObject->group_id))
            //->groupBy('group_id', 'id', 'name')
            ->cursor()
            ->groupBy('group_id');

        $this->withProgressBar($groups, function (Collection $group) {
            $group = $group
                //remove the admin user
                ->reject(fn ($user) => data_get($user, 'id') === 1 || data_get($user, 'name') === 'admin')
                // remove already existing character users
                ->reject(fn ($user) => CharacterUser::firstWhere('character_id', data_get($user, 'id')));

            $user = User::create([
                'main_character_id' => data_get($group->first(), 'id'),
                'active' => true,
            ]);

            $group->each(fn ($character) => CharacterUser::firstOrCreate([
                'user_id' => $user->id,
                'character_id' => data_get($character, 'id'),
            ], [
                'character_owner_hash' => $character->character_owner_hash,
            ]));
        });
    }
}
