<?php

namespace Tests\Feature\Ai;

use App\Services\Ai\AppManual;
use App\Services\Ai\ReadOnlySql;
use Tests\TestCase;

class AssistantSafetyTest extends TestCase
{
    public function test_rechaza_escrituras_y_tablas_internas(): void
    {
        $sql = new ReadOnlySql;

        $this->assertNull($sql->rejectReason('select count(*) from vehicles where deleted_at is null'));
        $this->assertNotNull($sql->rejectReason('delete from vehicles'));
        $this->assertNotNull($sql->rejectReason('select * from users where password is not null'));
        $this->assertNotNull($sql->rejectReason('select id from sessions'));
        $this->assertNotNull($sql->rejectReason('select 1; select 2'));
    }

    public function test_el_manual_responde_como_subir_una_licencia(): void
    {
        $result = app(AppManual::class)->search('cómo subo la licencia de un conductor', null);

        $this->assertTrue($result['ok']);
        $this->assertTrue($result['sin_acceso']);
        $this->assertStringContainsString('Conductores', $result['mensaje']);
        $manual = (string) file_get_contents(resource_path('manual/MANUAL_USUARIO.md'));
        $this->assertStringContainsString('reverso no es obligatorio', $manual);
    }
}
