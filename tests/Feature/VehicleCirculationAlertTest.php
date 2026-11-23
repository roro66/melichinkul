<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\Certification;
use App\Models\Vehicle;
use App\Services\VehicleCirculationAlertService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class VehicleCirculationAlertTest extends TestCase
{
    private VehicleCirculationAlertService $documents;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('license_plate')->unique();
            $table->string('brand');
            $table->string('model');
            $table->unsignedSmallInteger('year');
            $table->string('status', 32)->default('active');
            $table->date('incorporation_date');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->string('type', 64);
            $table->string('name');
            $table->date('expiration_date');
            $table->boolean('required')->default(true);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->unsignedBigInteger('maintenance_id')->nullable();
            $table->string('type', 64);
            $table->string('severity', 32)->default('informativa');
            $table->string('title');
            $table->text('message')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status', 32)->default('pending');
            $table->unsignedBigInteger('closed_by_id')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('snoozed_until')->nullable();
            $table->unsignedBigInteger('snoozed_by_id')->nullable();
            $table->text('snoozed_reason')->nullable();
            $table->timestamps();
        });

        $this->documents = app(VehicleCirculationAlertService::class);
    }

    public function test_circulation_documents_alert_and_other_documents_do_not(): void
    {
        $today = Carbon::parse('2026-11-23')->startOfDay();
        $vehicle = $this->vehicle('ABCD-12');
        $this->certification($vehicle, 'permiso_circulacion', $today->copy()->addDays(45));
        $this->certification($vehicle, 'technical_review', $today->copy()->addDays(10));
        $this->certification($vehicle, 'soap', $today->copy()->subDays(2));
        $this->certification($vehicle, 'analisis_gases', $today->copy()->addDays(60));
        $this->certification($vehicle, 'extintor_cabina', $today->copy()->subDay());
        $this->certification($vehicle, 'seguro_adicional', $today->copy()->subDay());

        $this->documents->generate($today);

        $open = Alert::query()->where('status', '!=', 'closed')->orderBy('id')->get();
        $this->assertCount(4, $open);
        $this->assertTrue($open->contains(fn (Alert $alert) => $alert->metadata['document_type'] === 'permiso_circulacion' && $alert->severity === 'advertencia'));
        $this->assertTrue($open->contains(fn (Alert $alert) => $alert->metadata['document_type'] === 'technical_review' && $alert->severity === 'critica' && $alert->type === 'documento_circulacion_por_vencer'));
        $this->assertTrue($open->contains(fn (Alert $alert) => $alert->metadata['document_type'] === 'soap' && $alert->type === 'documento_circulacion_vencido'));
        $this->assertTrue($open->contains(fn (Alert $alert) => $alert->metadata['document_type'] === 'analisis_gases'));
        $this->assertFalse($open->contains(fn (Alert $alert) => ($alert->metadata['document_type'] ?? '') === 'extintor_cabina'));
    }

    public function test_a_later_valid_document_closes_the_expired_one(): void
    {
        $today = Carbon::parse('2026-11-23')->startOfDay();
        $vehicle = $this->vehicle('EFGH-34');
        $this->certification($vehicle, 'permiso_circulacion', $today->copy()->subDays(3));
        $this->documents->refreshVehicle($vehicle, $today);
        $this->assertSame(1, Alert::query()->where('status', '!=', 'closed')->count());

        $this->certification($vehicle, 'permiso_circulacion', $today->copy()->addDays(120));
        $result = $this->documents->refreshVehicle($vehicle, $today, true);

        $this->assertSame('clear', $result['state']);
        $this->assertSame(0, Alert::query()->where('status', '!=', 'closed')->count());
    }

    public function test_a_new_date_still_inside_sixty_days_keeps_the_alert_and_clears_the_snooze(): void
    {
        $today = Carbon::parse('2026-11-23')->startOfDay();
        $vehicle = $this->vehicle('IJKL-56');
        $certification = $this->certification($vehicle, 'soap', $today->copy()->subDay());
        $this->documents->refreshVehicle($vehicle, $today);
        $alert = Alert::query()->where('status', '!=', 'closed')->first();
        $alert->update([
            'snoozed_until' => now()->addDays(3),
            'snoozed_reason' => 'Trámite',
        ]);

        $certification->update(['expiration_date' => $today->copy()->addDays(20)]);
        $this->documents->refreshVehicle($vehicle->fresh(), $today, true);

        $alert->refresh();
        $this->assertSame('pending', $alert->status);
        $this->assertSame('advertencia', $alert->severity);
        $this->assertNull($alert->snoozed_until);
        $this->assertSame('documento_circulacion_por_vencer', $alert->type);
    }

    public function test_an_inactive_vehicle_closes_circulation_alerts(): void
    {
        $today = Carbon::parse('2026-11-23')->startOfDay();
        $vehicle = $this->vehicle('MNOP-78');
        $this->certification($vehicle, 'technical_review', $today->copy()->subDays(5));
        $this->documents->refreshVehicle($vehicle, $today);

        $vehicle->update(['status' => 'inactive']);
        $this->documents->refreshVehicle($vehicle->fresh(), $today);

        $this->assertSame(0, Alert::query()->where('status', '!=', 'closed')->count());
    }

    public function test_the_old_generic_certificate_alert_is_retired(): void
    {
        $today = Carbon::parse('2026-11-23')->startOfDay();
        $vehicle = $this->vehicle('QRST-90');
        $certification = $this->certification($vehicle, 'permiso_circulacion', $today->copy()->subDays(4));
        Alert::create([
            'vehicle_id' => $vehicle->id,
            'type' => 'certificado_vencido',
            'severity' => 'critica',
            'title' => 'Documento vencido: Permiso',
            'generated_at' => now(),
            'due_date' => $certification->expiration_date,
            'status' => 'pending',
            'metadata' => ['key' => 'certification_'.$certification->id, 'certification_id' => $certification->id],
        ]);

        $this->documents->refreshVehicle($vehicle, $today);

        $this->assertSame(0, Alert::query()->where('type', 'certificado_vencido')->where('status', '!=', 'closed')->count());
        $this->assertSame(1, Alert::query()->where('type', 'documento_circulacion_vencido')->where('status', '!=', 'closed')->count());
    }

    public function test_email_cadence_matches_the_license_windows(): void
    {
        $monday = Carbon::parse('2026-11-23')->startOfDay();
        $weekly = $this->alertDueIn(45, $monday);
        $saturday = Carbon::parse('2026-11-28')->startOfDay();
        $daily = $this->alertDueIn(15, $saturday);

        $this->assertSame('weekly', $this->documents->emailTier($weekly, $monday));
        $this->assertTrue($this->documents->shouldEmailToday($weekly, $monday));
        $this->assertFalse($this->documents->shouldEmailToday($weekly, $monday->copy()->addDay()));
        $this->assertSame('daily', $this->documents->emailTier($daily, $saturday));
        $this->assertTrue($this->documents->shouldEmailToday($daily, $saturday));
        $this->assertStringContainsString('documento ya venció', $this->documents->emailCadenceText($daily, $saturday));
    }

    private function vehicle(string $plate): Vehicle
    {
        return Vehicle::create([
            'license_plate' => $plate,
            'brand' => 'Toyota',
            'model' => 'Hilux',
            'year' => 2022,
            'status' => 'active',
            'incorporation_date' => '2022-01-01',
        ]);
    }

    private function certification(Vehicle $vehicle, string $type, Carbon $expiration): Certification
    {
        return Certification::create([
            'vehicle_id' => $vehicle->id,
            'type' => $type,
            'name' => $type,
            'expiration_date' => $expiration->toDateString(),
            'active' => true,
        ]);
    }

    private function alertDueIn(int $days, Carbon $from): Alert
    {
        $alert = new Alert;
        $alert->due_date = $from->copy()->addDays($days);
        $alert->metadata = [];

        return $alert;
    }
}
