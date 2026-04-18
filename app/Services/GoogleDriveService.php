<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use RuntimeException;

class GoogleDriveService
{
    public function __construct(private readonly Client $client = new Client())
    {
    }

    public function upload(UploadedFile $file): string
    {
        $accessToken = $this->accessToken();
        $folderId = config('services.google_drive.folder_id');

        if (! $folderId) {
            throw new RuntimeException('Google Drive folder ID is not configured.');
        }

        $metadata = [
            'name' => uniqid('memorial_', true).'.'.$file->getClientOriginalExtension(),
            'parents' => [$folderId],
        ];

        $response = $this->client->post('https://www.googleapis.com/upload/drive/v3/files', [
            'headers' => [
                'Authorization' => 'Bearer '.$accessToken,
            ],
            'query' => [
                'uploadType' => 'multipart',
                'fields' => 'id,name,webViewLink,webContentLink',
            ],
            'multipart' => [
                [
                    'name' => 'metadata',
                    'contents' => json_encode($metadata, JSON_THROW_ON_ERROR),
                    'headers' => ['Content-Type' => 'application/json; charset=UTF-8'],
                ],
                [
                    'name' => 'file',
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'filename' => $file->getClientOriginalName(),
                    'headers' => ['Content-Type' => $file->getMimeType() ?: 'application/octet-stream'],
                ],
            ],
        ]);

        $uploaded = json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);

        if (config('services.google_drive.make_public')) {
            $this->makePublic($accessToken, $uploaded['id']);
        }

        return 'https://drive.google.com/uc?id='.$uploaded['id'];
    }

    private function accessToken(): string
    {
        $credentials = $this->credentials();
        $now = time();

        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ], JSON_THROW_ON_ERROR));

        $claim = $this->base64UrlEncode(json_encode([
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/drive.file',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ], JSON_THROW_ON_ERROR));

        $unsignedJwt = $header.'.'.$claim;

        if (! openssl_sign($unsignedJwt, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Could not sign Google service account JWT.');
        }

        $jwt = $unsignedJwt.'.'.$this->base64UrlEncode($signature);

        $response = $this->client->post('https://oauth2.googleapis.com/token', [
            'form_params' => [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ],
        ]);

        $token = json_decode((string) $response->getBody(), true, flags: JSON_THROW_ON_ERROR);

        return $token['access_token'];
    }

    private function credentials(): array
    {
        $json = config('services.google_drive.credentials_json');
        $path = config('services.google_drive.credentials_path');

        if ($json) {
            $credentials = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } elseif ($path && is_file($path)) {
            $credentials = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        } else {
            throw new RuntimeException('Google Drive service account credentials are not configured.');
        }

        foreach (['client_email', 'private_key'] as $key) {
            if (! Arr::has($credentials, $key)) {
                throw new RuntimeException("Google Drive credential is missing [{$key}].");
            }
        }

        return $credentials;
    }

    private function makePublic(string $accessToken, string $fileId): void
    {
        $this->client->post("https://www.googleapis.com/drive/v3/files/{$fileId}/permissions", [
            'headers' => [
                'Authorization' => 'Bearer '.$accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'role' => 'reader',
                'type' => 'anyone',
            ],
        ]);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
