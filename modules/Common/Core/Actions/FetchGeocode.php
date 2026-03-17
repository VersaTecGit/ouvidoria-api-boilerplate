<?php

declare(strict_types=1);

namespace Modules\Common\Core\Actions;

final readonly class FetchGeocode
{
    public function handle(array $address): array
    {
        $formattedAddress = implode(',', $address);
        $url = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($formattedAddress) . '&format=json&limit=1';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: API Boilerplate - Versa Tecnologia',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);

            return [];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return [];
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        return [
            'lat' => $data[0]['lat'] ?? null,
            'lon' => $data[0]['lon'] ?? null,
        ];
    }
}
