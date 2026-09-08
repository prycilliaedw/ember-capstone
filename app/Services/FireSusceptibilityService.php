<?php

namespace App\Services;

final class FireSusceptibilityService
{
    public const PRIOR_LCS = [
        3 => 50.0,   // Formasi Hutan
        5 => 30.0,   // Mangrove
        9 => 65.0,   // Kebun Kayu
        13 => 75.0,  // Tumbuhan Non-Hutan Lainnya
        21 => 55.0,  // Pertanian Lainnya
        24 => 20.0,  // Pemukiman
        25 => 15.0,  // Non-Vegetasi Lainnya
        30 => 15.0,  // Lubang Tambang
        31 => 10.0,  // Tambak
        33 => 5.0,   // Sungai, Danau, Laut
        35 => 65.0,  // Sawit
        40 => 30.0,  // Sawah
        76 => 50.0,  // Hutan Rawa Gambut
    ];

    public const LAND_COVER_NAMES = [
        3 => 'Formasi Hutan',
        5 => 'Mangrove',
        9 => 'Kebun Kayu',
        13 => 'Tumbuhan Non-Hutan Lainnya',
        21 => 'Pertanian Lainnya',
        24 => 'Pemukiman',
        25 => 'Non-Vegetasi Lainnya',
        30 => 'Lubang Tambang',
        31 => 'Tambak',
        33 => 'Sungai, Danau, Laut',
        35 => 'Sawit',
        40 => 'Sawah',
        76 => 'Hutan Rawa Gambut',
    ];

    public function confidenceClass(?float $confidence): ?string
    {
        if ($confidence === null) {
            return null;
        }

        if ($confidence < 30) {
            return 'low';
        }

        if ($confidence < 80) {
            return 'nominal';
        }

        return 'high';
    }

    /**
     * Hybrid land-cover susceptibility used by the current EMBER dataset.
     * The empirical evidence is already normalized to 0-100 before it reaches this service.
     */
    public function hybridLcs(?float $prior, ?float $evidence, float $priorWeight = 0.8): ?float
    {
        if ($prior === null || $evidence === null) {
            return null;
        }

        $priorWeight = max(0.0, min(1.0, $priorWeight));
        $result = ($priorWeight * $prior) + ((1 - $priorWeight) * $evidence);

        return round(max(0.0, min(100.0, $result)), 2);
    }

    /**
     * Sugeno zero-order fuzzy model.
     * Confidence memberships are continuous and land-cover memberships overlap.
     */
    public function fsi(?float $confidence, ?float $lcs): ?float
    {
        if ($confidence === null || $lcs === null) {
            return null;
        }

        $confidence = max(0.0, min(100.0, $confidence));
        $lcs = max(0.0, min(100.0, $lcs));

        $confidenceMemberships = [
            'low' => $this->leftShoulder($confidence, 20.0, 50.0),
            'nominal' => $this->triangle($confidence, 30.0, 55.0, 80.0),
            'high' => $this->rightShoulder($confidence, 50.0, 100.0),
        ];

        $lcsMemberships = [
            'very_low' => $this->leftShoulder($lcs, 0.0, 30.0),
            'low' => $this->triangle($lcs, 15.0, 30.0, 45.0),
            'moderate' => $this->triangle($lcs, 30.0, 50.0, 70.0),
            'high' => $this->triangle($lcs, 55.0, 75.0, 90.0),
            'very_high' => $this->rightShoulder($lcs, 80.0, 100.0),
        ];

        $confidenceValues = [
            'low' => 15.0,
            'nominal' => 55.0,
            'high' => 90.0,
        ];
        $lcsValues = [
            'very_low' => 10.0,
            'low' => 30.0,
            'moderate' => 50.0,
            'high' => 75.0,
            'very_high' => 95.0,
        ];

        $numerator = 0.0;
        $denominator = 0.0;

        foreach ($confidenceMemberships as $confidenceKey => $confidenceMembership) {
            if ($confidenceMembership <= 0) {
                continue;
            }

            foreach ($lcsMemberships as $lcsKey => $lcsMembership) {
                if ($lcsMembership <= 0) {
                    continue;
                }

                $weight = $confidenceMembership * $lcsMembership;
                $consequent = (0.55 * $confidenceValues[$confidenceKey])
                    + (0.45 * $lcsValues[$lcsKey]);

                $numerator += $weight * $consequent;
                $denominator += $weight;
            }
        }

        if ($denominator <= 0) {
            return null;
        }

        return round(max(0.0, min(100.0, $numerator / $denominator)), 2);
    }

    public function fsiClass(?float $fsi): ?string
    {
        if ($fsi === null) {
            return null;
        }

        return match (true) {
            $fsi < 20 => 'Very Low',
            $fsi < 40 => 'Low',
            $fsi < 60 => 'Moderate',
            $fsi < 80 => 'High',
            default => 'Very High',
        };
    }

    public function contextFlag(?float $prior, ?float $evidence): ?string
    {
        if ($prior === null || $evidence === null) {
            return null;
        }

        $gap = abs($evidence - $prior);

        return $gap >= 50 ? 'Review' : 'Consistent';
    }

    private function triangle(float $x, float $a, float $b, float $c): float
    {
        if ($x <= $a || $x >= $c) {
            return $x === $b ? 1.0 : 0.0;
        }

        if ($x < $b) {
            return ($x - $a) / ($b - $a);
        }

        return ($c - $x) / ($c - $b);
    }

    private function leftShoulder(float $x, float $a, float $b): float
    {
        if ($x <= $a) {
            return 1.0;
        }

        if ($x >= $b) {
            return 0.0;
        }

        return ($b - $x) / ($b - $a);
    }

    private function rightShoulder(float $x, float $a, float $b): float
    {
        if ($x <= $a) {
            return 0.0;
        }

        if ($x >= $b) {
            return 1.0;
        }

        return ($x - $a) / ($b - $a);
    }
}
