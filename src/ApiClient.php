<?php
declare(strict_types=1);

namespace Advancedideasmechanics\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class ApiClient implements ApiClientInterface
{
    private $client;
    private array $oauthCredentials;
    private string $tokenStoreFile;

    private $debug;

    public function __construct(
        string $baseUrl,
        string $clientId,
        string $clientSecret,
        string $grantType,
        string $scope,
        string $userName,
        string $userSecret,
        string $tokenLocation,
        string $tokenFilename,
        bool $debug = false,
        array $additionalParams = []
    ) {
        $this->client = new Client([
            'base_uri' => $baseUrl,
        ]);

        $this->oauthCredentials = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => $grantType,
            'scope' => $scope,
            'username' => $userName,
            'password' => $userSecret
        ];

        $this->oauthCredentials = array_merge(
            $this->oauthCredentials,
            $additionalParams
        );

        $this->tokenStoreFile = $tokenLocation . '/' . $tokenFilename;

        $this->debug = $debug;
    }

    private function storeToken($tokens)
    {
        file_put_contents($this->tokenStoreFile, json_encode($tokens, JSON_PRETTY_PRINT));
    }

    private function loadToken()
    {
        if (file_exists($this->tokenStoreFile)) {
            return json_decode(file_get_contents($this->tokenStoreFile), true);
        }
        return false;
    }

    private function obtainToken()
    {
        try {
            $response = $this->client->request('POST', '/security/generate-token', [
                'json' => $this->oauthCredentials,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'debug' => $this->debug,
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (ClientException $e) {
            return json_encode($e->getMessage(), JSON_PRETTY_PRINT);
        }
    }

    private function refreshToken($refreshToken)
    {
        try {
            $response = $this->client->request('POST', '/security/refresh-token', [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                    'client_id' => $this->oauthCredentials['client_id'],
                    'client_secret' => $this->oauthCredentials['client_secret'],
                    'scope' => $this->oauthCredentials['scope'],
                    'username' => $this->oauthCredentials['username'],
                    'password' => $this->oauthCredentials['password']
                ]
            ]);

            $tokenData = json_decode($response->getBody()->getContents(), true);

            $this->storeToken($tokenData);

            return $tokenData['access_token'];
        } catch (ClientException $e) {
            return json_encode($e->getMessage(), JSON_PRETTY_PRINT);
        }
    }

    public function getAccessToken()
    {
        if (file_exists($this->tokenStoreFile)) {
            /*
             * If token.json exists load it, If not request access token from DotKernel API
             */
            $storedToken = $this->loadToken();
            if ($storedToken && isset($storedToken['expires_in']) && time() < $storedToken['expires_in']) {
                return $storedToken['access_token'];
            } else {
                $storedToken = $this->refreshToken($storedToken['refresh_token']);
                return $storedToken['access_token'];
            }
        } else {
            $storedToken = $this->obtainToken();
            if ($storedToken && isset($storedToken['expires_in']) && time() < $storedToken['expires_in']) {
                return $storedToken['access_token'];
            } else {
                $token = $this->obtainToken();

                if ($token) {
                    $this->storeToken($token);
                    return $token['access_token'];
                }
                return false;
            }
        }
    }

    public function makeApiRequest($endpoint, $body, $method = "GET", $additionalHeaders = [])
    {
        $accessToken = $this->getAccessToken();

        if ($accessToken) {
            $headers['Authorization'] = 'Bearer ' . $accessToken;
            $headers = array_merge($headers, $additionalHeaders);
            return $this->client->request($method, $endpoint, [
                'json' => json_encode($body, JSON_PRETTY_PRINT),
                'headers' => $headers,
            ]);
        } else {
            return false;
        }
    }
}