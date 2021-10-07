<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Seatplus\Seat3Migrator\Tests\TestCase;

uses(TestCase::class)
    ->group('integration')
    ->in('Integration');

uses(TestCase::class)
    ->group('unit')
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function faker()
{
    $faker = Faker\Factory::create();

    return $faker;
}

function db()
{
    return DB::connection('seat3_backup');
}

function setupUserDB(int $count, bool $sameGroup = false)
{
    \db()->statement('create table users
                            (
                                id                   bigint               not null
                                    primary key,
                                group_id             int                  not null,
                                name                 varchar(255)         not null,
                                active               tinyint(1) default 1 not null,
                                character_owner_hash varchar(255)         not null,
                                last_login           datetime             null,
                                last_login_source    varchar(255)         null,
                                remember_token       varchar(100)         null,
                                created_at           timestamp            null,
                                updated_at           timestamp            null,
                                constraint users_name_unique
                                    unique (name)
                            )');

    $users = collect();
    $group_id = $sameGroup ? faker()->numberBetween(1, 999) : null;

    for ($i = 1; $i <= $count ; $i++) {
        $users->push([
            'id' => faker()->numberBetween(9000000, 98000000),
            'group_id' => $group_id ?? faker()->numberBetween(1, 999),
            'name' => faker()->name,
            'active' => faker()->boolean,
            'character_owner_hash' => faker()->uuid,
            'last_login' => faker()->dateTimeThisMonth,
            'last_login_source' => faker()->userAgent,
            'remember_token' => faker()->md5,
            'created_at' => faker()->dateTimeThisYear,
            'updated_at' => faker()->dateTimeThisMonth,
        ]);
    }

    if ($users->count() > 0) {
        \db()->table('users')->insert($users->toArray());
    }
}

function setupRefreshTokens(array|int $ids)
{
    $helper_function = function (int $ids) {
        $collection = collect();

        for ($i = 0; $i < $ids; $i++) {
            $collection->push(faker()->numberBetween(9000000, 98000000));
        }

        return $collection->toArray();
    };

    $ids = is_array($ids) ? $ids : $helper_function($ids);

    \db()->statement(
        'create table if not exists refresh_tokens
                    (
                        character_id  bigint                       not null
                            primary key,
                        refresh_token mediumtext                   not null,
                        scopes        longtext  not null,
                        expires_on    datetime                     not null,
                        token         varchar(255)                 not null,
                        created_at    timestamp                    null,
                        updated_at    timestamp                    null,
                        deleted_at    timestamp                    null
                    )'
    );

    $refresh_tokens = collect();

    foreach ($ids as $id) {
        $refresh_tokens->push([
            'character_id' => $id,
            'refresh_token' => faker()->sha1,
            'scopes' => json_encode(faker()->words(5)),
            'expires_on' => faker()->dateTime,
            'token' => faker()->sha1,
            'created_at' => faker()->iso8601,
            'updated_at' => faker()->iso8601,
            'deleted_at' => faker()->boolean(10) ? faker()->iso8601 : null,
        ]);
    }

    \db()->table('refresh_tokens')
        ->insert($refresh_tokens->toArray());
}

function mockNonVerboseCommand()
{
    $output = Mockery::mock(\Illuminate\Console\OutputStyle::class);
    $output->shouldReceive('writeln', 'newLine');

    // mocking methods for progressbar
    $output->shouldReceive('isDecorated')
        ->andReturnFalse();
    $output->shouldReceive('getVerbosity')
        ->andReturn(\Symfony\Component\Console\Output\OutputInterface::VERBOSITY_QUIET);

    $progressBar = new \Symfony\Component\Console\Helper\ProgressBar($output);

    $output->shouldReceive('createProgressBar')->andReturn($progressBar);


    $command = new \Illuminate\Console\Command();
    $command->setOutput($output);

    return $command;
}

function isMissingTable(string $name) : bool
{
    return ! Schema::connection('seat3_backup')->hasTable($name);
}
