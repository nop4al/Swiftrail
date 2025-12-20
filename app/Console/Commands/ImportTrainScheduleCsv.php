<?php

namespace App\Console\Commands;

use App\Models\Station;
use App\Models\Train;
use App\Models\TrainStop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportTrainScheduleCsv extends Command
{
    protected $signature = 'schedule:import {path : Path ke file CSV}';
    protected $description = 'Import jadwal kereta dari CSV format blok (train header + Stasiun/Datang/Berangkat)';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (!is_file($path)) {
            $this->error("File tidak ditemukan: {$path}");
            return self::FAILURE;
        }

        $rows = $this->readCsv($path);
        if (count($rows) === 0) {
            $this->error("CSV kosong.");
            return self::FAILURE;
        }

        DB::beginTransaction();
        try {
            $currentTrain = null;
            $inTable = false;
            $sequence = 0;

            foreach ($rows as $r) {
                $col1 = trim((string)($r[0] ?? ''));
                $col2 = trim((string)($r[1] ?? ''));
                $col3 = trim((string)($r[2] ?? ''));

                // baris kosong total => reset
                if ($col1 === '' && $col2 === '' && $col3 === '') {
                    $inTable = false;
                    $sequence = 0;
                    continue;
                }

                // header train: "Nama Kereta - KA1"
                if ($this->looksLikeTrainHeader($col1) && $col2 === '' && $col3 === '') {
                    [$name, $code] = $this->parseTrainHeader($col1);

                    $currentTrain = Train::updateOrCreate(
                        ['code' => $code],
                        [
                            'name' => $name,
                            'type' => 'Express',
                            'capacity' => 500,
                            'status' => 'active'
                        ]
                    );

                    $this->info("Train: {$currentTrain->name} ({$currentTrain->code})");
                    $inTable = false;
                    $sequence = 0;
                    continue;
                }

                // header tabel
                if (strtolower($col1) === 'stasiun') {
                    $inTable = true;
                    $sequence = 0;
                    continue;
                }

                if (!$currentTrain || !$inTable) continue;

                // data stop
                $stationName = $col1;
                $arrival = $this->parseTimeOrNull($col2);
                $departure = $this->parseTimeOrNull($col3);

                // Create or get station with generated code
                $station = Station::firstOrCreate(
                    ['name' => $stationName],
                    [
                        'code' => strtoupper(substr($stationName, 0, 3)),
                        'city' => $this->extractCity($stationName),
                        'active' => true
                    ]
                );

                $sequence++;

                TrainStop::updateOrCreate(
                    [
                        'train_id' => $currentTrain->id,
                        'sequence' => $sequence,
                    ],
                    [
                        'station_id' => $station->id,
                        'arrival_time' => $arrival,
                        'departure_time' => $departure,
                    ]
                );
            }

            DB::commit();
            $this->info("Import selesai.");
            $this->warn("Isi lat/lng di tabel stations (pakai seeder) supaya marker bisa bergerak.");
            return self::SUCCESS;

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error("Gagal import: " . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function readCsv(string $path): array
    {
        $rows = [];
        $fh = fopen($path, 'r');
        while (($data = fgetcsv($fh)) !== false) {
            $rows[] = $data;
        }
        fclose($fh);
        return $rows;
    }

    private function looksLikeTrainHeader(string $s): bool
    {
        return str_contains($s, ' - KA');
    }

    private function parseTrainHeader(string $s): array
    {
        $parts = explode(' - ', $s, 2);
        $name = trim($parts[0] ?? $s);
        $code = trim($parts[1] ?? 'KA?');
        return [$name, $code];
    }

    private function parseTimeOrNull(string $s): ?string
    {
        $s = trim($s);
        if ($s === '' || $s === '-') return null;

        // format CSV: 08.00 -> 08:00
        $s = str_replace('.', ':', $s);

        if (preg_match('/^\d{1,2}:\d{2}$/', $s) !== 1) return null;

        [$hh, $mm] = explode(':', $s, 2);
        $hh = str_pad($hh, 2, '0', STR_PAD_LEFT);

        return "{$hh}:{$mm}:00";
    }

    private function extractCity(string $stationName): string
    {
        $cityMap = [
            'Surabaya' => 'Surabaya',
            'Bojonegoro' => 'Bojonegoro',
            'Semarang' => 'Semarang',
            'Pekalongan' => 'Pekalongan',
            'Cirebon' => 'Cirebon',
            'Gambir' => 'Jakarta',
        ];

        foreach ($cityMap as $key => $city) {
            if (str_contains($stationName, $key)) {
                return $city;
            }
        }

        return trim(explode(' ', $stationName)[0] ?? 'Unknown');
    }
}
