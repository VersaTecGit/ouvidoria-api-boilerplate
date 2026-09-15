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

    /*
     * Signs against the `central` disk — the one the uploaded object is later
     * read from. The endpoint is the one the *browser* will PUT to: SigV4
     * signs the host, so it must be the public one when PHP and the browser
     * reach the storage by different names (MinIO locally). Without any
     * endpoint a custom S3 host would still get URLs pointing at AWS.
     */
    private function storageClient(): S3Client
    {
        $disk = config('filesystems.disks.central', []);

        return new S3Client(array_filter([
            'region' => $disk['region'] ?? $_ENV['AWS_DEFAULT_REGION'],
            'version' => 'latest',
            'signature_version' => 'v4',
            'endpoint' => $disk['public_endpoint'] ?? $disk['endpoint'] ?? null,
            'use_path_style_endpoint' => (bool) ($disk['use_path_style_endpoint'] ?? false),
            'credentials' => array_filter([
                'key' => $_ENV['AWS_ACCESS_KEY_ID'] ?? null,
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'] ?? null,
                'token' => $_ENV['AWS_SESSION_TOKEN'] ?? null,
            ]),
        ], fn (mixed $value): bool => $value !== null && $value !== ''));
    }
}
