<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Services\LicenseAlertService;
use Carbon\Carbon;
use Tests\TestCase;

class LicenseAlertEmailCadenceTest extends TestCase
{
    private LicenseAlertService $licenses;

    protected function setUp(): void
    {
        parent::setUp();
        $this->licenses = app(LicenseAlertService::class);
    }

    public function test_weekly_email_goes_out_on_monday_between_31_and_60_days(): void
    {
        $monday = Carbon::parse('2026-11-23')->startOfDay();
        $alert = $this->alertDueIn(45, $monday);

        $this->assertSame('weekly', $this->licenses->emailTier($alert, $monday));
        $this->assertTrue($this->licenses->shouldEmailToday($alert, $monday));
        $this->assertFalse($this->licenses->shouldEmailToday($alert, $monday->copy()->addDay()));
    }

    public function test_thrice_weekly_email_goes_out_monday_wednesday_and_friday(): void
    {
        $monday = Carbon::parse('2026-11-23')->startOfDay();
        $alert = $this->alertDueIn(25, $monday);

        $this->assertSame('thrice', $this->licenses->emailTier($alert, $monday));
        $this->assertTrue($this->licenses->shouldEmailToday($alert, $monday));
        $this->assertFalse($this->licenses->shouldEmailToday($alert, $monday->copy()->addDay()));
        $this->assertTrue($this->licenses->shouldEmailToday($alert, $monday->copy()->addDays(2)));
        $this->assertFalse($this->licenses->shouldEmailToday($alert, $monday->copy()->addDays(3)));
        $this->assertTrue($this->licenses->shouldEmailToday($alert, $monday->copy()->addDays(4)));
        $this->assertFalse($this->licenses->shouldEmailToday($alert, $monday->copy()->addDays(5)));
    }

    public function test_daily_email_includes_day_15_weekends_and_expired_licenses(): void
    {
        $saturday = Carbon::parse('2026-11-28')->startOfDay();
        $withinFifteen = $this->alertDueIn(15, $saturday);
        $expired = $this->alertDueIn(-2, $saturday);

        $this->assertSame('daily', $this->licenses->emailTier($withinFifteen, $saturday));
        $this->assertTrue($this->licenses->shouldEmailToday($withinFifteen, $saturday));
        $this->assertSame('daily', $this->licenses->emailTier($expired, $saturday));
        $this->assertTrue($this->licenses->shouldEmailToday($expired, $saturday));
    }

    public function test_boundaries_split_weekly_thrice_and_daily(): void
    {
        $monday = Carbon::parse('2026-11-23')->startOfDay();

        $this->assertNull($this->licenses->emailTier($this->alertDueIn(61, $monday), $monday));
        $this->assertSame('weekly', $this->licenses->emailTier($this->alertDueIn(60, $monday), $monday));
        $this->assertSame('weekly', $this->licenses->emailTier($this->alertDueIn(31, $monday), $monday));
        $this->assertSame('thrice', $this->licenses->emailTier($this->alertDueIn(30, $monday), $monday));
        $this->assertSame('thrice', $this->licenses->emailTier($this->alertDueIn(16, $monday), $monday));
        $this->assertSame('daily', $this->licenses->emailTier($this->alertDueIn(15, $monday), $monday));
    }

    public function test_the_same_license_is_not_emailed_twice_on_the_same_day(): void
    {
        $monday = Carbon::parse('2026-11-23')->startOfDay();
        $alert = $this->alertDueIn(10, $monday);
        $alert->metadata = ['last_email_on' => '2026-11-23'];

        $this->assertFalse($this->licenses->shouldEmailToday($alert, $monday));
    }

    private function alertDueIn(int $days, Carbon $from): Alert
    {
        $alert = new Alert();
        $alert->due_date = $from->copy()->addDays($days);
        $alert->metadata = [];

        return $alert;
    }
}
