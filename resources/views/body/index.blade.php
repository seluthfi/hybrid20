<x-app-shell :title="'Metrik Tubuh'">
    <div class="flex flex-col gap-6 w-full max-w-4xl mx-auto pb-12" x-data="{
        logWeightModalOpen: false,
        inputDate: '{{ Carbon\Carbon::now($settings?->timezone ?? 'Asia/Jakarta')->toDateString() }}',
        inputWeight: '{{ $latest?->weight_kg ? number_format($latest->weight_kg, 1, '.', '') : '' }}',
        inputWaist: '',
        inputChest: '',
        inputArm: '',
        inputThigh: '',
        inputNotes: ''
    }">

        <!-- Header: Judul & Segmented Nav (Berat & Lingkar vs Foto Progres) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="font-headline text-2xl sm:text-3xl font-bold tracking-tight text-on-surface">
                    Metrik & Komposisi Tubuh
                </h1>
                <p class="font-sans text-xs text-on-surface-variant">
                    Catat berat badan dan lingkar tubuh secara berkala untuk memantau tren perkembangan.
                </p>
            </div>

            <!-- Segmented Pill Selector (Berat & Lingkar vs Foto Progres) -->
            <div class="p-1 rounded-full bg-surface-container-high/60 backdrop-blur-md flex items-center gap-1 self-start sm:self-auto">
                <a href="{{ route('body.index') }}" class="px-4 py-2 rounded-full font-headline text-xs font-bold bg-surface-container-lowest text-primary shadow-sm flex items-center gap-1.5">
                    <x-icon name="straighten" class="w-4 h-4" />
                    <span>Berat & Lingkar</span>
                </a>
                <a href="{{ route('progress-photos.index') }}" class="px-4 py-2 rounded-full font-headline text-xs font-medium text-on-surface-variant hover:text-on-surface flex items-center gap-1.5 transition-colors">
                    <x-icon name="photo_camera" class="w-4 h-4" />
                    <span>Foto Progres</span>
                </a>
            </div>
        </div>

        <!-- Kartu Berat Badan Saat Ini & Tren 7 Hari (PLAN.md §8.8) -->
        <x-card level="1" class="p-6 relative overflow-hidden">
            <div class="flex flex-col gap-4">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="font-headline text-xs font-bold uppercase tracking-wider text-on-surface-variant">
                            Berat Badan Terakhir
                        </span>
                        <div class="flex items-baseline gap-2 mt-1">
                            @if($latest && $latest->weight_kg)
                                <span class="font-headline text-4xl sm:text-5xl font-bold tabular-nums text-on-surface">
                                    {{ number_format($latest->weight_kg, 1, ',', '.') }}
                                </span>
                                <span class="font-headline text-base font-semibold text-outline">kg</span>
                            @else
                                <span class="font-headline text-3xl font-bold text-outline">
                                    Belum ada data
                                </span>
                            @endif
                        </div>
                        @if($daysAgoText)
                            <span class="block text-xs font-sans text-outline mt-0.5">
                                Dicatat {{ $daysAgoText }} ({{ $latest->measured_at->translatedFormat('d M Y') }})
                            </span>
                        @endif
                    </div>

                    <button type="button" @click="logWeightModalOpen = true">
                        <x-button variant="primary" size="md">
                            <x-icon name="add" class="w-4 h-4" />
                            <span>Catat Pengukuran</span>
                        </x-button>
                    </button>
                </div>

                <!-- Tren 7 Hari Netral (§8.8: tanpa kata aman, sukses, target tercapai) -->
                @if($trendText)
                    <div class="p-3 rounded-xl bg-surface-container-low/80 flex items-center gap-2.5">
                        <x-icon name="trending_neutral" class="w-4 h-4 text-primary shrink-0" />
                        <span class="font-sans text-xs font-medium text-on-surface">
                            {{ $trendText }}
                        </span>
                    </div>
                @endif
            </div>
        </x-card>

        <!-- GRAFIK TREN METRIK TUBUH (PLAN.md §8.8, §9.8, D13) -->
        <x-card level="1" class="p-4 sm:p-6 flex flex-col gap-4" x-data="bodyMetricChart()">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-outline-variant/20">
                <div class="flex flex-col">
                    <div class="flex items-center gap-2">
                        <x-icon name="show_chart" class="w-5 h-5 text-primary" />
                        <h2 class="font-headline text-base sm:text-lg font-bold text-on-surface">
                            Grafik Tren Metrik Tubuh
                        </h2>
                    </div>
                    <p class="font-sans text-xs text-on-surface-variant mt-0.5">
                        Titik data asli dan garis rata-rata 7 hari (dihitung saat ada ≥ 4 data dalam jendela 7 hari).
                    </p>
                </div>

                <!-- Range Selector Chips -->
                <div class="flex items-center gap-1.5 self-start sm:self-auto bg-surface-container-high/60 p-1 rounded-full">
                    <button type="button" 
                            @click="setRange('4w')" 
                            :class="range === '4w' ? 'bg-surface-container-lowest text-primary font-bold shadow-xs' : 'text-on-surface-variant hover:text-on-surface'"
                            class="px-3 py-1 rounded-full text-xs font-headline transition-all">
                        4 Minggu
                    </button>
                    <button type="button" 
                            @click="setRange('8w')" 
                            :class="range === '8w' ? 'bg-surface-container-lowest text-primary font-bold shadow-xs' : 'text-on-surface-variant hover:text-on-surface'"
                            class="px-3 py-1 rounded-full text-xs font-headline transition-all">
                        8 Minggu
                    </button>
                    <button type="button" 
                            @click="setRange('all')" 
                            :class="range === 'all' ? 'bg-surface-container-lowest text-primary font-bold shadow-xs' : 'text-on-surface-variant hover:text-on-surface'"
                            class="px-3 py-1 rounded-full text-xs font-headline transition-all">
                        Semua
                    </button>
                </div>
            </div>

            <!-- Metric Selector Tabs -->
            <div class="flex items-center flex-wrap gap-2 pt-1">
                <button type="button" 
                        @click="setMetric('weight_kg')"
                        :class="metric === 'weight_kg' ? 'bg-primary text-on-primary font-bold shadow-xs' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        class="px-3.5 py-1.5 rounded-pill text-xs font-headline transition-all flex items-center gap-1.5">
                    <x-icon name="scale" class="w-3.5 h-3.5" />
                    <span>Berat Badan</span>
                </button>
                <button type="button" 
                        @click="setMetric('waist_cm')"
                        :class="metric === 'waist_cm' ? 'bg-primary text-on-primary font-bold shadow-xs' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        class="px-3.5 py-1.5 rounded-pill text-xs font-headline transition-all flex items-center gap-1.5">
                    <x-icon name="straighten" class="w-3.5 h-3.5" />
                    <span>Pinggang</span>
                </button>
                <button type="button" 
                        @click="setMetric('chest_cm')"
                        :class="metric === 'chest_cm' ? 'bg-primary text-on-primary font-bold shadow-xs' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        class="px-3.5 py-1.5 rounded-pill text-xs font-headline transition-all flex items-center gap-1.5">
                    <span>Dada</span>
                </button>
                <button type="button" 
                        @click="setMetric('arm_cm')"
                        :class="metric === 'arm_cm' ? 'bg-primary text-on-primary font-bold shadow-xs' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        class="px-3.5 py-1.5 rounded-pill text-xs font-headline transition-all flex items-center gap-1.5">
                    <span>Lengan</span>
                </button>
                <button type="button" 
                        @click="setMetric('thigh_cm')"
                        :class="metric === 'thigh_cm' ? 'bg-primary text-on-primary font-bold shadow-xs' : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container hover:text-on-surface'"
                        class="px-3.5 py-1.5 rounded-pill text-xs font-headline transition-all flex items-center gap-1.5">
                    <span>Paha</span>
                </button>
            </div>

            <!-- Canvas Container -->
            <div x-show="!isEmpty" x-cloak class="relative w-full h-72 sm:h-80 pt-2">
                <canvas id="bodyMetricChart" x-ref="metricCanvas"></canvas>
            </div>

            <!-- Empty State (PLAN.md §13 Fase 6: DB kosong harus menampilkan empty state) -->
            <div x-show="isEmpty" x-cloak class="py-12 flex flex-col items-center justify-center text-center">
                <x-icon name="monitor_weight" class="w-10 h-10 text-outline mb-2" />
                <span class="font-headline text-sm font-semibold text-on-surface">Belum Ada Data Pengukuran</span>
                <span class="font-sans text-xs text-on-surface-variant max-w-sm mt-0.5">
                    Catat pengukuran <span x-text="currentMetricLabel"></span> untuk melihat grafik perkembangan nyata di sini.
                </span>
            </div>

            <!-- Screen Reader Accessible Table (PLAN.md §9.8) -->
            <table class="sr-only" :aria-label="'Tabel ringkasan tren ' + currentMetricLabel">
                <caption class="sr-only">Tabel Data Riwayat Metrik Tubuh</caption>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nilai</th>
                        <th>Rata-rata 7 Hari</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, idx) in tableData" :key="idx">
                        <tr>
                            <td x-text="item.date"></td>
                            <td x-text="item.val"></td>
                            <td x-text="item.ma"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </x-card>

        <!-- GRAFIK RIWAYAT LATIHAN TERPILIH (PLAN.md §9.8) -->
        <x-card level="1" class="p-4 sm:p-6 flex flex-col gap-4" x-data="exerciseProgressionChart()">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-outline-variant/20">
                <div class="flex flex-col">
                    <div class="flex items-center gap-2">
                        <x-icon name="fitness_center" class="w-5 h-5 text-secondary" />
                        <h2 class="font-headline text-base sm:text-lg font-bold text-on-surface">
                            Riwayat Beban & Volume Latihan
                        </h2>
                    </div>
                    <p class="font-sans text-xs text-on-surface-variant mt-0.5">
                        Perkembangan beban tertinggi (kg) dan total volume (beban × repetisi) per sesi latihan.
                    </p>
                </div>

                <!-- Exercise Selector Dropdown -->
                <div x-show="availableExercises.length > 0" x-cloak class="self-start sm:self-auto">
                    <select @change="changeExercise($event.target.value)" 
                            class="px-3 py-1.5 rounded-field text-xs font-headline font-semibold bg-surface-container-lowest border border-outline-variant/60 text-on-surface focus:outline-primary">
                        <template x-for="ex in availableExercises" :key="ex.id">
                            <option :value="ex.id" :selected="ex.id === exerciseId" x-text="ex.name"></option>
                        </template>
                    </select>
                </div>
            </div>

            <!-- Canvas Container -->
            <div x-show="!isEmpty" x-cloak class="relative w-full h-72 sm:h-80 pt-2">
                <canvas id="exerciseHistoryChart" x-ref="exerciseCanvas"></canvas>
            </div>

            <!-- Empty State -->
            <div x-show="isEmpty" x-cloak class="py-12 flex flex-col items-center justify-center text-center">
                <x-icon name="fitness_center" class="w-10 h-10 text-outline mb-2" />
                <span class="font-headline text-sm font-semibold text-on-surface">Belum Ada Riwayat Latihan</span>
                <span class="font-sans text-xs text-on-surface-variant max-w-sm mt-0.5">
                    Selesaikan sesi latihan gym dan catat set untuk melihat progres beban dan volume di sini.
                </span>
            </div>

            <!-- Screen Reader Accessible Table (PLAN.md §9.8) -->
            <table class="sr-only" :aria-label="'Tabel ringkasan riwayat latihan ' + currentExerciseName">
                <caption class="sr-only">Tabel Data Progres Latihan Beban</caption>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Beban Tertinggi</th>
                        <th>Total Volume</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(item, idx) in tableData" :key="idx">
                        <tr>
                            <td x-text="item.date"></td>
                            <td x-text="item.max"></td>
                            <td x-text="item.vol"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </x-card>

        <!-- Riwayat Pengukuran Tubuh (PLAN.md §10.9) -->
        <x-card level="1" class="p-4 sm:p-6 flex flex-col gap-4">
            <div class="flex items-center justify-between pb-2 border-b border-outline-variant/30">
                <h2 class="font-headline text-base font-bold text-on-surface">Riwayat Pengukuran</h2>
                <span class="text-xs font-sans text-outline">Tersimpan sesuai data yang diisi</span>
            </div>

            <!-- Tabel Riwayat -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm font-sans">
                    <thead>
                        <tr class="border-b border-outline-variant/30 text-xs font-headline text-on-surface-variant uppercase tracking-wider">
                            <th class="py-2.5 px-3">Tanggal</th>
                            <th class="py-2.5 px-3">Berat (kg)</th>
                            <th class="py-2.5 px-3">Pinggang</th>
                            <th class="py-2.5 px-3">Dada</th>
                            <th class="py-2.5 px-3">Lengan</th>
                            <th class="py-2.5 px-3">Paha</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20">
                        @forelse($measurements as $m)
                            <tr class="hover:bg-surface-container-low/50 transition-colors">
                                <td class="py-3 px-3 font-medium text-on-surface whitespace-nowrap">
                                    {{ $m->measured_at->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-3 px-3 font-headline font-bold text-on-surface tabular-nums">
                                    {{ $m->weight_kg ? number_format($m->weight_kg, 1, ',', '.') : '-' }}
                                </td>
                                <td class="py-3 px-3 tabular-nums text-on-surface-variant">
                                    {{ $m->waist_cm ? number_format($m->waist_cm, 1, ',', '.') . ' cm' : '-' }}
                                </td>
                                <td class="py-3 px-3 tabular-nums text-on-surface-variant">
                                    {{ $m->chest_cm ? number_format($m->chest_cm, 1, ',', '.') . ' cm' : '-' }}
                                </td>
                                <td class="py-3 px-3 tabular-nums text-on-surface-variant">
                                    {{ $m->arm_cm ? number_format($m->arm_cm, 1, ',', '.') . ' cm' : '-' }}
                                </td>
                                <td class="py-3 px-3 tabular-nums text-on-surface-variant">
                                    {{ $m->thigh_cm ? number_format($m->thigh_cm, 1, ',', '.') . ' cm' : '-' }}
                                </td>
                                <td class="py-3 px-3 text-right whitespace-nowrap">
                                    <form action="{{ route('measurements.destroy', $m->measured_at->toDateString()) }}" method="POST" onsubmit="return confirm('Hapus catatan pengukuran tanggal ini?')" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-headline text-error hover:underline px-2 py-1">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-xs font-sans text-outline">
                                    Belum ada catatan pengukuran tubuh.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($measurements->hasPages())
                <div class="pt-3 border-t border-outline-variant/30">
                    {{ $measurements->links() }}
                </div>
            @endif
        </x-card>

        <!-- MODAL CATAT / UPSERT PENGUKURAN TUBUH (PLAN.md §10.9) -->
        <div x-show="logWeightModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-on-background/25 backdrop-blur-md" @click="logWeightModalOpen = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
                <div class="w-full max-w-md glass-3 rounded-card p-6 shadow-2xl border border-white/80 pointer-events-auto flex flex-col gap-4">
                    <div class="flex items-center justify-between pb-2 border-b border-outline-variant/30">
                        <h3 class="font-headline text-lg font-bold text-on-surface">Catat Pengukuran Tubuh</h3>
                        <button type="button" @click="logWeightModalOpen = false" class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-colors">
                            <x-icon name="close" class="w-5 h-5" />
                        </button>
                    </div>

                    <form :action="'/body/' + inputDate" method="POST" class="flex flex-col gap-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block font-headline text-xs font-semibold text-on-surface mb-1.5">Tanggal</label>
                            <input type="date" name="date" x-model="inputDate" required class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                        </div>

                        <div>
                            <label class="block font-headline text-xs font-semibold text-on-surface mb-1.5">Berat Badan (kg)</label>
                            <input type="number" step="0.1" name="weight_kg" x-model="inputWeight" placeholder="misal: 73.5" class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-headline text-xs font-semibold text-on-surface mb-1.5">Lingkar Pinggang (cm)</label>
                                <input type="number" step="0.1" name="waist_cm" x-model="inputWaist" placeholder="Opsional" class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                            </div>
                            <div>
                                <label class="block font-headline text-xs font-semibold text-on-surface mb-1.5">Lingkar Dada (cm)</label>
                                <input type="number" step="0.1" name="chest_cm" x-model="inputChest" placeholder="Opsional" class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-headline text-xs font-semibold text-on-surface mb-1.5">Lingkar Lengan (cm)</label>
                                <input type="number" step="0.1" name="arm_cm" x-model="inputArm" placeholder="Opsional" class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                            </div>
                            <div>
                                <label class="block font-headline text-xs font-semibold text-on-surface mb-1.5">Lingkar Paha (cm)</label>
                                <input type="number" step="0.1" name="thigh_cm" x-model="inputThigh" placeholder="Opsional" class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                            </div>
                        </div>

                        <div class="flex justify-end gap-2 pt-2 border-t border-outline-variant/20">
                            <x-button type="button" variant="ghost" size="md" @click="logWeightModalOpen = false">
                                Batal
                            </x-button>
                            <x-button type="submit" variant="primary" size="md">
                                Simpan Pengukuran
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <!-- Chart.js Alpine Logic -->
    <script>
        function bodyMetricChart() {
            return {
                metric: 'weight_kg',
                range: '4w',
                currentMetricLabel: 'Berat Badan',
                unit: 'kg',
                isEmpty: false,
                isLoading: false,
                tableData: [],
                chartInstance: null,

                init() {
                    this.fetchData();
                },

                setMetric(newMetric) {
                    this.metric = newMetric;
                    this.fetchData();
                },

                setRange(newRange) {
                    this.range = newRange;
                    this.fetchData();
                },

                async fetchData() {
                    this.isLoading = true;
                    try {
                        const res = await fetch(`/charts/weight?metric=${this.metric}&range=${this.range}`);
                        const data = await res.json();
                        this.isEmpty = data.empty || !data.values || data.values.length === 0;
                        this.currentMetricLabel = data.metric_label || 'Metrik';
                        this.unit = data.unit || 'kg';

                        this.tableData = (data.labels || []).map((label, i) => ({
                            date: label,
                            val: data.values[i] + ' ' + this.unit,
                            ma: data.moving_averages[i] ? data.moving_averages[i] + ' ' + this.unit : '-'
                        }));

                        if (!this.isEmpty && this.$refs.metricCanvas) {
                            this.$nextTick(() => {
                                this.renderChart(data);
                            });
                        } else if (this.chartInstance) {
                            this.chartInstance.destroy();
                            this.chartInstance = null;
                        }
                    } catch (e) {
                        this.isEmpty = true;
                    } finally {
                        this.isLoading = false;
                    }
                },

                renderChart(data) {
                    if (this.chartInstance) {
                        this.chartInstance.destroy();
                        this.chartInstance = null;
                    }
                    if (!this.$refs.metricCanvas) return;

                    const ctx = this.$refs.metricCanvas.getContext('2d');
                    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                    this.chartInstance = new window.Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [
                                {
                                    label: `${data.metric_label} (${data.unit})`,
                                    data: data.values,
                                    borderColor: '#613dde',
                                    backgroundColor: 'rgba(97, 61, 222, 0.08)',
                                    borderWidth: 2.5,
                                    pointBackgroundColor: '#ffffff',
                                    pointBorderColor: '#613dde',
                                    pointBorderWidth: 2,
                                    pointRadius: 4.5,
                                    pointHoverRadius: 6.5,
                                    fill: true,
                                    tension: 0.2,
                                },
                                {
                                    label: 'Rata-rata 7 Hari (≥4 data)',
                                    data: data.moving_averages,
                                    borderColor: '#006b5c',
                                    borderWidth: 2,
                                    borderDash: [5, 5],
                                    pointRadius: 0,
                                    pointHoverRadius: 4,
                                    fill: false,
                                    tension: 0.3,
                                    spanGaps: false
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: prefersReducedMotion ? false : { duration: 500 },
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        color: '#1c1929',
                                        font: {
                                            family: 'Plus Jakarta Sans',
                                            size: 12,
                                            weight: 600
                                        },
                                        usePointStyle: true,
                                        boxWidth: 8
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(28, 25, 41, 0.9)',
                                    titleFont: { family: 'Space Grotesk', size: 13, weight: 700 },
                                    bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                                    padding: 10,
                                    cornerRadius: 8,
                                    callbacks: {
                                        label: function(context) {
                                            if (context.parsed.y === null) return null;
                                            return `${context.dataset.label}: ${context.parsed.y.toLocaleString('id-ID')} ${data.unit}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        color: 'rgba(202, 196, 215, 0.25)',
                                        drawBorder: false
                                    },
                                    ticks: {
                                        color: '#484555',
                                        font: { family: 'Plus Jakarta Sans', size: 11 }
                                    }
                                },
                                y: {
                                    grid: {
                                        color: 'rgba(202, 196, 215, 0.25)',
                                        drawBorder: false
                                    },
                                    ticks: {
                                        color: '#484555',
                                        font: { family: 'Space Grotesk', size: 12 }
                                    }
                                }
                            }
                        }
                    });
                }
            }
        }

        function exerciseProgressionChart() {
            return {
                exerciseId: null,
                availableExercises: [],
                currentExerciseName: '',
                isEmpty: false,
                isLoading: false,
                tableData: [],
                chartInstance: null,

                init() {
                    this.fetchData();
                },

                changeExercise(id) {
                    this.exerciseId = id;
                    this.fetchData();
                },

                async fetchData() {
                    this.isLoading = true;
                    try {
                        const url = this.exerciseId ? `/charts/exercise-history?exercise_id=${this.exerciseId}` : '/charts/exercise-history';
                        const res = await fetch(url);
                        const data = await res.json();
                        this.isEmpty = data.empty || !data.sessions || data.sessions.length === 0;
                        this.availableExercises = data.available_exercises || [];
                        if (data.exercise) {
                            this.exerciseId = data.exercise.id;
                            this.currentExerciseName = data.exercise.name;
                        }

                        this.tableData = (data.sessions || []).map(s => ({
                            date: s.formatted_date,
                            max: s.max_weight_kg + ' kg',
                            vol: s.volume_kg.toLocaleString('id-ID') + ' kg'
                        }));

                        if (!this.isEmpty && this.$refs.exerciseCanvas) {
                            this.$nextTick(() => {
                                this.renderChart(data);
                            });
                        } else if (this.chartInstance) {
                            this.chartInstance.destroy();
                            this.chartInstance = null;
                        }
                    } catch (e) {
                        this.isEmpty = true;
                    } finally {
                        this.isLoading = false;
                    }
                },

                renderChart(data) {
                    if (this.chartInstance) {
                        this.chartInstance.destroy();
                        this.chartInstance = null;
                    }
                    if (!this.$refs.exerciseCanvas) return;

                    const ctx = this.$refs.exerciseCanvas.getContext('2d');
                    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    const labels = data.sessions.map(s => s.formatted_date);
                    const maxWeights = data.sessions.map(s => s.max_weight_kg);
                    const volumes = data.sessions.map(s => s.volume_kg);

                    this.chartInstance = new window.Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    type: 'line',
                                    label: 'Beban Puncak (kg)',
                                    data: maxWeights,
                                    borderColor: '#613dde',
                                    backgroundColor: '#613dde',
                                    borderWidth: 2.5,
                                    pointRadius: 4.5,
                                    pointHoverRadius: 6.5,
                                    pointBackgroundColor: '#ffffff',
                                    pointBorderColor: '#613dde',
                                    pointBorderWidth: 2,
                                    yAxisID: 'y',
                                    tension: 0.2
                                },
                                {
                                    type: 'bar',
                                    label: 'Total Volume (kg)',
                                    data: volumes,
                                    backgroundColor: 'rgba(0, 107, 92, 0.45)',
                                    borderColor: '#006b5c',
                                    borderWidth: 1.5,
                                    borderRadius: 6,
                                    yAxisID: 'y1'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: prefersReducedMotion ? false : { duration: 500 },
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        color: '#1c1929',
                                        font: { family: 'Plus Jakarta Sans', size: 12, weight: 600 },
                                        usePointStyle: true,
                                        boxWidth: 8
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(28, 25, 41, 0.9)',
                                    titleFont: { family: 'Space Grotesk', size: 13, weight: 700 },
                                    bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                                    padding: 10,
                                    cornerRadius: 8
                                }
                            },
                            scales: {
                                x: {
                                    grid: { color: 'rgba(202, 196, 215, 0.25)', drawBorder: false },
                                    ticks: { color: '#484555', font: { family: 'Plus Jakarta Sans', size: 11 } }
                                },
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    title: {
                                        display: true,
                                        text: 'Beban Puncak (kg)',
                                        color: '#613dde',
                                        font: { family: 'Plus Jakarta Sans', size: 11, weight: 600 }
                                    },
                                    grid: { color: 'rgba(202, 196, 215, 0.25)', drawBorder: false },
                                    ticks: { color: '#484555', font: { family: 'Space Grotesk', size: 11 } }
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    title: {
                                        display: true,
                                        text: 'Volume (kg)',
                                        color: '#006b5c',
                                        font: { family: 'Plus Jakarta Sans', size: 11, weight: 600 }
                                    },
                                    grid: { drawOnChartArea: false, drawBorder: false },
                                    ticks: { color: '#484555', font: { family: 'Space Grotesk', size: 11 } }
                                }
                            }
                        }
                    });
                }
            }
        }
    </script>
</x-app-shell>
