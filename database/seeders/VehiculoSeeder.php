<?php

namespace Database\Seeders;

use App\Models\Vehiculo;
use App\Models\CategoriaVehiculo;
use Illuminate\Database\Seeder;

class VehiculoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = CategoriaVehiculo::all()->keyBy("slug");
        
        $vehiculos = [
            [
                "patente" => "ABCD12",
                "marca" => "Fiat",
                "modelo" => "Ducato",
                "anio" => 2020,
                "categoria_id" => $categorias->get("utilitarios")?->id,
                "tipo_combustible" => "diesel",
                "estado" => "activo",
                "kilometraje_actual" => 45000,
                "fecha_incorporacion" => "2020-03-15",
                "valor_compra" => 15000000,
            ],
            [
                "patente" => "EFGH34",
                "marca" => "Peugeot",
                "modelo" => "Partner",
                "anio" => 2021,
                "categoria_id" => $categorias->get("utilitarios")?->id,
                "tipo_combustible" => "diesel",
                "estado" => "activo",
                "kilometraje_actual" => 32000,
                "fecha_incorporacion" => "2021-05-20",
                "valor_compra" => 12000000,
            ],
            [
                "patente" => "IJKL56",
                "marca" => "Renault",
                "modelo" => "Kangoo",
                "anio" => 2019,
                "categoria_id" => $categorias->get("utilitarios")?->id,
                "tipo_combustible" => "gasolina",
                "estado" => "activo",
                "kilometraje_actual" => 68000,
                "fecha_incorporacion" => "2019-08-10",
                "valor_compra" => 11000000,
            ],
            [
                "patente" => "MNOP78",
                "marca" => "Nissan",
                "modelo" => "Navara",
                "anio" => 2022,
                "categoria_id" => $categorias->get("camionetas")?->id,
                "tipo_combustible" => "diesel",
                "estado" => "activo",
                "kilometraje_actual" => 25000,
                "fecha_incorporacion" => "2022-02-14",
                "valor_compra" => 18000000,
            ],
            [
                "patente" => "QRST90",
                "marca" => "Ford",
                "modelo" => "Ranger",
                "anio" => 2021,
                "categoria_id" => $categorias->get("camionetas")?->id,
                "tipo_combustible" => "diesel",
                "estado" => "activo",
                "kilometraje_actual" => 38000,
                "fecha_incorporacion" => "2021-07-22",
                "valor_compra" => 20000000,
            ],
            [
                "patente" => "UVWX12",
                "marca" => "Toyota",
                "modelo" => "Hilux",
                "anio" => 2020,
                "categoria_id" => $categorias->get("camionetas")?->id,
                "tipo_combustible" => "diesel",
                "estado" => "mantenimiento",
                "kilometraje_actual" => 55000,
                "fecha_incorporacion" => "2020-11-05",
                "valor_compra" => 19500000,
            ],
            [
                "patente" => "YZAB34",
                "marca" => "Iveco",
                "modelo" => "Daily 50C",
                "anio" => 2019,
                "categoria_id" => $categorias->get("camiones-grua")?->id,
                "tipo_combustible" => "diesel",
                "estado" => "activo",
                "kilometraje_actual" => 72000,
                "horometro_actual" => 1500,
                "fecha_incorporacion" => "2019-04-18",
                "valor_compra" => 35000000,
            ],
            [
                "patente" => "CDEF56",
                "marca" => "Mercedes-Benz",
                "modelo" => "Atego 1218",
                "anio" => 2021,
                "categoria_id" => $categorias->get("camiones-grua")?->id,
                "tipo_combustible" => "diesel",
                "estado" => "activo",
                "kilometraje_actual" => 42000,
                "horometro_actual" => 850,
                "fecha_incorporacion" => "2021-09-12",
                "valor_compra" => 42000000,
            ],
            [
                "patente" => "GHIJ78",
                "marca" => "Bobcat",
                "modelo" => "S570",
                "anio" => 2020,
                "categoria_id" => $categorias->get("maquinaria")?->id,
                "tipo_combustible" => "diesel",
                "estado" => "activo",
                "kilometraje_actual" => 0,
                "horometro_actual" => 2800,
                "fecha_incorporacion" => "2020-06-25",
                "valor_compra" => 25000000,
            ],
            [
                "patente" => "KLMN90",
                "marca" => "Caterpillar",
                "modelo" => "302.5",
                "anio" => 2019,
                "categoria_id" => $categorias->get("maquinaria")?->id,
                "tipo_combustible" => "diesel",
                "estado" => "activo",
                "kilometraje_actual" => 0,
                "horometro_actual" => 4200,
                "fecha_incorporacion" => "2019-12-03",
                "valor_compra" => 28000000,
            ],
        ];

        foreach ($vehiculos as $vehiculo) {
            Vehiculo::firstOrCreate(
                ["patente" => $vehiculo["patente"]],
                $vehiculo
            );
        }
    }
}
