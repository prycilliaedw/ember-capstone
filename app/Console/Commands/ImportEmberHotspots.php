<?php

namespace App\Console\Commands;

use App\Services\FireSusceptibilityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportEmberHotspots extends Command
{
    protected $signature = 'ember:import-hotspots
        {path : Path to the enriched EMBER hotspot CSV}
        {--truncate : Empty titik_lokasi before importing}
        {--chunk=1000 : Insert batch size}';

    protected $description = 'Import NASA/MapBiomas-enriched EMBER hotspots with FSI fields.';

    public function handle(FireSusceptibilityService $susceptibility): int
    {
        $path = $this->argument('path');

        if (! is_file($path) || ! is_readable($path)) {
            $this->error("CSV tidak dapat dibaca: {$path}");
            return self::FAILURE;
        }

        if ($this->option('truncate')) {
            DB::table('titik_lokasi')->truncate();
            $this->warn('titik_lokasi dikosongkan sebelum impor.');
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Gagal membuka CSV.');
        }

        try {
            $header = fgetcsv($handle, escape: '');
            if (! is_array($header)) {
                throw new RuntimeException('CSV kosong.');
            }

            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', trim((string) $header[0])) ?? trim((string) $header[0]);
            $header = array_map(static fn ($value) => trim((string) $value), $header);

            $required = ['LATITUDE', 'LONGITUDE', 'ACQ_DATE', 'ACQ_TIME', 'SATELLITE', 'INSTRUMENT', 'CONFIDENCE', 'DAYNIGHT', 'LEVEL_3', 'LEVEL_4', 'LEVEL_5', 'LEVEL_6', 'LAND_COVER_ID', 'LAND_COVER', 'prior', 'emp_conf_ev', 'LCS_80_20', 'FSI_SUGENO_55_45', 'FSI_CLASS_SUGENO'];
            foreach ($required as $column) {
                if (! in_array($column, $header, true)) {
                    throw new RuntimeException("Kolom wajib tidak ditemukan: {$column}");
                }
            }

            $index = array_flip($header);
            $batch = [];
            $processed = 0;
            $inserted = 0;
            $chunkSize = max(100, (int) $this->option('chunk'));

            while (($row = fgetcsv($handle, separator: ',', escape: '')) !== false) {
                if (count(array_filter($row, static fn ($value) => trim((string) $value) !== '')) === 0) {
                    continue;
                }

                $processed++;
                $confidence = is_numeric($row[$index['CONFIDENCE']] ?? null)
                    ? (float) $row[$index['CONFIDENCE']]
                    : null;

                $fsi = is_numeric($row[$index['FSI_SUGENO_55_45']] ?? null)
                    ? (float) $row[$index['FSI_SUGENO_55_45']]
                    : null;

                $date = $this->parseDate($row[$index['ACQ_DATE']] ?? null);
                $daynight = strtoupper(trim((string) ($row[$index['DAYNIGHT']] ?? '')));

                $batch[] = [
                    'provinsi' => $row[$index['LEVEL_3']] ?: null,
                    'kabupaten_kota' => $row[$index['LEVEL_4']] ?: null,
                    'kecamatan' => $row[$index['LEVEL_5']] ?: null,
                    'desa' => $row[$index['LEVEL_6']] ?: null,
                    'latitude' => $this->number($row[$index['LATITUDE']] ?? null),
                    'longitude' => $this->number($row[$index['LONGITUDE']] ?? null),
                    'date' => $date,
                    'confidence' => $confidence,
                    'satellite' => $row[$index['SATELLITE']] ?: null,
                    'instrument' => $row[$index['INSTRUMENT']] ?: null,
                    'daynight' => $daynight !== '' ? substr($daynight, 0, 1) : null,
                    'acq_time' => is_numeric($row[$index['ACQ_TIME']] ?? null) ? (int) $row[$index['ACQ_TIME']] : null,
                    'land_cover_id' => is_numeric($row[$index['LAND_COVER_ID']] ?? null) ? (int) $row[$index['LAND_COVER_ID']] : null,
                    'land_cover' => $row[$index['LAND_COVER']] ?: null,
                    'prior_lcs' => $this->number($row[$index['prior']] ?? null),
                    'empirical_evidence' => $this->number($row[$index['emp_conf_ev']] ?? null),
                    'hybrid_lcs' => $this->number($row[$index['LCS_80_20']] ?? null),
                    'fsi_score' => $fsi,
                    'fsi_class' => $row[$index['FSI_CLASS_SUGENO']] ?: ($susceptibility->fsiClass($fsi)),
                    'context_flag' => $susceptibility->contextFlag(
                        $this->number($row[$index['prior']] ?? null),
                        $this->number($row[$index['emp_conf_ev']] ?? null),
                    ),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($batch) >= $chunkSize) {
                    DB::table('titik_lokasi')->insert($batch);
                    $inserted += count($batch);
                    $batch = [];
                    $this->line("Imported {$inserted} rows...");
                }
            }

            if ($batch !== []) {
                DB::table('titik_lokasi')->insert($batch);
                $inserted += count($batch);
            }
        } finally {
            fclose($handle);
        }

        $this->info("Selesai: {$inserted} hotspot berhasil diimpor dari {$processed} baris.");

        return self::SUCCESS;
    }

    private function number(?string $value): ?float
    {
        if ($value === null || trim($value) === '' || ! is_numeric(trim($value))) {
            return null;
        }

        return (float) trim($value);
    }

    private function parseDate(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^(\\d{4})[\\/-](\\d{1,2})[\\/-](\\d{1,2})$/', $value, $matches) === 1) {
            return sprintf('%04d-%02d-%02d', $matches[1], $matches[2], $matches[3]);
        }

        throw new RuntimeException("Format tanggal tidak dikenali: {$value}");
    }
}
