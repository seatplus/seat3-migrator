<?php

use Seatplus\Seat3Migrator\DataTransferObjects\GroupObject;
use Seatplus\Seat3Migrator\Pipes\MigrateRefreshTokenPipe;

beforeEach(function (){
    config([
        'seat3-migrator.eve_client_id' => 'id',
        'seat3-migrator.eve_client_secret' => 'secret'
    ]);
});

afterEach(fn() => Mockery::close());

it('creates refresh_token entries', function () {

    setupRefreshTokens(5);

    expect(\db()->table('refresh_tokens')->get())->toHaveCount(5);

    // create with array
    setupRefreshTokens([1,2]);

    expect(\db()->table('refresh_tokens')->get())->toHaveCount(5+2);
});

it('alerts', closure: function () {

    $pipe = new MigrateRefreshTokenPipe;

    $refresh_token = \Seatplus\Eveapi\Models\RefreshToken::factory()->make();

    $mock = Mockery::mock(\Seatplus\EsiClient\Services\UpdateRefreshTokenService::class);
    $mock->shouldReceive('getRefreshTokenResponse')
        ->andReturn([
            'refresh_token' => 'bar',
            'access_token' => $refresh_token->token,
            'expires_in' => 20*60, //20 Minutes
        ]);

    $pipe->setUpdateService($mock);

    setupUserDB(5);
    setupRefreshTokens(db()->table('users')->pluck('id')->toArray());


    // create group object
    $groupObject = new GroupObject([
        'group_id' => null,
        'command' => mockNonVerboseCommand(),
    ]);

    // Assert

    expect(\Seatplus\Eveapi\Models\RefreshToken::all())->toHaveCount(0);
    $pipe->handle($groupObject, fn() => null );

    expect(\Seatplus\Eveapi\Models\RefreshToken::all())
        ->toHaveCount(db()->table('refresh_tokens')->whereNull('deleted_at')->count());

});
