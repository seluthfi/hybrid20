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
</x-app-shell>
