<?php

namespace App\Services;

final class FireSusceptibilityService
{
    /**
     * Literature-based prior land-cover susceptibility.
     *
     * Main source:
     * Suprapto et al. (2022), Table 4 and Table 5.
     *
     * Table 5 provides the following land-cover weights:
     *
     * Settlements                       = 0.055
     * Plantation / Rice / Bare land    = 0.203
     * Shrub / Agriculture              = 0.518
     * Secondary Dryland Forest         = 1.000
     *
     * The weights are normalized to 0-100 using min-max
     * normalization against the observed literature range:
     *
     *     normalized =
     *     (weight - 0.055)
     *     / (1.000 - 0.055) * 100
     *
     * Resulting anchors:
     *
     * 0.055 -> 0.00
     * 0.203 -> 15.66
     * 0.518 -> 48.99
     * 1.000 -> 100.00
     *
     * Suprapto et al. also classify land cover into:
     * - Not Vulnerable
     * - Slightly Vulnerable
     * - Moderately Vulnerable
     * - Highly Vulnerable
     *
     * For source categories without a numeric Table 5 value,
     * the lower endpoint of the normalized 0-100 susceptibility
     * scale is used explicitly as the operational baseline.
     *
     * Mapping of EMBER classes to literature categories is
     * documented in the comments below.
     */
    public const PRIOR_LCS = [
        // Formasi Hutan
        // Operational mapping to Not Vulnerable forest category.
        3 => 0.00,

        // Mangrove
        // Suprapto Table 4: Moderately Vulnerable.
        5 => 48.99,

        // Kebun Kayu
        // Plantation Forest -> Slightly Vulnerable.
        9 => 15.66,

        // Tumbuhan Non-Hutan Lainnya
        // Shrub -> Moderately Vulnerable.
        13 => 48.99,

        // Pertanian Lainnya
        // Dryland Agriculture -> Moderately Vulnerable.
        21 => 48.99,

        // Pemukiman
        // Settlements -> weight 0.055 -> 0.00.
        24 => 0.00,

        // Non-Vegetasi Lainnya
        // Bare Land -> Slightly Vulnerable.
        25 => 15.66,

        // Lubang Tambang
        // Mining is not directly represented in Suprapto.
        // Supplemental proxy retained from Ikhsan et al. (2023).
        30 => 11.02,

        // Tambak
        // Operational non-combustible/water proxy.
        31 => 0.00,

        // Sungai, Danau, Laut
        // Water/non-combustible operational proxy.
        33 => 0.00,

        // Sawit
        // Plantation proxy -> Slightly Vulnerable.
        35 => 15.66,

        // Sawah
        // Rice fields -> Slightly Vulnerable.
        40 => 15.66,

        // Hutan Rawa Gambut
        // Swamp Forest -> Not Vulnerable.
        76 => 0.00,
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

    /**
     * Empirical evidence anchors derived from the current
     * EMBER land-cover empirical evidence distribution.
     *
     * Existing 13 land-cover evidence values:
     *
     * 0.00, 23.60, 30.14, 55.19, 56.85, 57.62,
     * 61.79, 62.77, 79.70, 93.60, 94.58, 97.24, 100.00
     *
     * Quartiles:
     *
     * Q1 = 55.19
     * Q2 = 61.79
     * Q3 = 93.60
     *
     * These are calculated from EMBER data, not copied
     * from literature.
     */
    public const EMPIRICAL_Q1 = 55.19;
    public const EMPIRICAL_Q2 = 61.79;
    public const EMPIRICAL_Q3 = 93.60;

    /**
     * MODIS confidence classification is retained for display
     * and descriptive filtering.
     *
     * It is no longer used as a direct fuzzy input.
     */
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
     * Zero-Order Sugeno fuzzy inference.
     *
     * Fuzzy inputs:
     *
     * 1. Prior LCS
     * 2. Empirical hotspot evidence
     *
     * Prior LCS linguistic sets:
     * - Very Low
     * - Low
     * - Moderate
     * - High
     *
     * Empirical evidence linguistic sets:
     * - Very Low
     * - Low
     * - Moderate
     * - High
     *
     * Rule firing:
     *
     *     w_r = mu_prior * mu_evidence
     *
     * Zero-order Sugeno output constants:
     *
     * Very Low  = 10
     * Low       = 30
     * Moderate  = 50
     * High      = 70
     * Very High = 90
     *
     * Final output:
     *
     *     FSI =
     *     SUM(w_r * z_r) / SUM(w_r)
     *
     * The exact 4x4 rule matrix is an EMBER-specific
     * monotonic knowledge adaptation.
     */
    public function fsi(
        ?float $priorLcs,
        ?float $empiricalEvidence
    ): ?float {
        if (
            $priorLcs === null ||
            $empiricalEvidence === null
        ) {
            return null;
        }

        $priorLcs = max(
            0.0,
            min(100.0, $priorLcs)
        );

        $empiricalEvidence = max(
            0.0,
            min(100.0, $empiricalEvidence)
        );

        /*
         * ---------------------------------------------------------
         * PRIOR LCS MEMBERSHIP
         * ---------------------------------------------------------
         *
         * Literature-derived anchors:
         *
         * Not Vulnerable   = 0.00
         * Slightly         = 15.66
         * Moderately       = 48.99
         * Highly           = 100.00
         *
         * Membership sets use these anchors directly so that
         * the numeric boundaries remain traceable to the
         * literature-derived susceptibility scale.
         */

        $priorMemberships = [
            'very_low' => $this->leftShoulder(
                $priorLcs,
                0.0,
                15.66
            ),

            'low' => $this->triangle(
                $priorLcs,
                0.0,
                15.66,
                48.99
            ),

            'moderate' => $this->triangle(
                $priorLcs,
                15.66,
                48.99,
                100.0
            ),

            'high' => $this->rightShoulder(
                $priorLcs,
                48.99,
                100.0
            ),
        ];

        /*
         * ---------------------------------------------------------
         * EMPIRICAL EVIDENCE MEMBERSHIP
         * ---------------------------------------------------------
         *
         * Boundaries are obtained from the empirical evidence
         * distribution using Q1, Q2 and Q3:
         *
         * Q1 = 55.19
         * Q2 = 61.79
         * Q3 = 93.60
         *
         * This is data-derived rather than literature-invented.
         */

        $evidenceMemberships = [
            'very_low' => $this->leftShoulder(
                $empiricalEvidence,
                0.0,
                self::EMPIRICAL_Q1
            ),

            'low' => $this->triangle(
                $empiricalEvidence,
                0.0,
                self::EMPIRICAL_Q1,
                self::EMPIRICAL_Q2
            ),

            'moderate' => $this->triangle(
                $empiricalEvidence,
                self::EMPIRICAL_Q1,
                self::EMPIRICAL_Q2,
                self::EMPIRICAL_Q3
            ),

            'high' => $this->rightShoulder(
                $empiricalEvidence,
                self::EMPIRICAL_Q2,
                self::EMPIRICAL_Q3
            ),
        ];

        /*
         * ---------------------------------------------------------
         * ZERO-ORDER SUGENO OUTPUT
         * ---------------------------------------------------------
         *
         * Five FSI output classes:
         *
         * 0-<20   Very Low  -> 10
         * 20-<40  Low       -> 30
         * 40-<60  Moderate  -> 50
         * 60-<80  High      -> 70
         * 80-100  Very High -> 90
         *
         * The constants are interval midpoints.
         */

        $outputConstants = [
            'very_low' => 10.0,
            'low' => 30.0,
            'moderate' => 50.0,
            'high' => 70.0,
            'very_high' => 90.0,
        ];

        /*
         * ---------------------------------------------------------
         * EMBER RULE BASE
         * ---------------------------------------------------------
         *
         * Prior LCS \ Empirical Evidence
         *
         *                 Very Low   Low       Moderate   High
         *
         * Very Low       Very Low   Low       Moderate   Moderate
         * Low            Low        Low       Moderate   High
         * Moderate       Moderate   Moderate  High        Very High
         * High           Moderate   High      Very High  Very High
         *
         * The matrix is monotonic in both inputs:
         * increasing either input does not decrease the
         * assigned susceptibility class.
         */

        $ruleBase = [
            'very_low' => [
                'very_low' => 'very_low',
                'low' => 'low',
                'moderate' => 'moderate',
                'high' => 'moderate',
            ],

            'low' => [
                'very_low' => 'low',
                'low' => 'low',
                'moderate' => 'moderate',
                'high' => 'high',
            ],

            'moderate' => [
                'very_low' => 'moderate',
                'low' => 'moderate',
                'moderate' => 'high',
                'high' => 'very_high',
            ],

            'high' => [
                'very_low' => 'moderate',
                'low' => 'high',
                'moderate' => 'very_high',
                'high' => 'very_high',
            ],
        ];

        /*
         * ---------------------------------------------------------
         * SUGENO INFERENCE
         * ---------------------------------------------------------
         */

        $numerator = 0.0;
        $denominator = 0.0;

        foreach (
            $priorMemberships as $priorKey => $priorMembership
        ) {
            if ($priorMembership <= 0.0) {
                continue;
            }

            foreach (
                $evidenceMemberships
                as $evidenceKey => $evidenceMembership
            ) {
                if ($evidenceMembership <= 0.0) {
                    continue;
                }

                /*
                 * Product inference:
                 *
                 *     w_r = mu_prior * mu_evidence
                 */
                $weight =
                    $priorMembership
                    * $evidenceMembership;

                $outputClass =
                    $ruleBase[$priorKey][$evidenceKey];

                $consequent =
                    $outputConstants[$outputClass];

                $numerator +=
                    $weight * $consequent;

                $denominator += $weight;
            }
        }

        if ($denominator <= 0.0) {
            return null;
        }

        $result = $numerator / $denominator;

        return round(
            max(0.0, min(100.0, $result)),
            2
        );
    }

    /**
     * Convert numeric FSI to final linguistic class.
     */
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

    /**
     * Compare literature prior against empirical evidence.
     */
    public function contextFlag(
        ?float $prior,
        ?float $evidence
    ): ?string {
        if (
            $prior === null ||
            $evidence === null
        ) {
            return null;
        }

        $gap = abs($evidence - $prior);

        return $gap >= 50
            ? 'Review'
            : 'Consistent';
    }

    /**
     * Triangular membership function.
     */
    private function triangle(
        float $x,
        float $a,
        float $b,
        float $c
    ): float {
        if ($x <= $a || $x >= $c) {
            return $x === $b ? 1.0 : 0.0;
        }

        if ($x < $b) {
            return ($x - $a) / ($b - $a);
        }

        return ($c - $x) / ($c - $b);
    }

    /**
     * Left shoulder membership function.
     */
    private function leftShoulder(
        float $x,
        float $a,
        float $b
    ): float {
        if ($x <= $a) {
            return 1.0;
        }

        if ($x >= $b) {
            return 0.0;
        }

        return ($b - $x) / ($b - $a);
    }

    /**
     * Right shoulder membership function.
     */
    private function rightShoulder(
        float $x,
        float $a,
        float $b
    ): float {
        if ($x <= $a) {
            return 0.0;
        }

        if ($x >= $b) {
            return 1.0;
        }

        return ($x - $a) / ($b - $a);
    }
}