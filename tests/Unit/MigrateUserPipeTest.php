<?php


use Seatplus\Seat3Migrator\DataTransferObjects\GroupObject;

it('can create users db entries', function () {
    setupUserDB(5);

    expect(\db()->table('users')->get())->toHaveCount(5);
});


it('creates users of same group', function () {

    setupUserDB(5, true);

    $group_id = db()->table('users')->first()->group_id;

    expect(db()->table('users')->where('group_id', $group_id)->get())->toHaveCount(5);

});

it('migrates five unique user groups', function () {

    setupUserDB(5);

    $pipe = new \Seatplus\Seat3Migrator\Pipes\MigrateUserPipe;

    // create group object
    $groupObject = new GroupObject([
        'group_id' => null,
        'command' => mockNonVerboseCommand(),
    ]);

    expect(\Seatplus\Auth\Models\User::all())->toHaveCount(0);

    $pipe->handle($groupObject, fn() => null);

    $numberUsers = db()->table('users')->count();
    expect(\Seatplus\Auth\Models\User::all())->toHaveCount($numberUsers);
    expect(\Seatplus\Auth\Models\CharacterUser::all())->toHaveCount($numberUsers);
});

it('migrates one unique user group with 5 characters', function () {

    setupUserDB(5, true);

    $pipe = new \Seatplus\Seat3Migrator\Pipes\MigrateUserPipe;

    $group_id = (int) db()->table('users')->pluck('group_id')->unique()->toArray()[0];

    // create group object
    $groupObject = new GroupObject([
        'group_id' => $group_id,
        'command' => mockNonVerboseCommand(),
    ]);

    expect(\Seatplus\Auth\Models\User::all())->toHaveCount(0);

    $pipe->handle($groupObject, fn() => null);

    $numberUsers = db()->table('users')->count();
    expect(\Seatplus\Auth\Models\User::all())->toHaveCount(1);
    expect(\Seatplus\Auth\Models\CharacterUser::all())->toHaveCount(5);
});
