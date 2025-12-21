<?php
// Simple script to update all schedules to operate every day (no days restriction)

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Schedule;

// Update all active schedules - set days to empty string so they operate every day
$updated = Schedule::where('status', 'active')
    ->update(['days' => '']);

echo "Updated $updated schedules to operate every day (no restriction).\n";

// Show updated schedules
$schedules = Schedule::where('status', 'active')->get();
foreach ($schedules as $schedule) {
    echo "Schedule {$schedule->id}: {$schedule->departure_time} - {$schedule->arrival_time}\n";
}
