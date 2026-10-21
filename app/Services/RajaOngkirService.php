<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class RajaOngkirService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://pro.rajaongkir.com/api';
    protected string $originCityId;

    public function __construct()
    {
        $this->apiKey = env('RAJAONGKIR_API_KEY', '');
        $this->originCityId = env('RAJAONGKIR_ORIGIN_CITY_ID', '152'); // Jakarta Pusat
    }

    protected function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Get all provinces.
     */
    public function getProvinces(): array
    {
        /*
        if ($this->isConfigured()) {
            try {
                $response = Http::withHeaders([
                    'key' => $this->apiKey
                ])->get($this->baseUrl . '/province');

                if ($response->successful()) {
                    return $response->json()['rajaongkir']['results'] ?? [];
                }
                Log::error('RajaOngkir getProvinces error: ' . $response->body());
            } catch (Exception $e) {
                Log::error('RajaOngkir getProvinces exception: ' . $e->getMessage());
            }
        }
        */

        return [
            ['province_id' => '1', 'province' => 'DKI Jakarta'],
            ['province_id' => '2', 'province' => 'Jawa Barat'],
            ['province_id' => '3', 'province' => 'Jawa Tengah'],
            ['province_id' => '4', 'province' => 'Jawa Timur'],
            ['province_id' => '5', 'province' => 'Banten'],
        ];
    }

    /**
     * Get cities by province ID.
     */
    public function getCities(string $provinceId): array
    {
        /*
        if ($this->isConfigured()) {
            try {
                $response = Http::withHeaders([
                    'key' => $this->apiKey
                ])->get($this->baseUrl . '/city', [
                    'province' => $provinceId
                ]);

                if ($response->successful()) {
                    return $response->json()['rajaongkir']['results'] ?? [];
                }
                Log::error('RajaOngkir getCities error: ' . $response->body());
            } catch (Exception $e) {
                Log::error('RajaOngkir getCities exception: ' . $e->getMessage());
            }
        }
        */

        $mockCities = [
            '1' => [
                ['city_id' => '151', 'city_name' => 'Jakarta Barat', 'type' => 'Kota'],
                ['city_id' => '152', 'city_name' => 'Jakarta Pusat', 'type' => 'Kota'],
                ['city_id' => '153', 'city_name' => 'Jakarta Selatan', 'type' => 'Kota'],
                ['city_id' => '154', 'city_name' => 'Jakarta Timur', 'type' => 'Kota'],
                ['city_id' => '155', 'city_name' => 'Jakarta Utara', 'type' => 'Kota'],
            ],
            '2' => [
                ['city_id' => '22', 'city_name' => 'Bandung', 'type' => 'Kota'],
                ['city_id' => '23', 'city_name' => 'Bogor', 'type' => 'Kota'],
                ['city_id' => '24', 'city_name' => 'Bekasi', 'type' => 'Kota'],
                ['city_id' => '25', 'city_name' => 'Depok', 'type' => 'Kota'],
            ],
            '3' => [
                ['city_id' => '398', 'city_name' => 'Semarang', 'type' => 'Kota'],
                ['city_id' => '501', 'city_name' => 'Surakarta', 'type' => 'Kota'],
            ],
            '4' => [
                ['city_id' => '444', 'city_name' => 'Surabaya', 'type' => 'Kota'],
                ['city_id' => '256', 'city_name' => 'Malang', 'type' => 'Kota'],
            ],
            '5' => [
                ['city_id' => '455', 'city_name' => 'Tangerang', 'type' => 'Kota'],
                ['city_id' => '456', 'city_name' => 'Tangerang Selatan', 'type' => 'Kota'],
            ],
        ];

        return $mockCities[$provinceId] ?? [];
    }

    /**
     * Get subdistricts by city ID.
     */
    public function getSubdistricts(string $cityId): array
    {
        /*
        if ($this->isConfigured()) {
            try {
                $response = Http::withHeaders([
                    'key' => $this->apiKey
                ])->get($this->baseUrl . '/subdistrict', [
                    'city' => $cityId
                ]);

                if ($response->successful()) {
                    return $response->json()['rajaongkir']['results'] ?? [];
                }
                Log::error('RajaOngkir getSubdistricts error: ' . $response->body());
            } catch (Exception $e) {
                Log::error('RajaOngkir getSubdistricts exception: ' . $e->getMessage());
            }
        }
        */

        return [
            ['subdistrict_id' => $cityId . '01', 'subdistrict_name' => 'Kecamatan Utama'],
            ['subdistrict_id' => $cityId . '02', 'subdistrict_name' => 'Kecamatan Tengah'],
            ['subdistrict_id' => $cityId . '03', 'subdistrict_name' => 'Kecamatan Barat'],
            ['subdistrict_id' => $cityId . '04', 'subdistrict_name' => 'Kecamatan Timur'],
        ];
    }

    /**
     * Calculate shipping cost.
     */
    public function calculateCost(string $destinationSubdistrictId, int $weightGrams, string $courier): array
    {
        /*
        if ($this->isConfigured()) {
            try {
                $response = Http::withHeaders([
                    'key' => $this->apiKey
                ])->post($this->baseUrl . '/cost', [
                    'origin' => $this->originCityId,
                    'originType' => 'city',
                    'destination' => $destinationSubdistrictId,
                    'destinationType' => 'subdistrict',
                    'weight' => $weightGrams,
                    'courier' => $courier
                ]);

                if ($response->successful()) {
                    return $response->json()['rajaongkir']['results'] ?? [];
                }
                Log::error('RajaOngkir calculateCost error: ' . $response->body());
            } catch (Exception $e) {
                Log::error('RajaOngkir calculateCost exception: ' . $e->getMessage());
            }
        }
        */

        $kg = ceil($weightGrams / 1000);
        $baseRate = 12000;
        
        $destInt = (int)$destinationSubdistrictId;
        $baseRate += ($destInt % 5) * 3000;

        if (strtolower($courier) === 'jne') {
            return [
                [
                    'code' => 'jne',
                    'name' => 'Jalur Nugraha Ekakurir (JNE)',
                    'costs' => [
                        [
                            'service' => 'REG',
                            'description' => 'Layanan Reguler',
                            'cost' => [
                                ['value' => $baseRate * $kg, 'etd' => '2-3 Hari']
                            ]
                        ],
                        [
                            'service' => 'YES',
                            'description' => 'Yakin Esok Sampai',
                            'cost' => [
                                ['value' => ($baseRate + 12000) * $kg, 'etd' => '1 Hari']
                            ]
                        ]
                    ]
                ]
            ];
        } elseif (strtolower($courier) === 'pos') {
            return [
                [
                    'code' => 'pos',
                    'name' => 'POS Indonesia (POS)',
                    'costs' => [
                        [
                            'service' => 'KILAT',
                            'description' => 'Pos Kilat Khusus',
                            'cost' => [
                                ['value' => ($baseRate - 2000) * $kg, 'etd' => '3-4 Hari']
                            ]
                        ]
                    ]
                ]
            ];
        } else {
            return [
                [
                    'code' => 'j&t',
                    'name' => 'J&T Express',
                    'costs' => [
                        [
                            'service' => 'EZ',
                            'description' => 'Layanan EZ',
                            'cost' => [
                                ['value' => ($baseRate - 1000) * $kg, 'etd' => '2-3 Hari']
                            ]
                        ]
                    ]
                ]
            ];
        }
    }

    /**
     * Track package by waybill number.
     */
    public function trackWaybill(string $waybillNumber, string $courier): array
    {
        /*
        if ($this->isConfigured()) {
            try {
                $response = Http::withHeaders([
                    'key' => $this->apiKey
                ])->post($this->baseUrl . '/waybill', [
                    'waybill' => $waybillNumber,
                    'courier' => $courier
                ]);

                if ($response->successful()) {
                    return $response->json()['rajaongkir']['result'] ?? [];
                }
                Log::error('RajaOngkir trackWaybill error: ' . $response->body());
            } catch (Exception $e) {
                Log::error('RajaOngkir trackWaybill exception: ' . $e->getMessage());
            }
        }
        */

        return [
            'delivered' => true,
            'summary' => [
                'waybill_number' => $waybillNumber,
                'courier_name' => strtoupper($courier),
                'status' => 'DELIVERED',
            ],
            'manifest' => [
                [
                    'manifest_description' => 'Paket telah diterima oleh Elvara Customer (Penerima Asli).',
                    'manifest_date' => date('Y-m-d'),
                    'manifest_time' => '14:30',
                    'city_name' => 'Tujuan'
                ],
                [
                    'manifest_description' => 'Paket sedang dibawa oleh kurir menuju alamat tujuan.',
                    'manifest_date' => date('Y-m-d'),
                    'manifest_time' => '08:15',
                    'city_name' => 'Hub Jakarta'
                ],
                [
                    'manifest_description' => 'Paket telah sampai di gudang sortir Jakarta.',
                    'manifest_date' => date('Y-m-d', strtotime('-1 day')),
                    'manifest_time' => '19:40',
                    'city_name' => 'Sortir Jakarta'
                ],
                [
                    'manifest_description' => 'Paket telah diterima di hub asal pengiriman.',
                    'manifest_date' => date('Y-m-d', strtotime('-2 days')),
                    'manifest_time' => '10:00',
                    'city_name' => 'Gudang Pusat'
                ]
            ]
        ];
    }
}
