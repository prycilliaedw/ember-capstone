# EMBER – Hotspot + MapBiomas + FSI integration

This project now supports the hotspot susceptibility fields discussed for EMBER.

## Model snapshot

- NASA MODIS confidence: 0–100; Low <30, Nominal 30–<80, High >=80.
- Hybrid Land Cover Susceptibility (LCS): `0.8 * prior + 0.2 * empirical_evidence`.
- FSI: zero-order Sugeno fuzzy inference using confidence and hybrid LCS, with a 55:45 confidence:LCS consequent weighting.
- FSI classes: Very Low (<20), Low (<40), Moderate (<60), High (<80), Very High (>=80).
- `context_flag=Review` when the absolute gap between prior and empirical evidence is >=50; this is an interpretation flag, not a fire probability.

## Import the prepared 33,209 hotspot dataset

1. Run migrations:

```bash
php artisan migrate
```

2. Import the prepared enriched CSV:

```bash
php artisan ember:import-hotspots /path/to/ember_hotspots_sugeno_55_45.csv --truncate
```

Do not use `--truncate` when you want to append to an existing `titik_lokasi` table.

## UI changes

The interactive map now supports filters for:

- NASA confidence: Low / Nominal / High
- FSI class: Very Low / Low / Moderate / High / Very High / Not assessed
- MapBiomas land cover

Hotspot detail views display the land-cover context, hybrid LCS, FSI score and FSI class.

The FSI is a relative hotspot susceptibility indicator. It is not a statistical probability of fire occurrence.
