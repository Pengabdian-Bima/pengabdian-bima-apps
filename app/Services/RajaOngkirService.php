<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RajaOngkirService
{
    /**
     * Get Komerce Origin ID by resolving the configured origin name or ID.
     */
    public function getOriginId()
    {
        $origin = config('services.rajaongkir.origin', '128');

        if (is_numeric($origin)) {
            return (int) $origin;
        }

        return Cache::remember('komerce_origin_id_' . md5($origin), now()->addDays(30), function () use ($origin) {
            $apiKey = config('services.rajaongkir.api_key');
            if (!$apiKey) {
                return 128; // Default mock ID
            }

            try {
                $response = Http::withoutVerifying()->withHeaders([
                    'key' => $apiKey
                ])->timeout(5)->get("https://rajaongkir.komerce.id/api/v1/destination/domestic-destination", [
                    'search' => $origin
                ]);

                if ($response->successful()) {
                    $data = $response->json()['data'] ?? [];
                    if (!empty($data)) {
                        return $data[0]['id'];
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Komerce getOriginId error: " . $e->getMessage());
            }

            return 128; // Default fallback
        });
    }

    /**
     * Search location using Komerce domestic destination search API.
     */
    public function searchLocation(string $query)
    {
        if (strlen($query) < 2) {
            return [];
        }

        $apiKey = config('services.rajaongkir.api_key');
        if (!$apiKey) {
            return $this->getMockSearchResults($query);
        }

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'key' => $apiKey
            ])->timeout(5)->get("https://rajaongkir.komerce.id/api/v1/destination/domestic-destination", [
                'search' => $query
            ]);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                $results = [];

                if (is_array($data)) {
                    foreach ($data as $item) {
                        $results[] = [
                            'label' => strtoupper($item['label'] ?? ''),
                            'province' => $item['province_name'] ?? $item['province'] ?? '',
                            'city' => $item['city_name'] ?? $item['city'] ?? '',
                            'city_id' => $item['id'], // Komerce location ID
                            'district' => $item['district_name'] ?? $item['district'] ?? '',
                            'village' => $item['subdistrict_name'] ?? $item['subdistrict'] ?? '',
                            'postal_code' => $item['zip_code'] ?? '',
                        ];
                    }
                    return $results;
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Komerce searchLocation error: " . $e->getMessage());
        }

        return $this->getMockSearchResults($query);
    }

    /**
     * Calculate cost using Komerce calculate domestic cost API.
     */
    public function calculateCost($destinationCityId, $weightGrams, $courier)
    {
        $apiKey = config('services.rajaongkir.api_key');
        if (!$apiKey) {
            return $this->getMockCost($courier, $destinationCityId);
        }

        $origin = $this->getOriginId();

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'key' => $apiKey
            ])->asForm()->timeout(5)->post("https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost", [
                'origin' => $origin,
                'destination' => $destinationCityId,
                'weight' => $weightGrams,
                'courier' => strtolower($courier),
            ]);

            if ($response->successful()) {
                $rawResults = $response->json()['data'] ?? [];
                if (!empty($rawResults)) {
                    $costs = [];
                    $courierName = '';
                    $courierCode = strtolower($courier);

                    foreach ($rawResults as $item) {
                        if (empty($courierName) && !empty($item['name'])) {
                            $courierName = $item['name'];
                        }

                        $value = isset($item['cost']) ? (float)$item['cost'] : 0.0;
                        $etd = $item['etd'] ?? '';

                        $costs[] = [
                            'service' => $item['service'] ?? '',
                            'description' => $item['description'] ?? '',
                            'cost' => [
                                [
                                    'value' => $value,
                                    'etd' => $etd,
                                    'note' => ''
                                ]
                            ]
                        ];
                    }

                    if (empty($courierName)) {
                        $courierName = strtoupper($courier);
                    }

                    return [
                        [
                            'code' => $courierCode,
                            'name' => $courierName,
                            'costs' => $costs
                        ]
                    ];
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Komerce calculateCost error: " . $e->getMessage());
        }

        return $this->getMockCost($courier, $destinationCityId);
    }

    /**
     * Mock search results if API key is not yet set or calls fail.
     */
    private function getMockSearchResults($query)
    {
        $mockLocations = [
            ['id' => 406, 'label' => 'Jakarta Baru, Bula Barat, Seram Bagian Timur, 97554', 'province' => 'Maluku', 'city' => 'Seram Bagian Timur', 'district' => 'Bula Barat', 'subdistrict' => 'Jakarta Baru', 'zip_code' => '97554'],
            ['id' => 152, 'label' => 'Sukabumi Utara (Ilir), Kebon Jeruk, Jakarta Barat, 11540', 'province' => 'DKI Jakarta', 'city' => 'Jakarta Barat', 'district' => 'Kebon Jeruk', 'subdistrict' => 'Sukabumi Utara', 'zip_code' => '11540'],
            ['id' => 153, 'label' => 'Joglo, Kembangan, Jakarta Barat, 11640', 'province' => 'DKI Jakarta', 'city' => 'Jakarta Barat', 'district' => 'Kembangan', 'subdistrict' => 'Joglo', 'zip_code' => '11640'],
            ['id' => 154, 'label' => 'Kembangan Selatan, Kembangan, Jakarta Barat, 11610', 'province' => 'DKI Jakarta', 'city' => 'Jakarta Barat', 'district' => 'Kembangan', 'subdistrict' => 'Kembangan Selatan', 'zip_code' => '11610'],
            ['id' => 128, 'label' => 'Limboto, Gorontalo, Gorontalo, 96181', 'province' => 'Gorontalo', 'city' => 'Gorontalo', 'district' => 'Limboto', 'subdistrict' => 'Limboto', 'zip_code' => '96181'],
        ];

        $results = [];
        foreach ($mockLocations as $item) {
            if (stripos($item['label'], $query) !== false) {
                $results[] = [
                    'label' => strtoupper($item['label']),
                    'province' => $item['province'],
                    'city' => $item['city'],
                    'city_id' => $item['id'],
                    'district' => $item['district'],
                    'village' => $item['subdistrict'],
                    'postal_code' => $item['zip_code'],
                ];
            }
        }

        return $results;
    }

    /**
     * Mock shipping cost calculation fallback.
     */
    private function getMockCost($courier, $destinationCityId)
    {
        $courier = strtolower($courier);
        $baseCost = 15000;

        if ($destinationCityId) {
            $baseCost += ((int) $destinationCityId % 5) * 5000;
        }

        $services = [];
        if ($courier === 'jne') {
            $services = [
                ['service' => 'REG', 'description' => 'Layanan Reguler', 'cost' => [['value' => $baseCost, 'etd' => '2-3', 'note' => '']]],
                ['service' => 'OKE', 'description' => 'Ongkos Kirim Ekonomis', 'cost' => [['value' => $baseCost - 3000, 'etd' => '4-5', 'note' => '']]],
                ['service' => 'YES', 'description' => 'Yakin Esok Sampai', 'cost' => [['value' => $baseCost + 10000, 'etd' => '1-1', 'note' => '']]]
            ];
        } elseif ($courier === 'pos') {
            $services = [
                ['service' => 'Pos Reguler', 'description' => 'Pos Reguler', 'cost' => [['value' => $baseCost - 1000, 'etd' => '3-4', 'note' => '']]],
                ['service' => 'Pos Kilat Khusus', 'description' => 'Pos Kilat Khusus', 'cost' => [['value' => $baseCost + 2000, 'etd' => '2-3', 'note' => '']]]
            ];
        } elseif ($courier === 'tiki') {
            $services = [
                ['service' => 'REG', 'description' => 'Regular Service', 'cost' => [['value' => $baseCost, 'etd' => '2-3', 'note' => '']]],
                ['service' => 'ECO', 'description' => 'Economy Service', 'cost' => [['value' => $baseCost - 4000, 'etd' => '5-6', 'note' => '']]]
            ];
        } elseif ($courier === 'jnt') {
            $services = [
                ['service' => 'EZ', 'description' => 'Layanan Reguler EZ', 'cost' => [['value' => $baseCost, 'etd' => '2-3', 'note' => '']]],
                ['service' => 'J&T ECO', 'description' => 'Layanan Ekonomis', 'cost' => [['value' => $baseCost - 3000, 'etd' => '4-5', 'note' => '']]]
            ];
        } elseif ($courier === 'sicepat') {
            $services = [
                ['service' => 'SIUNTUNG', 'description' => 'Layanan Reguler SiUntung', 'cost' => [['value' => $baseCost, 'etd' => '2-3', 'note' => '']]],
                ['service' => 'BEST', 'description' => 'Besok Sampai Tujuan', 'cost' => [['value' => $baseCost + 8000, 'etd' => '1-1', 'note' => '']]]
            ];
        } elseif ($courier === 'anteraja') {
            $services = [
                ['service' => 'REG', 'description' => 'Layanan Reguler', 'cost' => [['value' => $baseCost, 'etd' => '2-3', 'note' => '']]],
                ['service' => 'NEXTDAY', 'description' => 'Layanan Kilat', 'cost' => [['value' => $baseCost + 7000, 'etd' => '1-1', 'note' => '']]]
            ];
        } elseif ($courier === 'wahana') {
            $services = [
                ['service' => 'Normal', 'description' => 'Layanan Normal', 'cost' => [['value' => $baseCost - 5000, 'etd' => '3-5', 'note' => '']]]
            ];
        } elseif ($courier === 'ninja') {
            $services = [
                ['service' => 'Standard', 'description' => 'Layanan Standar', 'cost' => [['value' => $baseCost, 'etd' => '2-3', 'note' => '']]]
            ];
        } elseif ($courier === 'lion') {
            $services = [
                ['service' => 'REGPACK', 'description' => 'Regular Package', 'cost' => [['value' => $baseCost, 'etd' => '2-3', 'note' => '']]]
            ];
        } else {
            $services = [
                ['service' => 'REG', 'description' => 'Layanan Reguler', 'cost' => [['value' => $baseCost, 'etd' => '2-3', 'note' => '']]]
            ];
        }

        return [
            [
                'code' => $courier,
                'name' => strtoupper($courier),
                'costs' => $services
            ]
        ];
    }
}
