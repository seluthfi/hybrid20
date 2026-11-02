<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Masuk — HYBRID 20</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-bg font-sans text-on-surface min-h-screen flex items-center justify-center p-4 antialiased selection:bg-primary-fixed">

    <main class="w-full max-w-[420px] py-8">
        <div class="glass-3 rounded-card p-6 sm:p-8 shadow-2xl border border-white/80 flex flex-col gap-6 relative overflow-hidden">
            
            <!-- Brand Logo & Header -->
            <div class="flex flex-col items-center text-center gap-2">
                <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-primary to-secondary-container flex items-center justify-center shadow-lg shadow-primary/25 mb-1">
                    <div class="w-12 h-12 rounded-full glass-2 flex items-center justify-center text-primary">
                        <x-icon name="fitness_center" class="w-6 h-6" />
                    </div>
                </div>

                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-pill bg-surface-container-high/60">
                    <span class="w-2 h-2 rounded-full bg-secondary"></span>
                    <span class="font-headline text-xs tracking-wider text-on-surface uppercase font-bold">HYBRID 20</span>
                </div>

                <h1 class="font-headline text-xl font-bold tracking-tight text-on-surface mt-1">
                    Jurnal Kebugaran Pribadi
                </h1>
                <p class="font-sans text-xs text-on-surface-variant max-w-xs">
                    Masuk untuk mencatat dan memantau latihan, nutrisi, dan program 20 minggumu.
                </p>
            </div>

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
                @csrf

                <!-- Field Email -->
                <x-field label="Email" name="email" id="email" :error="$errors->first('email')" required>
                    <div class="relative w-full flex items-center">
                        <span class="absolute left-4 text-on-surface-variant pointer-events-none">
                            <x-icon name="person" class="w-5 h-5" />
                        </span>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            value="{{ old('email') }}"
                            placeholder="nama@email.com"
                            required 
                            autofocus
                            class="w-full min-h-[48px] pl-11 pr-4 rounded-field glass-2 font-sans text-sm text-on-surface placeholder:text-outline-variant focus:outline-none focus:ring-2 focus:ring-primary border border-outline-variant/40 shadow-xs transition-all"
                        />
                    </div>
                </x-field>

                <!-- Field Password -->
                <div x-data="{ showPass: false }" class="w-full">
                    <x-field label="Kata Sandi" name="password" id="password" :error="$errors->first('password')" required>
                        <div class="relative w-full flex items-center">
                            <span class="absolute left-4 text-on-surface-variant pointer-events-none">
                                <x-icon name="settings" class="w-5 h-5" />
                            </span>
                            <input 
                                :type="showPass ? 'text' : 'password'" 
                                name="password" 
                                id="password" 
                                placeholder="Masukkan kata sandi"
                                required 
                                class="w-full min-h-[48px] pl-11 pr-12 rounded-field glass-2 font-sans text-sm text-on-surface placeholder:text-outline-variant focus:outline-none focus:ring-2 focus:ring-primary border border-outline-variant/40 shadow-xs transition-all"
                            />
                            <button 
                                type="button" 
                                @click="showPass = !showPass"
                                class="absolute right-1 w-11 h-11 min-w-[44px] min-h-[44px] flex items-center justify-center text-on-surface-variant hover:text-on-surface rounded-full transition-colors"
                                aria-label="Tampilkan atau sembunyikan kata sandi"
                            >
                                <span x-show="!showPass">
                                    <x-icon name="visibility" class="w-5 h-5" />
                                </span>
                                <span x-show="showPass" style="display: none;">
                                    <x-icon name="visibility_off" class="w-5 h-5" />
                                </span>
                            </button>
                        </div>
                    </x-field>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" id="remember" class="sr-only peer" />
                        <div class="w-5 h-5 rounded-md border border-outline/50 flex items-center justify-center peer-checked:bg-primary peer-checked:border-primary text-transparent peer-checked:text-white transition-all shadow-xs">
                            <x-icon name="check" class="w-3.5 h-3.5" />
                        </div>
                        <span class="font-sans text-xs text-on-surface-variant font-medium">Ingat sesi</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <x-button type="submit" variant="primary" size="lg" class="w-full">
                        <span>Masuk</span>
                        <x-icon name="arrow_forward" class="w-5 h-5" />
                    </x-button>
                </div>
            </form>

            <!-- Info Note -->
            <div class="pt-2 border-t border-outline-variant/30 text-center">
                <p class="font-sans text-xs text-on-surface-variant">
                    Akun dikelola oleh pemilik aplikasi secara mandiri.
                </p>
            </div>
        </div>
    </main>

</body>
</html>
