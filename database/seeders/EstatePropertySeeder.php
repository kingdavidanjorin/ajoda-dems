<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PropertyStatus;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseStatusHistory;
use App\Models\HouseType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class EstatePropertySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $estate = Estate::query()->updateOrCreate(
                ['code' => 'AJODA-GRA'],
                [
                    'name' => 'Ajoda GRA',
                    'slug' => 'ajoda-gra',
                    'description' => 'A secure residential development with modern homes and planned infrastructure.',
                    'address' => 'Ajoda New Town',
                    'city' => 'Ibadan',
                    'state' => 'Oyo',
                    'country' => 'Nigeria',
                    'latitude' => null,
                    'longitude' => null,
                    'is_active' => true,
                ],
            );

            $phaseOne = EstatePhase::query()->updateOrCreate(
                [
                    'estate_id' => $estate->id,
                    'code' => 'PHASE-1',
                ],
                [
                    'name' => 'Phase 1',
                    'slug' => 'phase-1',
                    'description' => 'Initial residential development phase.',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            );

            $phaseTwo = EstatePhase::query()->updateOrCreate(
                [
                    'estate_id' => $estate->id,
                    'code' => 'PHASE-2',
                ],
                [
                    'name' => 'Phase 2',
                    'slug' => 'phase-2',
                    'description' => 'Second residential development phase.',
                    'sort_order' => 2,
                    'is_active' => true,
                ],
            );

            $terrace = HouseType::query()->updateOrCreate(
                ['code' => 'TERRACE-3'],
                [
                    'name' => '3 Bedroom Terrace Duplex',
                    'slug' => '3-bedroom-terrace-duplex',
                    'description' => 'A modern three-bedroom terrace duplex designed for comfortable family living.',
                    'bedrooms' => 3,
                    'bathrooms' => 3,
                    'toilets' => 4,
                    'floors' => 2,
                    'plot_size_sqm' => 300,
                    'building_size_sqm' => 210,
                    'base_price' => 65000000,
                    'features' => [
                        'All rooms en-suite',
                        'Fitted kitchen',
                        'Family lounge',
                        'Parking spaces',
                    ],
                    'is_active' => true,
                ],
            );

            $semiDetached = HouseType::query()->updateOrCreate(
                ['code' => 'SEMI-4'],
                [
                    'name' => '4 Bedroom Semi-Detached Duplex',
                    'slug' => '4-bedroom-semi-detached-duplex',
                    'description' => 'A spacious four-bedroom semi-detached duplex with modern finishes.',
                    'bedrooms' => 4,
                    'bathrooms' => 4,
                    'toilets' => 5,
                    'floors' => 2,
                    'plot_size_sqm' => 450,
                    'building_size_sqm' => 320,
                    'base_price' => 95000000,
                    'features' => [
                        'All rooms en-suite',
                        'Fitted kitchen',
                        'Family lounge',
                        'Study',
                        'Parking spaces',
                    ],
                    'is_active' => true,
                ],
            );

            $detached = HouseType::query()->updateOrCreate(
                ['code' => 'DETACHED-5'],
                [
                    'name' => '5 Bedroom Detached Duplex',
                    'slug' => '5-bedroom-detached-duplex',
                    'description' => 'A premium five-bedroom detached duplex with generous indoor and outdoor spaces.',
                    'bedrooms' => 5,
                    'bathrooms' => 5,
                    'toilets' => 6,
                    'floors' => 2,
                    'plot_size_sqm' => 650,
                    'building_size_sqm' => 450,
                    'base_price' => 145000000,
                    'features' => [
                        'All rooms en-suite',
                        'Fitted kitchen',
                        'Multiple lounges',
                        'Study',
                        'Domestic staff quarters',
                        'Parking spaces',
                    ],
                    'is_active' => true,
                ],
            );

            $houses = [
                [
                    'phase' => $phaseOne,
                    'type' => $terrace,
                    'property_code' => 'AJD-P1-001',
                    'name' => 'Terrace Duplex A1',
                    'slug' => 'terrace-duplex-a1',
                    'plot_number' => 'A-01',
                    'house_number' => 'A1',
                    'status' => PropertyStatus::Available,
                    'featured' => true,
                    'x' => 12.5,
                    'y' => 18.0,
                ],
                [
                    'phase' => $phaseOne,
                    'type' => $terrace,
                    'property_code' => 'AJD-P1-002',
                    'name' => 'Terrace Duplex A2',
                    'slug' => 'terrace-duplex-a2',
                    'plot_number' => 'A-02',
                    'house_number' => 'A2',
                    'status' => PropertyStatus::Available,
                    'featured' => true,
                    'x' => 22.5,
                    'y' => 18.0,
                ],
                [
                    'phase' => $phaseOne,
                    'type' => $terrace,
                    'property_code' => 'AJD-P1-003',
                    'name' => 'Terrace Duplex A3',
                    'slug' => 'terrace-duplex-a3',
                    'plot_number' => 'A-03',
                    'house_number' => 'A3',
                    'status' => PropertyStatus::TemporarilyReserved,
                    'featured' => false,
                    'x' => 32.5,
                    'y' => 18.0,
                ],
                [
                    'phase' => $phaseOne,
                    'type' => $semiDetached,
                    'property_code' => 'AJD-P1-004',
                    'name' => 'Semi-Detached Duplex B1',
                    'slug' => 'semi-detached-duplex-b1',
                    'plot_number' => 'B-01',
                    'house_number' => 'B1',
                    'status' => PropertyStatus::Available,
                    'featured' => true,
                    'x' => 15.0,
                    'y' => 42.0,
                ],
                [
                    'phase' => $phaseOne,
                    'type' => $semiDetached,
                    'property_code' => 'AJD-P1-005',
                    'name' => 'Semi-Detached Duplex B2',
                    'slug' => 'semi-detached-duplex-b2',
                    'plot_number' => 'B-02',
                    'house_number' => 'B2',
                    'status' => PropertyStatus::SaleProcessing,
                    'featured' => false,
                    'x' => 30.0,
                    'y' => 42.0,
                ],
                [
                    'phase' => $phaseOne,
                    'type' => $detached,
                    'property_code' => 'AJD-P1-006',
                    'name' => 'Detached Duplex C1',
                    'slug' => 'detached-duplex-c1',
                    'plot_number' => 'C-01',
                    'house_number' => 'C1',
                    'status' => PropertyStatus::Sold,
                    'featured' => false,
                    'x' => 52.0,
                    'y' => 24.0,
                ],
                [
                    'phase' => $phaseTwo,
                    'type' => $terrace,
                    'property_code' => 'AJD-P2-001',
                    'name' => 'Terrace Duplex D1',
                    'slug' => 'terrace-duplex-d1',
                    'plot_number' => 'D-01',
                    'house_number' => 'D1',
                    'status' => PropertyStatus::Available,
                    'featured' => false,
                    'x' => 62.0,
                    'y' => 58.0,
                ],
                [
                    'phase' => $phaseTwo,
                    'type' => $semiDetached,
                    'property_code' => 'AJD-P2-002',
                    'name' => 'Semi-Detached Duplex D2',
                    'slug' => 'semi-detached-duplex-d2',
                    'plot_number' => 'D-02',
                    'house_number' => 'D2',
                    'status' => PropertyStatus::Available,
                    'featured' => true,
                    'x' => 74.0,
                    'y' => 58.0,
                ],
                [
                    'phase' => $phaseTwo,
                    'type' => $detached,
                    'property_code' => 'AJD-P2-003',
                    'name' => 'Detached Duplex E1',
                    'slug' => 'detached-duplex-e1',
                    'plot_number' => 'E-01',
                    'house_number' => 'E1',
                    'status' => PropertyStatus::UnderConstruction,
                    'featured' => true,
                    'x' => 64.0,
                    'y' => 78.0,
                ],
                [
                    'phase' => $phaseTwo,
                    'type' => $detached,
                    'property_code' => 'AJD-P2-004',
                    'name' => 'Detached Duplex E2',
                    'slug' => 'detached-duplex-e2',
                    'plot_number' => 'E-02',
                    'house_number' => 'E2',
                    'status' => PropertyStatus::Available,
                    'featured' => true,
                    'x' => 80.0,
                    'y' => 78.0,
                ],
            ];

            foreach ($houses as $data) {
                $house = House::query()->updateOrCreate(
                    ['property_code' => $data['property_code']],
                    [
                        'estate_id' => $estate->id,
                        'estate_phase_id' => $data['phase']->id,
                        'house_type_id' => $data['type']->id,
                        'name' => $data['name'],
                        'slug' => $data['slug'],
                        'plot_number' => $data['plot_number'],
                        'house_number' => $data['house_number'],
                        'description' => $data['type']->description,
                        'price_override' => null,
                        'status' => $data['status']->value,
                        'is_featured' => $data['featured'],
                        'is_published' => true,
                        'layout_x' => $data['x'],
                        'layout_y' => $data['y'],
                        'reserved_until' => null,
                    ],
                );

                HouseStatusHistory::query()->firstOrCreate(
                    [
                        'house_id' => $house->id,
                        'from_status' => null,
                        'to_status' => $data['status']->value,
                        'reason' => 'Initial demonstration inventory import.',
                    ],
                    [
                        'changed_by' => null,
                        'metadata' => [
                            'source' => 'EstatePropertySeeder',
                        ],
                        'changed_at' => now(),
                    ],
                );
            }
        });
    }
}
