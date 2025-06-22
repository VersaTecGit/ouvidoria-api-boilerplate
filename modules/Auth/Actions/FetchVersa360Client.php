<?php

declare(strict_types=1);

namespace Modules\Auth\Actions;

use Illuminate\Support\Facades\Http;
use Modules\Common\Core\Exceptions\ApiException;

final readonly class FetchVersa360Client
{
    public function __construct(private LoggedUser $loggedUser) {}

    public function handle(): array
    {
        $credentials = tenant()->versa360Credential;

        $tokenResponse = Http::post('https://api.versa360.com.br/v1/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $credentials->client_id,
            'client_secret' => $credentials->client_secret,
            'scope' => '',
        ]);

        $token = $tokenResponse->json()['access_token'];

        $clientResponse = Http::withToken($token)->get('https://api.versa360.com.br/v1/clients/me')->json();

        if (! $clientResponse['status']) {
            throw new ApiException('Failed to fetch client data from Versa360');
        }

        return $clientResponse['result'];
    }
}
