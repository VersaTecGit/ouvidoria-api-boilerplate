<?php

declare(strict_types=1);

namespace Modules\Common\Core\Support;

use DateTimeInterface;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

/**
 * Signs temporary URLs against the disk's `public_endpoint` when one is set.
 *
 * SigV4 signs the host, so a URL signed for the endpoint PHP talks to
 * (`minio:9000` locally) is rejected the moment the browser reaches the
 * storage by another name. Laravel's `temporary_url` option only swaps the
 * host *after* signing and fails exactly there. Without `public_endpoint`
 * this behaves as the default generator — production on AWS is untouched.
 */
final class PublicEndpointUrlGenerator extends DefaultUrlGenerator
{
    public function getTemporaryUrl(DateTimeInterface $expiration, array $options = []): string
    {
        $config = config("filesystems.disks.{$this->getDiskName()}", []);
        $publicEndpoint = $config['public_endpoint'] ?? null;

        if (empty($publicEndpoint)) {
            return parent::getTemporaryUrl($expiration, $options);
        }

        $publicDisk = Storage::build([
            ...$config,
            'endpoint' => $publicEndpoint,
        ]);

        return $publicDisk->temporaryUrl($this->getPathRelativeToRoot(), $expiration, $options);
    }
}
