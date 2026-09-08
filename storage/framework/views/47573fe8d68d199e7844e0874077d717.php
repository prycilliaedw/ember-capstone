<?php $__env->startSection('title', ($language === 'en' ? 'Interactive Map' : 'Peta Interaktif') . ' - EMBER'); ?>
<?php $__env->startSection('hideFooter', true); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $availableYears = $locations
            ->pluck('date')
            ->filter()
            ->map(fn ($date) => (int) substr($date, 0, 4))
            ->unique()
            ->sort()
            ->values();
    ?>

    <section class="relative h-[calc(100vh-6.5rem)] min-h-[600px] w-full overflow-hidden bg-slate-100">
        <div class="absolute left-16 top-3 z-[500] max-w-[calc(100%-5rem)] bg-white/95 px-4 py-3 shadow-xl ring-1 ring-slate-200 backdrop-blur sm:left-20 sm:top-5 sm:max-w-[calc(100%-6.25rem)] sm:px-5 sm:py-4">
            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-red-600"><?php echo e($language === 'en' ? 'Interactive map' : 'Peta interaktif'); ?></p>
            <h1 class="mt-1 text-lg font-bold tracking-tight text-slate-950 sm:text-xl"><?php echo e($language === 'en' ? 'EMBER location distribution' : 'Persebaran lokasi EMBER'); ?></h1>
        </div>

        <div id="map-drilldown-control" class="absolute right-3 top-24 z-[500] w-[min(330px,calc(100%-1.5rem))] bg-white/95 p-4 shadow-xl ring-1 ring-slate-200 backdrop-blur sm:right-5 sm:top-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p id="map-boundary-level" class="text-[10px] font-bold uppercase tracking-[0.16em] text-red-600">Provinsi</p>
                    <p id="map-boundary-breadcrumb" class="mt-1 text-sm font-bold text-slate-950">Sumatera</p>
                    <p id="map-boundary-instruction" class="mt-1 text-xs leading-5 text-slate-500">Klik provinsi untuk melihat kabupaten/kota.</p>
                </div>
                <button id="map-boundary-reset" type="button" class="hidden shrink-0 border border-slate-300 px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-red-300 hover:bg-red-50 hover:text-red-700">Reset</button>
            </div>
        </div>

        <section id="map-statistics-panel" class="absolute right-3 top-56 z-[500] max-h-[calc(100%-15rem)] w-[min(330px,calc(100%-1.5rem))] overflow-y-auto bg-white/95 p-4 shadow-xl ring-1 ring-slate-200 backdrop-blur sm:right-5 sm:top-40 sm:max-h-[calc(100%-11rem)]" aria-label="<?php echo e($language === 'en' ? 'Map statistics' : 'Statistik peta'); ?>">
            <div class="flex items-start justify-between gap-3 border-b border-slate-200 pb-3">
                <div>
                    <p class="text-[9px] font-black uppercase tracking-[0.18em] text-red-700"><?php echo e($language === 'en' ? 'Status statistics' : 'Statistik status'); ?></p>
                    <h2 id="map-statistics-region" class="mt-1 text-base font-black text-slate-950">Sumatera</h2>
                </div>
                <span id="map-statistics-period" class="bg-slate-100 px-2 py-1 text-[9px] font-black text-slate-500"><?php echo e($language === 'en' ? 'All years' : 'Semua tahun'); ?></span>
            </div>

            <div class="grid grid-cols-[112px_1fr] items-center gap-4 py-4">
                <div class="relative mx-auto size-28">
                    <div id="map-yearly-donut" class="absolute inset-0 rounded-full bg-slate-200" role="img" aria-label="<?php echo e($language === 'en' ? 'Yearly status donut chart' : 'Grafik donat status tahunan'); ?>"></div>
                    <div class="absolute inset-[24%] flex flex-col items-center justify-center rounded-full bg-white shadow-inner">
                        <strong id="map-yearly-total" class="text-xl font-black text-slate-950">0</strong>
                        <span class="text-[8px] font-black uppercase tracking-wider text-slate-400"><?php echo e($language === 'en' ? 'locations' : 'lokasi'); ?></span>
                    </div>
                </div>

                <div class="space-y-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
                        ['high', '#b91c1c', $language === 'en' ? 'High' : 'Tinggi'],
                        ['nominal', '#d97706', 'Nominal'],
                        ['low', '#047857', $language === 'en' ? 'Low' : 'Rendah'],
                        ['unrated', '#334155', $language === 'en' ? 'Unrated' : 'Belum dinilai'],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$key, $color, $label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="flex items-center gap-2 text-[10px]">
                            <span class="size-2 shrink-0 rounded-full" style="background: <?php echo e($color); ?>"></span>
                            <span class="min-w-0 flex-1 truncate font-bold text-slate-600"><?php echo e($label); ?></span>
                            <strong data-map-yearly-count="<?php echo e($key); ?>" class="font-black text-slate-950">0</strong>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>

            <div id="map-monthly-statistics" class="hidden border-t border-slate-200 pt-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-[0.16em] text-slate-400"><?php echo e($language === 'en' ? 'Monthly status' : 'Status per bulan'); ?></p>
                        <p id="map-monthly-title" class="mt-1 text-xs font-black text-slate-800">-</p>
                    </div>
                    <span id="map-monthly-total" class="text-xs font-black text-red-700">0</span>
                </div>
                <div class="mt-4 grid h-28 grid-cols-12 items-end gap-1 border-b border-slate-200 px-1" aria-label="<?php echo e($language === 'en' ? 'Monthly status bar chart' : 'Grafik batang status bulanan'); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($language === 'en'
                        ? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
                        : ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="flex h-full min-w-0 flex-col justify-end" title="<?php echo e($month); ?>">
                            <div data-map-month-bar="<?php echo e($index); ?>" class="flex min-h-0 w-full flex-col-reverse overflow-hidden bg-slate-100"></div>
                            <span class="mt-1 block truncate text-center text-[7px] font-bold text-slate-400"><?php echo e($month); ?></span>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>
        </section>

        <div id="map-status-filter" class="absolute bottom-28 left-3 z-[500] w-[min(260px,calc(100%-1.5rem))] rounded-2xl border border-slate-200 bg-white/95 p-3 shadow-xl backdrop-blur sm:bottom-32 sm:left-5">
            <div class="mb-2.5 flex items-center justify-between gap-3">
                <p class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500"><?php echo e($language === 'en' ? 'Hotspot filters' : 'Filter hotspot'); ?></p>
                <button type="button" data-map-filters-reset class="text-[10px] font-black text-red-600 transition hover:text-red-500"><?php echo e($language === 'en' ? 'Reset' : 'Reset'); ?></button>
            </div>

            <p class="mb-2 text-[9px] font-black uppercase tracking-[0.14em] text-slate-400"><?php echo e($language === 'en' ? 'NASA confidence' : 'Confidence NASA'); ?></p>
            <div class="grid grid-cols-3 gap-2 text-[11px] font-bold text-slate-600">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
                    ['low', 'bg-emerald-500', $language === 'en' ? 'Low' : 'Rendah'],
                    ['nominal', 'bg-amber-400', 'Nominal'],
                    ['high', 'bg-red-500', $language === 'en' ? 'High' : 'Tinggi'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$key, $color, $label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <button type="button" data-map-confidence="<?php echo e($key); ?>" aria-pressed="true" class="map-status-option flex min-w-0 items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-2 py-2.5 text-center transition hover:border-slate-300 hover:bg-slate-50">
                        <span class="size-2 shrink-0 rounded-full <?php echo e($color); ?>"></span>
                        <span class="truncate"><?php echo e($label); ?></span>
                    </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>

            <p class="mb-2 mt-3 text-[9px] font-black uppercase tracking-[0.14em] text-slate-400"><?php echo e($language === 'en' ? 'Fire susceptibility' : 'Kerawanan hotspot'); ?></p>
            <div class="grid grid-cols-2 gap-2 text-[11px] font-bold text-slate-600">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
                    ['very_low', $language === 'en' ? 'Very Low' : 'Sangat Rendah'],
                    ['low', $language === 'en' ? 'Low' : 'Rendah'],
                    ['moderate', $language === 'en' ? 'Moderate' : 'Sedang'],
                    ['high', $language === 'en' ? 'High' : 'Tinggi'],
                    ['very_high', $language === 'en' ? 'Very High' : 'Sangat Tinggi'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$key, $label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <button type="button" data-map-fsi="<?php echo e($key); ?>" aria-pressed="true" class="map-status-option flex min-w-0 items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-left transition hover:border-slate-300 hover:bg-slate-50">
                        <span class="truncate"><?php echo e($label); ?></span>
                        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" class="size-3.5 shrink-0 text-red-600" aria-hidden="true"><path d="m4 10 4 4 8-8"/></svg>
                    </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>

            <label class="mt-3 block">
                <span class="mb-2 block text-[9px] font-black uppercase tracking-[0.14em] text-slate-400"><?php echo e($language === 'en' ? 'Land cover' : 'Tutupan lahan'); ?></span>
                <select id="map-land-cover-filter" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs font-bold text-slate-700 outline-none focus:border-red-400 focus:ring-4 focus:ring-red-100">
                    <option value="all"><?php echo e($language === 'en' ? 'All land covers' : 'Semua tutupan lahan'); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $locations->pluck('land_cover')->filter()->unique()->sort()->values(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $landCover): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($landCover); ?>"><?php echo e($landCover); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </label>
        </div>

        <div id="map-year-filter" class="absolute bottom-7 left-3 z-[550] w-[min(420px,calc(100%-1.5rem))] sm:bottom-8 sm:left-5">
            <div class="map-year-slider-control" style="--slider-progress: calc(100% - 1.125rem)">
                <span class="sr-only"><?php echo e($language === 'en' ? 'Filter locations by year' : 'Filter lokasi berdasarkan tahun'); ?></span>
                <div class="map-year-slider-track" aria-hidden="true">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $availableYears; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $year): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <span class="map-year-slider-mark" data-year-mark="<?php echo e($year); ?>" data-active="false">
                            <span class="map-year-slider-mark-label"><?php echo e($year); ?></span>
                            <span class="map-year-slider-dot"></span>
                        </span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <span class="map-year-slider-mark" data-year-mark="all" data-active="true">
                        <span class="map-year-slider-mark-label"><?php echo e($language === 'en' ? 'All' : 'Semua'); ?></span>
                        <span class="map-year-slider-dot"></span>
                    </span>
                </div>
                <input
                    id="map-year-range"
                    class="map-year-slider-input"
                    type="range"
                    min="0"
                    max="<?php echo e($availableYears->count()); ?>"
                    value="<?php echo e($availableYears->count()); ?>"
                    step="1"
                    data-year-values="<?php echo e($availableYears->join(',')); ?>"
                    aria-label="<?php echo e($language === 'en' ? 'Location year' : 'Tahun lokasi'); ?>"
                >
                <output id="map-year-output" class="map-year-slider-value" for="map-year-range" aria-live="polite">
                    <span id="map-year-label"><?php echo e($language === 'en' ? 'All' : 'Semua'); ?></span>
                    <span class="sr-only">, </span>
                    <span id="map-result-count" class="sr-only"><?php echo e($locations->count()); ?> <?php echo e($language === 'en' ? 'locations' : 'lokasi'); ?></span>
                </output>
            </div>
            <p class="mt-1 text-center text-[10px] font-bold text-slate-500">&larr; <?php echo e($language === 'en' ? 'Drag to change year' : 'Geser untuk mengganti tahun'); ?> &rarr;</p>
        </div>

        <div class="absolute inset-0 bg-white">
            <div id="ember-map" class="h-full w-full bg-slate-100" aria-label="<?php echo e($language === 'en' ? 'EMBER location distribution map' : 'Peta persebaran lokasi EMBER'); ?>"></div>
            <div id="map-filter-empty" class="pointer-events-none absolute right-4 top-4 z-[550] hidden max-w-sm bg-white/95 px-5 py-4 text-center text-sm font-semibold text-slate-700 shadow-lg backdrop-blur sm:right-5 sm:top-5" aria-live="polite">
                <?php echo e($language === 'en' ? 'No locations match the selected filters.' : 'Tidak ada titik lokasi yang sesuai dengan filter.'); ?>

            </div>

            <aside id="map-detail-panel" data-open="false" class="map-detail-panel absolute inset-y-0 right-0 z-[600] flex w-full max-w-sm flex-col bg-white shadow-2xl" aria-hidden="true" aria-label="<?php echo e($language === 'en' ? 'Location details' : 'Detail lokasi'); ?>">
                <div class="flex items-start justify-between gap-4 bg-slate-950 p-5 text-white">
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-red-400"><?php echo e($language === 'en' ? 'Location details' : 'Detail lokasi'); ?></p>
                        <h2 id="map-detail-title" class="mt-2 truncate text-xl font-bold">-</h2>
                        <p id="map-detail-region" class="mt-1 line-clamp-2 text-xs leading-5 text-slate-400">-</p>
                    </div>
                    <button id="map-detail-close" type="button" class="flex size-9 shrink-0 items-center justify-center border border-white/20 text-slate-300 transition hover:bg-white/10 hover:text-white" aria-label="<?php echo e($language === 'en' ? 'Close location details' : 'Tutup detail lokasi'); ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-5" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-5">
                    <div class="grid gap-px bg-slate-200 sm:grid-cols-2">
                        <div class="bg-slate-50 p-4">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Confidence NASA</p>
                            <p id="map-detail-confidence" class="mt-2 font-bold text-slate-900">-</p>
                        </div>
                        <div class="bg-slate-50 p-4">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Status</p>
                            <p id="map-detail-status" class="mt-2 font-bold text-slate-900">-</p>
                        </div>
                        <div class="bg-slate-950 p-4 text-white">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-red-300">Fire Susceptibility</p>
                            <p id="map-detail-fsi" class="mt-2 text-2xl font-black">-</p>
                        </div>
                        <div class="bg-slate-950 p-4 text-white">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400"><?php echo e($language === 'en' ? 'FSI class' : 'Kelas FSI'); ?></p>
                            <p id="map-detail-fsiClass" class="mt-2 font-bold">-</p>
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400"><?php echo e($language === 'en' ? 'Land-cover context' : 'Konteks tutupan lahan'); ?></p>
                        <p id="map-detail-landCover" class="mt-2 font-bold text-slate-900">-</p>
                        <div class="mt-3 flex items-center justify-between gap-3 text-xs">
                            <span class="font-semibold text-slate-400">Hybrid LCS</span>
                            <span id="map-detail-lcs" class="font-black text-slate-800">-</span>
                        </div>
                    </div>
                    
                    <div class="mt-3 flex items-center justify-between gap-3 text-xs">
                        <span class="font-semibold text-slate-400">
                            <?php echo e($language === 'en' ? 'Context assessment' : 'Penilaian konteks'); ?>

                        </span>

                        <span id="map-detail-contextFlag" class="rounded-full bg-white px-2.5 py-1 font-black text-slate-700 ring-1 ring-slate-200">
                            -
                        </span>
                    </div>

                    <dl class="mt-5 divide-y divide-slate-100 border-y border-slate-200">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($language === 'en' ? [
                            'Province' => 'province', 'Regency/City' => 'regency', 'District' => 'district',
                            'Village' => 'village', 'Date' => 'date', 'Coordinates' => 'coordinates',
                        ] : [
                            'Provinsi' => 'province', 'Kabupaten/Kota' => 'regency', 'Kecamatan' => 'district',
                            'Desa' => 'village', 'Tanggal' => 'date', 'Koordinat' => 'coordinates',
                        ]); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="grid grid-cols-[110px_1fr] gap-3 py-3.5">
                                <dt class="text-xs font-semibold text-slate-400"><?php echo e($label); ?></dt>
                                <dd id="map-detail-<?php echo e($key); ?>" class="break-words text-sm font-semibold text-slate-800">-</dd>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </dl>
                </div>

                <div class="border-t border-slate-200 p-5">
                    <a id="map-detail-link" href="#" class="flex w-full items-center justify-center gap-2 bg-red-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-red-500">
                        <?php echo e($language === 'en' ? 'Open full details' : 'Buka detail lengkap'); ?> <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </aside>
            <script id="ember-map-data" type="application/json"><?php echo json_encode(['language' => $language, 'locations' => $locations, 'boundaryLayers' => $boundaryLayers], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.user', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\capstone\ember-neko-main\resources\views/user/map.blade.php ENDPATH**/ ?>