<?php

declare(strict_types=1);

namespace Modules\Common\Core\Support;

use Aws\S3\S3Client;
use InvalidArgumentException;

class SignedStorageUrlService
{
    public function generate(
        string $key,
        string $contentType,
        string $visibility = 'private',
        ?string $uuid = null,
    ): array {
        $this->ensureEnvironmentVariablesAreAvailable();

        $client = $this->storageClient();
        $bucket = $_ENV['AWS_BUCKET'];
        $expiresAfter = (int) config('vapor.signed_storage_url_expires_after', 5);

        $signedRequest = $client->createPresignedRequest(
            $client->getCommand('putObject', array_filter([
                'Bucket' => $bucket,
                'Key' => $key,
                'ACL' => $visibility,
                'ContentType' => $contentType,
            ])),
            sprintf('+%s minutes', $expiresAfter),
        );

        $uri = $signedRequest->getUri();

        return [
            'uuid' => $uuid,
            'bucket' => $bucket,
            'key' => $key,
            'url' => $uri->getScheme() . '://' . $uri->getAuthority() . $uri->getPath() . '?' . $uri->getQuery(),
            'headers' => array_merge(
                $signedRequest->getHeaders(),
                ['Content-Type' => $contentType],
            ),
        ];
    }

    private function ensureEnvironmentVariablesAreAvailable(): void
    {
        $missing = array_diff_key(array_flip([
            'AWS_BUCKET',
            'AWS_DEFAULT_REGION',
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY',
        ]), $_ENV);

        if (empty($missing)) {
            return;
        }

        throw new InvalidArgumentException(
            'Unable to issue signed URL. Missing environment variables: ' . implode(', ', array_keys($missing))
        );
    }

    private function storageClient(): S3Client
    {
        return new S3Client([
            'region' => config('filesystems.disks.s3.region', $_ENV['AWS_DEFAULT_REGION']),
            'version' => 'latest',
            'signature_version' => 'v4',
            'use_path_style_endpoint' => config('filesystems.disks.s3.use_path_style_endpoint', false),
            'credentials' => array_filter([
                'key' => $_ENV['AWS_ACCESS_KEY_ID'] ?? null,
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'] ?? null,
                'token' => $_ENV['AWS_SESSION_TOKEN'] ?? null,
            ]),
        ]);
    }
}
