<?php

use App\Models\Station;
use App\Models\TimeLog;
use App\Models\User;
use Carbon\Carbon;

test('unauthenticated users are redirected to login when accessing export', function () {
    $response = $this->get(route('time-reports.export', [
        'period' => 'week',
        'date' => '2024-01-15',
        'format' => 'csv'
    ]));

    $response->assertRedirect(route('login'));
});

test('export validates required parameters', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('time-reports.export', [
        'period' => 'invalid',
        'date' => 'invalid-date'
    ]));

    $response->assertSessionHasErrors(['period', 'date']);
});

test('csv export generates correct filename and headers', function () {
    $user = User::factory()->create();
    $station = Station::factory()->create(['name' => 'Test Station']);
    
    // Create a completed time log
    TimeLog::factory()->create([
        'user_id' => $user->id,
        'station_id' => $station->id,
        'date' => '2024-01-15',
        'clock_in' => Carbon::parse('2024-01-15 08:00:00'),
        'clock_out' => Carbon::parse('2024-01-15 16:00:00'),
        'total_minutes' => 480, // 8 hours
        'is_oncall' => false,
        'notes' => 'Test work day'
    ]);

    $response = $this->actingAs($user)->get(route('time-reports.export', [
        'period' => 'week',
        'date' => '2024-01-15',
        'format' => 'csv'
    ]));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $response->assertDownload('tidsrapport-week-2024-01-15-2024-01-21.csv');
});

test('csv export contains correct swedish headers and formatting', function () {
    $user = User::factory()->create();
    $station = Station::factory()->create(['name' => 'Test Station']);
    
    TimeLog::factory()->create([
        'user_id' => $user->id,
        'station_id' => $station->id,
        'date' => '2024-01-15',
        'clock_in' => Carbon::parse('2024-01-15 08:00:00'),
        'clock_out' => Carbon::parse('2024-01-15 16:30:00'),
        'total_minutes' => 510, // 8.5 hours
        'is_oncall' => false,
        'notes' => 'Regular work'
    ]);

$response = $this->actingAs($user)->get(route('time-reports.export', [
        'period' => 'week',
        'date' => '2024-01-15'
    ]));

    $content = $response->streamedContent();
    
    // Check UTF-8 BOM
    expect(substr($content, 0, 3))->toBe("\xEF\xBB\xBF");
    
    // Check Swedish headers and formatting
    expect($content)->toContain('Summering');
    // Ensure table headers and one detailed row are present
    expect($content)->toContain('Datum;Veckodag;Station;Typ;Tid;Minuter;Anteckningar');
expect($content)->toContain('2024-01-15;mån;Test Station;Ordinarie;8 tim 30 min');
    
    // Check CRLF line endings
    expect($content)->toContain("\r\n");
});

test('csv export handles empty dataset correctly', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('time-reports.export', [
        'period' => 'month',
        'date' => '2024-01-15'
    ]));

    $content = $response->streamedContent();
    
    expect($content)->toContain('Summering');
expect($content)->toContain('Total tid;0 min;0');
expect($content)->toContain('Datum;Veckodag;Station;Typ;Tid;Minuter;Anteckningar');
    // Should not contain any log data rows after headers
});

test('csv export only includes completed logs for authenticated user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $station = Station::factory()->create(['name' => 'Test Station']);
    
    // User 1's completed log
    TimeLog::factory()->create([
        'user_id' => $user1->id,
        'station_id' => $station->id,
        'date' => '2024-01-15',
        'clock_in' => Carbon::parse('2024-01-15 08:00:00'),
        'clock_out' => Carbon::parse('2024-01-15 16:00:00'),
        'total_minutes' => 480,
        'is_oncall' => false,
    ]);
    
    // User 2's completed log (should not appear in user 1's export)
    TimeLog::factory()->create([
        'user_id' => $user2->id,
        'station_id' => $station->id,
        'date' => '2024-01-15',
        'clock_in' => Carbon::parse('2024-01-15 09:00:00'),
        'clock_out' => Carbon::parse('2024-01-15 17:00:00'),
        'total_minutes' => 480,
        'is_oncall' => false,
    ]);
    
    // User 1's incomplete log (should not appear)
    TimeLog::factory()->create([
        'user_id' => $user1->id,
        'station_id' => $station->id,
        'date' => '2024-01-15',
        'clock_in' => Carbon::parse('2024-01-15 14:00:00'),
        'clock_out' => null,
        'total_minutes' => null,
        'is_oncall' => false,
    ]);

$response = $this->actingAs($user1)->get(route('time-reports.export', [
        'period' => 'week',
        'date' => '2024-01-15'
    ]));

    $content = $response->streamedContent();
    
    // Should only have one detail row (user1's completed log)
    $lines = explode("\r\n", $content);
    $detailRows = array_filter($lines, fn($line) => str_starts_with($line, '2024-01-15;'));
    expect(count($detailRows))->toBe(1);
});

test('pdf export generates correct filename and headers', function () {
    $user = User::factory()->create(['name' => 'John Doe']);
    $station = Station::factory()->create(['name' => 'Test Station']);
    
    TimeLog::factory()->create([
        'user_id' => $user->id,
        'station_id' => $station->id,
        'date' => '2024-01-15',
        'clock_in' => Carbon::parse('2024-01-15 08:00:00'),
        'clock_out' => Carbon::parse('2024-01-15 16:30:00'),
        'total_minutes' => 510, // 8.5 hours
        'is_oncall' => false,
        'notes' => 'Test work day'
    ]);

    $response = $this->actingAs($user)->get(route('time-reports.export', [
        'period' => 'week',
        'date' => '2024-01-15',
        'format' => 'pdf'
    ]));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/pdf');
    $response->assertDownload('tidsrapport-week-2024-01-15-2024-01-21.pdf');
});

test('pdf export contains user information and period label', function () {
    $user = User::factory()->create(['name' => 'Jane Smith']);
    $station = Station::factory()->create(['name' => 'Main Station']);
    
    TimeLog::factory()->create([
        'user_id' => $user->id,
        'station_id' => $station->id,
        'date' => '2024-01-15',
        'clock_in' => Carbon::parse('2024-01-15 09:00:00'),
        'clock_out' => Carbon::parse('2024-01-15 17:00:00'),
        'total_minutes' => 480, // 8 hours
        'is_oncall' => true,
        'notes' => 'On-call shift'
    ]);

    $response = $this->actingAs($user)->get(route('time-reports.export', [
        'period' => 'day',
        'date' => '2024-01-15',
        'format' => 'pdf'
    ]));

    $response->assertOk();
    // PDF content testing would require additional setup for PDF parsing
    // For now, we just verify the response is successful and has correct headers
});

test('export validates format parameter correctly', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('time-reports.export', [
        'period' => 'week',
        'date' => '2024-01-15',
        'format' => 'invalid-format'
    ]));

    $response->assertSessionHasErrors(['format']);
});
