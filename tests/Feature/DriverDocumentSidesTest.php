<?php

namespace Tests\Feature;

use App\Livewire\Conductores\DriverDocuments;
use App\Models\Driver;
use App\Services\LicenseAlertService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DriverDocumentSidesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('rut')->unique();
            $table->string('full_name');
            $table->date('license_expiration_date')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('driver_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->string('name');
            $table->string('kind', 32)->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->string('reverse_file_path')->nullable();
            $table->string('reverse_original_name')->nullable();
            $table->timestamps();
        });

        $this->mock(LicenseAlertService::class, function ($mock) {
            $mock->shouldReceive('refreshDriver')->andReturn([
                'created' => 0,
                'closed' => 0,
                'state' => 'clear',
            ]);
        });
    }

    public function test_una_sola_imagen_alcanza_para_carnet_y_licencia(): void
    {
        Storage::fake('public');
        $driver = $this->driver();

        Livewire::test(DriverDocuments::class, ['driverId' => $driver->id])
            ->set('kind', 'carnet')
            ->assertSee('ambas caras')
            ->assertSee('Reverso, si va en otra imagen')
            ->set('file', UploadedFile::fake()->image('carnet-ambas.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $carnet = $driver->documents()->where('kind', 'carnet')->first();
        $this->assertNull($carnet->reverse_file_path);
        Storage::disk('public')->assertExists($carnet->file_path);

        Livewire::test(DriverDocuments::class, ['driverId' => $driver->id])
            ->set('kind', 'licencia')
            ->set('expiration_date', now()->addYear()->toDateString())
            ->set('reverseFile', UploadedFile::fake()->image('reverso.jpg'))
            ->call('save')
            ->assertHasErrors(['file']);
    }

    public function test_guarda_las_dos_caras_del_carnet_y_de_la_licencia(): void
    {
        Storage::fake('public');
        $driver = $this->driver();
        $vencimiento = now()->addDays(20)->toDateString();

        Livewire::test(DriverDocuments::class, ['driverId' => $driver->id])
            ->set('kind', 'carnet')
            ->set('file', UploadedFile::fake()->image('carnet-anverso.jpg'))
            ->set('reverseFile', UploadedFile::fake()->image('carnet-reverso.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(DriverDocuments::class, ['driverId' => $driver->id])
            ->set('kind', 'licencia')
            ->set('expiration_date', $vencimiento)
            ->set('file', UploadedFile::fake()->image('licencia-anverso.jpg'))
            ->set('reverseFile', UploadedFile::fake()->image('licencia-reverso.jpg'))
            ->call('save')
            ->assertHasNoErrors()
            ->assertSee('Ver anverso')
            ->assertSee('Ver reverso');

        $carnet = $driver->documents()->where('kind', 'carnet')->first();
        $licencia = $driver->documents()->where('kind', 'licencia')->first();

        $this->assertSame('Carnet de identidad', $carnet->name);
        $this->assertSame('carnet-anverso.jpg', $carnet->original_name);
        $this->assertSame('carnet-reverso.jpg', $carnet->reverse_original_name);
        Storage::disk('public')->assertExists($carnet->file_path);
        Storage::disk('public')->assertExists($carnet->reverse_file_path);

        $this->assertSame('Licencia de conducir', $licencia->name);
        $this->assertSame($vencimiento, $licencia->expiration_date->toDateString());
        Storage::disk('public')->assertExists($licencia->file_path);
        Storage::disk('public')->assertExists($licencia->reverse_file_path);
        $this->assertSame($vencimiento, $driver->fresh()->license_expiration_date->toDateString());
    }

    public function test_otro_documento_sigue_siendo_un_solo_archivo(): void
    {
        Storage::fake('public');
        $driver = $this->driver();

        Livewire::test(DriverDocuments::class, ['driverId' => $driver->id])
            ->set('kind', 'otro')
            ->set('name', 'Certificado de antecedentes')
            ->set('file', UploadedFile::fake()->image('antecedentes.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $document = $driver->documents()->first();
        $this->assertSame('otro', $document->kind);
        $this->assertNull($document->reverse_file_path);
        Storage::disk('public')->assertExists($document->file_path);
    }

    private function driver(): Driver
    {
        return Driver::create([
            'rut' => '11.111.111-1',
            'full_name' => 'Juan Pérez',
            'active' => true,
        ]);
    }
}
