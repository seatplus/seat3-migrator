<?php

namespace Seatplus\Seat3Migrator\Pipes;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Seatplus\EsiClient\DataTransferObjects\EsiAuthentication;
use Seatplus\EsiClient\Services\UpdateRefreshTokenService;
use Seatplus\Eveapi\Models\RefreshToken;

class MigrateRefreshTokenPipe extends AbstractMigratorPipeClass
{
    private Client $httpClient;
    private UpdateRefreshTokenService $update_service;

    public function execute(): void
    {
        $this->alert('Migrating refresh_tokens');

        // Get all non-deleted tokens
        $refresh_tokens = $this->isMissingTable('refresh_tokens') ? collect() : DB::connection('seat3_backup')
            ->table('refresh_tokens')
            ->whereIn('character_id', $this->groupObject->getCharacterIds()->toArray())
            ->whereNull('deleted_at')
            ->get();

        $this->withProgressBar($refresh_tokens, fn ($refreshToken) => $this->migrate($refreshToken));
    }

    private function migrate($refreshToken)
    {
        $character_id = data_get($refreshToken, 'character_id');

        $existing_token = RefreshToken::find($character_id);

        if ($existing_token) {
            $this->info("Already existing: skipping token for character_id ${character_id}");

            return;
        }

        $authentication = new EsiAuthentication([
            'client_id' => config('seat3-migrator.config.eve_client_id'),
            'secret' => config('seat3-migrator.config.eve_client_secret'),
            'access_token' => data_get($refreshToken, 'token'),
            'refresh_token' => data_get($refreshToken, 'refresh_token'),
        ]);

        // Values are access_token // expires_in // token_type // refresh_token
        $token = $this->getUpdateService()->getRefreshTokenResponse($authentication);

        RefreshToken::updateOrCreate([
            'character_id' => $character_id,
        ], [
            'refresh_token' => data_get($token, 'refresh_token'),
            'token' => data_get($token, 'access_token'),
            'expires_on' => carbon()->addSeconds(data_get($token, 'expires_in')),
        ]);
    }

    private function getHttpClient(): Client
    {
        if (! isset($this->httpClient)) {
            $this->httpClient = new Client();
        }

        return $this->httpClient;
    }

    private function getUpdateService()
    {
        if (! isset($this->update_service)) {
            $this->update_service = (new UpdateRefreshTokenService)->setClient($this->getHttpClient());
        }

        return $this->update_service;
    }

    /**
     * @param UpdateRefreshTokenService $update_service
     */
    public function setUpdateService(UpdateRefreshTokenService $update_service): void
    {
        $this->update_service = $update_service;
    }

    /**
     * @param Client $httpClient
     */
    public function setHttpClient(Client $httpClient): void
    {
        $this->httpClient = $httpClient;
    }
}
