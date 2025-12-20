<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Train;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TrackingLiveController extends Controller
{
    public function show(Request $request, string $train_code)
    {
        $tz = config('app.timezone', 'Asia/Jakarta');
        $now = Carbon::now($tz);

        $train = Train::where('code', $train_code)->first();
        if (!$train) return response()->json(['message' => 'Train not found'], 404);

        $stops = $train->stops()->with('station')->get();
        if ($stops->count() < 1) return response()->json(['message' => 'No stops for this train'], 422);

        $baseDate = $now->copy()->startOfDay();
        $timeline = [];
        $dayOffset = 0;
        $prevClock = null;

        foreach ($stops as $s) {
            $arr = $this->timeToDate($baseDate, $s->arrival_time, $dayOffset);
            $dep = $this->timeToDate($baseDate, $s->departure_time, $dayOffset);

            $clock = $s->departure_time ?? $s->arrival_time;
            if ($clock) {
                $clockMin = $this->clockToMinutes($clock);
                if ($prevClock !== null && $clockMin < $prevClock) {
                    $dayOffset++;
                    $arr = $this->timeToDate($baseDate, $s->arrival_time, $dayOffset);
                    $dep = $this->timeToDate($baseDate, $s->departure_time, $dayOffset);
                }
                $prevClock = $clockMin;
            }

            $timeline[] = ['stop' => $s, 'arrival_dt' => $arr, 'departure_dt' => $dep];
        }

        $firstDepart = $timeline[0]['departure_dt'] ?? $timeline[0]['arrival_dt'];
        $lastArrive = $timeline[count($timeline)-1]['arrival_dt'] ?? $timeline[count($timeline)-1]['departure_dt'];

        if ($firstDepart && $now->lt($firstDepart)) {
            return $this->responseAtStation($train, $timeline[0]['stop']->station, $now, 'NOT_STARTED', 0.0);
        }
        if ($lastArrive && $now->gt($lastArrive)) {
            return $this->responseAtStation($train, $timeline[count($timeline)-1]['stop']->station, $now, 'ARRIVED', 1.0);
        }

        foreach ($timeline as $t) {
            $arr = $t['arrival_dt']; $dep = $t['departure_dt'];
            if ($arr && $dep && $now->betweenIncluded($arr, $dep)) {
                return $this->responseAtStation($train, $t['stop']->station, $now, 'AT_STATION', $this->overallProgress($timeline, $now));
            }
        }

        for ($i = 0; $i < count($timeline) - 1; $i++) {
            $from = $timeline[$i];
            $to = $timeline[$i + 1];

            $t0 = $from['departure_dt'] ?? $from['arrival_dt'];
            $t1 = $to['arrival_dt'] ?? $to['departure_dt'];
            if (!$t0 || !$t1) continue;

            if ($now->betweenIncluded($t0, $t1)) {
                $p = ($now->timestamp - $t0->timestamp) / max(1, ($t1->timestamp - $t0->timestamp));
                $p = max(0, min(1, $p));

                $fromSt = $from['stop']->station;
                $toSt = $to['stop']->station;

                $lat = null; $lng = null;
                if ($fromSt->lat !== null && $fromSt->lng !== null && $toSt->lat !== null && $toSt->lng !== null) {
                    $lat = (float)$fromSt->lat + ((float)$toSt->lat - (float)$fromSt->lat) * $p;
                    $lng = (float)$fromSt->lng + ((float)$toSt->lng - (float)$fromSt->lng) * $p;
                }

                return response()->json([
                    'train' => ['code' => $train->code, 'name' => $train->name],
                    'status' => 'ON_ROUTE',
                    'updated_at' => $now->toIso8601String(),
                    'from_station' => $fromSt->name,
                    'to_station' => $toSt->name,
                    'segment_progress' => $p,
                    'overall_progress' => $this->overallProgress($timeline, $now),
                    'lat' => $lat,
                    'lng' => $lng,
                    'note' => ($lat === null ? 'Koordinat stasiun belum lengkap (isi stations.lat/lng)' : null),
                ]);
            }
        }

        return response()->json([
            'train' => ['code' => $train->code, 'name' => $train->name],
            'status' => 'UNKNOWN',
            'updated_at' => $now->toIso8601String(),
            'overall_progress' => $this->overallProgress($timeline, $now),
            'note' => 'Tidak menemukan segmen aktif (cek data jadwal).',
        ]);
    }

    private function responseAtStation($train, $station, Carbon $now, string $status, float $overallProgress)
    {
        return response()->json([
            'train' => ['code' => $train->code, 'name' => $train->name],
            'status' => $status,
            'updated_at' => $now->toIso8601String(),
            'station' => $station->name,
            'overall_progress' => $overallProgress,
            'lat' => $station->latitude !== null ? (float)$station->latitude : null,
            'lng' => $station->longitude !== null ? (float)$station->longitude : null,
            'note' => (($station->latitude === null || $station->longitude === null) ? 'Koordinat stasiun belum lengkap (isi stations.latitude/longitude)' : null),
        ]);
    }

    private function timeToDate(Carbon $baseDate, $time, int $dayOffset): ?Carbon
    {
        if (!$time) return null;
        return Carbon::parse($baseDate->toDateString() . ' ' . $time, $baseDate->getTimezone())->addDays($dayOffset);
    }

    private function clockToMinutes(string $time): int
    {
        [$h, $m] = explode(':', $time, 3);
        return ((int)$h) * 60 + (int)$m;
    }

    private function overallProgress(array $timeline, Carbon $now): float
    {
        $firstDepart = $timeline[0]['departure_dt'] ?? $timeline[0]['arrival_dt'];
        $lastArrive = $timeline[count($timeline)-1]['arrival_dt'] ?? $timeline[count($timeline)-1]['departure_dt'];
        if (!$firstDepart || !$lastArrive) return 0.0;

        $total = max(1, $lastArrive->timestamp - $firstDepart->timestamp);
        $cur = $now->timestamp - $firstDepart->timestamp;

        return max(0, min(1, $cur / $total));
    }
}
