<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use App\Models\VehicleCategory;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $categories = VehicleCategory::all()->keyBy("slug");
        
        $vehicles = [
            [
                "license_plate" => "ABCD12",
                "brand" => "Fiat",
                "model" => "Ducato",
                "year" => 2020,
                "category_id" => $categories->get("utilitarios")?->id,
                "fuel_type" => "diesel",
                "status" => "active",
                "current_mileage" => 45000,
                "incorporation_date" => "2020-03-15",
                "purchase_value" => 15000000,
            ],
            [
                "license_plate" => "EFGH34",
                "brand" => "Peugeot",
                "model" => "Partner",
                "year" => 2021,
                "category_id" => $categories->get("utilitarios")?->id,
                "fuel_type" => "diesel",
                "status" => "active",
                "current_mileage" => 32000,
                "incorporation_date" => "2021-05-20",
                "purchase_value" => 12000000,
            ],
            [
                "license_plate" => "IJKL56",
                "brand" => "Renault",
                "model" => "Kangoo",
                "year" => 2019,
                "category_id" => $categories->get("utilitarios")?->id,
                "fuel_type" => "gasoline",
                "status" => "active",
                "current_mileage" => 68000,
                "incorporation_date" => "2019-08-10",
                "purchase_value" => 11000000,
            ],
            [
                "license_plate" => "MNOP78",
                "brand" => "Nissan",
                "model" => "Navara",
                "year" => 2022,
                "category_id" => $categories->get("camionetas")?->id,
                "fuel_type" => "diesel",
                "status" => "active",
                "current_mileage" => 25000,
                "incorporation_date" => "2022-02-14",
                "purchase_value" => 18000000,
            ],
            [
                "license_plate" => "QRST90",
                "brand" => "Ford",
                "model" => "Ranger",
                "year" => 2021,
                "category_id" => $categories->get("camionetas")?->id,
                "fuel_type" => "diesel",
                "status" => "active",
                "current_mileage" => 38000,
                "incorporation_date" => "2021-07-22",
                "purchase_value" => 20000000,
            ],
            [
                "license_plate" => "UVWX12",
                "brand" => "Toyota",
                "model" => "Hilux",
                "year" => 2020,
                "category_id" => $categories->get("camionetas")?->id,
                "fuel_type" => "diesel",
                "status" => "maintenance",
                "current_mileage" => 55000,
                "incorporation_date" => "2020-11-05",
                "purchase_value" => 19500000,
            ],
            [
                "license_plate" => "YZAB34",
                "brand" => "Iveco",
                "model" => "Daily 50C",
                "year" => 2019,
                "category_id" => $categories->get("camiones-grua")?->id,
                "fuel_type" => "diesel",
                "status" => "active",
                "current_mileage" => 72000,
                "current_hours" => 1500,
                "incorporation_date" => "2019-04-18",
                "purchase_value" => 35000000,
            ],
            [
                "license_plate" => "CDEF56",
                "brand" => "Mercedes-Benz",
                "model" => "Atego 1218",
                "year" => 2021,
                "category_id" => $categories->get("camiones-grua")?->id,
                "fuel_type" => "diesel",
                "status" => "active",
                "current_mileage" => 42000,
                "current_hours" => 850,
                "incorporation_date" => "2021-09-12",
                "purchase_value" => 42000000,
            ],
            [
                "license_plate" => "GHIJ78",
                "brand" => "Bobcat",
                "model" => "S570",
                "year" => 2020,
                "category_id" => $categories->get("maquinaria")?->id,
                "fuel_type" => "diesel",
                "status" => "active",
                "current_mileage" => 0,
                "current_hours" => 2800,
                "incorporation_date" => "2020-06-25",
                "purchase_value" => 25000000,
            ],
            [
                "license_plate" => "KLMN90",
                "brand" => "Caterpillar",
                "model" => "302.5",
                "year" => 2019,
                "category_id" => $categories->get("maquinaria")?->id,
                "fuel_type" => "diesel",
                "status" => "active",
                "current_mileage" => 0,
                "current_hours" => 4200,
                "incorporation_date" => "2019-12-03",
                "purchase_value" => 28000000,
            ],
            ["license_plate" => "PQRS12", "brand" => "Fiat", "model" => "Berlingo", "year" => 2022, "category_id" => $categories->get("utilitarios")?->id, "fuel_type" => "diesel", "status" => "active", "current_mileage" => 18000, "incorporation_date" => "2022-04-10", "purchase_value" => 13500000],
            ["license_plate" => "STUV34", "brand" => "Peugeot", "model" => "Boxer", "year" => 2021, "category_id" => $categories->get("utilitarios")?->id, "fuel_type" => "diesel", "status" => "active", "current_mileage" => 41000, "incorporation_date" => "2021-11-08", "purchase_value" => 14200000],
            ["license_plate" => "WXYZ56", "brand" => "Chevrolet", "model" => "N300", "year" => 2020, "category_id" => $categories->get("utilitarios")?->id, "fuel_type" => "gasoline", "status" => "inactive", "current_mileage" => 72000, "incorporation_date" => "2020-01-20", "purchase_value" => 9800000],
            ["license_plate" => "BCDF78", "brand" => "Ford", "model" => "Transit", "year" => 2023, "category_id" => $categories->get("utilitarios")?->id, "fuel_type" => "diesel", "status" => "active", "current_mileage" => 8000, "incorporation_date" => "2023-06-01", "purchase_value" => 16500000],
            ["license_plate" => "GHJK90", "brand" => "Toyota", "model" => "Land Cruiser", "year" => 2021, "category_id" => $categories->get("camionetas")?->id, "fuel_type" => "diesel", "status" => "active", "current_mileage" => 35000, "incorporation_date" => "2021-03-15", "purchase_value" => 32000000],
            ["license_plate" => "LMNP12", "brand" => "Mitsubishi", "model" => "L200", "year" => 2022, "category_id" => $categories->get("camionetas")?->id, "fuel_type" => "diesel", "status" => "active", "current_mileage" => 22000, "incorporation_date" => "2022-08-22", "purchase_value" => 18500000],
            ["license_plate" => "QRST34", "brand" => "Isuzu", "model" => "D-Max", "year" => 2020, "category_id" => $categories->get("camionetas")?->id, "fuel_type" => "diesel", "status" => "maintenance", "current_mileage" => 48000, "incorporation_date" => "2020-09-14", "purchase_value" => 17200000],
            ["license_plate" => "UVWX56", "brand" => "Hyundai", "model" => "Santa Fe", "year" => 2021, "category_id" => $categories->get("camionetas")?->id, "fuel_type" => "diesel", "status" => "active", "current_mileage" => 39000, "incorporation_date" => "2021-12-05", "purchase_value" => 21000000],
            ["license_plate" => "YZAB78", "brand" => "Scania", "model" => "P 310", "year" => 2018, "category_id" => $categories->get("camiones-grua")?->id, "fuel_type" => "diesel", "status" => "active", "current_mileage" => 95000, "current_hours" => 2200, "incorporation_date" => "2018-05-30", "purchase_value" => 38000000],
            ["license_plate" => "CDEF90", "brand" => "Volvo", "model" => "FH 440", "year" => 2020, "category_id" => $categories->get("camiones-grua")?->id, "fuel_type" => "diesel", "status" => "active", "current_mileage" => 62000, "current_hours" => 1100, "incorporation_date" => "2020-07-18", "purchase_value" => 45000000],
            ["license_plate" => "GHIJ12", "brand" => "Bobcat", "model" => "T770", "year" => 2021, "category_id" => $categories->get("maquinaria")?->id, "fuel_type" => "diesel", "status" => "active", "current_mileage" => 0, "current_hours" => 1800, "incorporation_date" => "2021-02-28", "purchase_value" => 32000000],
            ["license_plate" => "KLMN34", "brand" => "JCB", "model" => "3CX", "year" => 2019, "category_id" => $categories->get("maquinaria")?->id, "fuel_type" => "diesel", "status" => "active", "current_mileage" => 0, "current_hours" => 3500, "incorporation_date" => "2019-10-12", "purchase_value" => 26500000],
        ];

        foreach ($vehicles as $vehicleData) {
            Vehicle::withoutGlobalScopes()
                ->updateOrCreate(
                    ["license_plate" => $vehicleData["license_plate"]],
                    array_merge($vehicleData, ['deleted_at' => null])
                );
        }
    }
}
