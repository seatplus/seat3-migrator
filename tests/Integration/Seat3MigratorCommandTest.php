<?php

it('migrator warns if no group_id has been provided', function () {

    test()->artisan('seatplus:migrate')
        ->expectsConfirmation('You did not select any specific seat group to migrate, is this correct?', 'n')
        ->assertExitCode(0);
});

it('stops the migration if start has not been confirmed', function () {
    test()->artisan('seatplus:migrate 12')
        ->expectsConfirmation('Do you wish to continue?', 'n')
        ->expectsOutput('migration did not start')
        ->assertExitCode(0);
});

it('runs the migration', function () {
    test()->artisan('seatplus:migrate 12')
        ->expectsConfirmation('Do you wish to continue?', 'yes')
        ->expectsOutput('processed all pipes')
        ->assertExitCode(0);
});
