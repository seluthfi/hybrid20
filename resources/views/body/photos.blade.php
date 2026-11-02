<x-app-shell :title="'Foto Progres'">
    <div class="flex flex-col gap-6 w-full max-w-4xl mx-auto pb-12" x-data="{
        uploadModalOpen: false,
        viewerModalOpen: false,
        activePhoto: null,
        openViewer(photo) {
            this.activePhoto = photo;
            this.viewerModalOpen = true;
        }
    }">

        <!-- Header: Judul & Segmented Toggle -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="font-headline text-2xl sm:text-3xl font-bold tracking-tight text-on-surface">
                    Foto Progres
                </h1>
                <p class="font-sans text-xs text-on-surface-variant">
                    Simpan visual perubahan tubuh secara privat dan aman.
                </p>
            </div>

            <!-- Segmented Pill Selector (Berat & Lingkar vs Foto Progres) -->
            <div class="p-1 rounded-full glass-2 flex items-center gap-1 self-start sm:self-auto">
                <a href="{{ route('body.index') }}" class="px-4 py-2 rounded-full font-headline text-xs font-medium text-on-surface-variant hover:text-on-surface flex items-center gap-1.5 transition-colors">
                    <x-icon name="straighten" class="w-4 h-4" />
                    <span>Berat & Lingkar</span>
                </a>
                <a href="{{ route('progress-photos.index') }}" class="px-4 py-2 rounded-full font-headline text-xs font-bold bg-surface-container-lowest text-primary shadow-sm flex items-center gap-1.5">
                    <x-icon name="photo_camera" class="w-4 h-4" />
                    <span>Foto Progres</span>
                </a>
            </div>
        </div>

        <!-- Privacy Lock Assurance Banner (PLAN.md §10.9: jujur, tanpa klaim palsu on-device/enkripsi lokal) -->
        <div class="p-4 rounded-2xl glass-2 border border-outline-variant/30 flex items-start gap-3">
            <div class="w-8 h-8 rounded-full bg-secondary-container text-on-secondary-container flex items-center justify-center shrink-0">
                <x-icon name="lock" class="w-4 h-4" />
            </div>
            <div class="flex flex-col gap-0.5">
                <span class="font-headline text-xs font-bold uppercase tracking-wider text-secondary">
                    Privasi Foto Terlindungi
                </span>
                <p class="font-sans text-xs text-on-surface-variant leading-relaxed">
                    Foto privat. Hanya bisa dibuka setelah login dan tidak punya tautan publik. Metadata lokasi dan perangkat (EXIF) otomatis dibersihkan saat diunggah.
                </p>
            </div>
        </div>

        <!-- Action Bar: Tombol Upload & Komparasi -->
        <div class="flex items-center justify-between gap-3">
            @if($photos->count() >= 2)
                <a href="{{ route('progress-photos.compare') }}">
                    <x-button variant="ghost" size="sm">
                        <x-icon name="compare" class="w-4 h-4" />
                        <span>Komparasi 2 Foto</span>
                    </x-button>
                </a>
            @else
                <div></div>
            @endif

            <button type="button" @click="uploadModalOpen = true">
                <x-button variant="primary" size="md">
                    <x-icon name="add_a_photo" class="w-4 h-4" />
                    <span>Unggah Foto Progres</span>
                </x-button>
            </button>
        </div>

        <!-- Chronological Galleries Grouped by Date (PLAN.md §10.9) -->
        <div class="flex flex-col gap-6">
            @forelse($groups as $group)
                <x-card level="1" class="p-4 sm:p-5 flex flex-col gap-3">
                    <!-- Date Group Header -->
                    <div class="flex items-center justify-between pb-2 border-b border-outline-variant/30">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-primary"></span>
                            <h2 class="font-headline text-sm font-bold text-on-surface">
                                {{ $group['date_formatted'] }}
                            </h2>
                            <span class="px-2 py-0.5 rounded-full bg-primary-fixed text-primary font-headline text-[10px] font-bold">
                                {{ $group['week_label'] }}
                            </span>
                        </div>

                        @if($group['weight_kg'])
                            <span class="font-headline text-xs font-bold text-on-surface-variant tabular-nums">
                                {{ number_format($group['weight_kg'], 1, ',', '.') }} kg
                            </span>
                        @endif
                    </div>

                    <!-- Photos Grid for this date -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach($group['photos'] as $p)
                            @php
                                $poseTitles = [
                                    'front' => 'Pose Depan',
                                    'side' => 'Pose Samping',
                                    'back' => 'Pose Belakang',
                                    'other' => 'Lainnya',
                                ];
                            @endphp
                            <div @click="openViewer({{ json_encode($p) }})" class="group relative flex flex-col gap-1 cursor-pointer">
                                <div class="relative w-full aspect-[3/4] rounded-xl overflow-hidden bg-surface-container-high border border-outline-variant/30 shadow-xs">
                                    <img src="{{ route('progress-photos.thumb', $p->id) }}" 
                                         alt="{{ $poseTitles[$p->pose_label] ?? 'Foto Progres' }} tanggal {{ $group['date_formatted'] }}" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    
                                    <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full bg-on-background/70 text-white font-headline text-[10px]">
                                        {{ $poseTitles[$p->pose_label] ?? ucfirst($p->pose_label) }}
                                    </span>
                                </div>
                                <span class="text-center font-sans text-xs text-on-surface-variant group-hover:text-primary transition-colors">
                                    {{ $poseTitles[$p->pose_label] ?? ucfirst($p->pose_label) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @empty
                <!-- Empty State (PLAN.md §10.9: mulai dari empty state tanpa foto contoh buatan AI/CDN) -->
                <x-card level="1" class="p-10 text-center flex flex-col items-center justify-center gap-3">
                    <div class="w-16 h-16 rounded-full bg-surface-container-high text-outline flex items-center justify-center">
                        <x-icon name="photo_camera" class="w-8 h-8" />
                    </div>
                    <div class="flex flex-col gap-1 max-w-sm">
                        <span class="font-headline text-lg font-bold text-on-surface">Belum Ada Foto Progres</span>
                        <p class="font-sans text-xs text-on-surface-variant leading-relaxed">
                            Foto progres membantumu melihat transformasi fisik yang tidak selalu terlihat di timbangan. Unggah foto pertamamu hari ini.
                        </p>
                    </div>
                    <button type="button" @click="uploadModalOpen = true" class="mt-2">
                        <x-button variant="primary" size="md">
                            <x-icon name="add_a_photo" class="w-4 h-4" />
                            <span>Unggah Foto Pertama</span>
                        </x-button>
                    </button>
                </x-card>
            @endforelse
        </div>

        <!-- MODAL UPLOAD FOTO (PLAN.md §10.9) -->
        <div x-show="uploadModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-on-background/40" @click="uploadModalOpen = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
                <div class="w-full max-w-md glass-3 rounded-card p-6 shadow-2xl border border-white/80 pointer-events-auto flex flex-col gap-4">
                    <div class="flex items-center justify-between pb-2 border-b border-outline-variant/30">
                        <h3 class="font-headline text-lg font-bold text-on-surface">Unggah Foto Progres</h3>
                        <button type="button" @click="uploadModalOpen = false" class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-colors">
                            <x-icon name="close" class="w-5 h-5" />
                        </button>
                    </div>

                    <form action="{{ route('progress-photos.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
                        @csrf

                        <div>
                            <label class="block font-headline text-xs font-semibold text-on-surface mb-1.5">Berkas Foto</label>
                            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required class="w-full text-xs font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                            <span class="block text-[11px] font-sans text-outline mt-1">Format: JPG, PNG, atau WebP (maks. 10 MB). Metadata EXIF otomatis dibersihkan.</span>
                        </div>

                        <div>
                            <label class="block font-headline text-xs font-semibold text-on-surface mb-1.5">Tanggal Pengambilan</label>
                            <input type="date" name="taken_at" value="{{ Carbon\Carbon::today()->toDateString() }}" required class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                        </div>

                        <div>
                            <label class="block font-headline text-xs font-semibold text-on-surface mb-1.5">Sudut Pose</label>
                            <select name="pose_label" required class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                                <option value="front">Pose Depan (Front)</option>
                                <option value="side">Pose Samping (Side)</option>
                                <option value="back">Pose Belakang (Back)</option>
                                <option value="other">Lainnya (Other)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-headline text-xs font-semibold text-on-surface mb-1.5">Catatan Tambahan (Opsional)</label>
                            <input type="text" name="notes" placeholder="misal: pencahayaan pagi, puasa 12 jam" class="w-full text-sm font-sans rounded-field border border-outline-variant/60 bg-surface-container-lowest p-2.5 text-on-surface focus:outline-none focus:ring-2 focus:ring-primary/40">
                        </div>

                        <div class="flex justify-end gap-2 pt-2 border-t border-outline-variant/20">
                            <x-button type="button" variant="ghost" size="md" @click="uploadModalOpen = false">
                                Batal
                            </x-button>
                            <x-button type="submit" variant="primary" size="md">
                                Simpan Foto
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- MODAL VIEWER FULLSCREEN (PLAN.md §10.9) -->
        <div x-show="viewerModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-on-background/70" @click="viewerModalOpen = false"></div>
            <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
                <div class="w-full max-w-lg glass-3 rounded-card p-5 shadow-2xl border border-white/80 pointer-events-auto flex flex-col gap-4">
                    <div class="flex items-center justify-between pb-2 border-b border-outline-variant/30">
                        <div class="flex flex-col">
                            <h3 class="font-headline text-base font-bold text-on-surface" x-text="activePhoto?.pose_label ? ('Pose ' + activePhoto.pose_label.toUpperCase()) : 'Foto Progres'"></h3>
                            <span class="text-xs font-sans text-outline" x-text="activePhoto?.taken_at"></span>
                        </div>
                        <button type="button" @click="viewerModalOpen = false" class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface-variant hover:bg-surface-container-high transition-colors">
                            <x-icon name="close" class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="relative w-full max-h-[65vh] rounded-xl overflow-hidden bg-black/5 flex items-center justify-center">
                        <img :src="'/progress-photos/' + (activePhoto?.id || '') + '/image'" alt="Foto progres ukuran penuh" class="max-h-[60vh] w-auto object-contain rounded-lg">
                    </div>

                    <template x-if="activePhoto?.notes">
                        <p class="text-xs font-sans text-on-surface-variant italic px-1" x-text="activePhoto.notes"></p>
                    </template>

                    <div class="flex items-center justify-between pt-2 border-t border-outline-variant/20">
                        <form :action="'/progress-photos/' + (activePhoto?.id || '')" method="POST" onsubmit="return confirm('Hapus foto ini secara permanen?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-headline font-semibold text-error hover:underline px-2 py-1">
                                Hapus Foto
                            </button>
                        </form>

                        <x-button type="button" variant="ghost" size="sm" @click="viewerModalOpen = false">
                            Tutup
                        </x-button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-shell>
