<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class ResetPasswordCommand extends Command
{
    /**
     * Nama dan signature perintah konsol.
     */
    protected $signature = 'hybrid:reset-password 
                            {email? : Alamat email pengguna} 
                            {--password= : Kata sandi baru (opsional)}';

    /**
     * Deskripsi perintah konsol.
     */
    protected $description = 'Mengatur ulang kata sandi akun pengguna HYBRID 20';

    /**
     * Eksekusi perintah konsol.
     */
    public function handle(): int
    {
        $email = $this->argument('email') ?: $this->ask('Masukkan alamat email');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("Pengguna dengan email [{$email}] tidak ditemukan.");
            return self::FAILURE;
        }

        $password = $this->option('password');
        if (! $password) {
            $password = $this->secret('Masukkan kata sandi baru');
            $passwordConfirm = $this->secret('Konfirmasi kata sandi baru');

            if ($password !== $passwordConfirm) {
                $this->error('Konfirmasi kata sandi tidak cocok.');
                return self::FAILURE;
            }
        }

        $validator = Validator::make([
            'password' => $password,
        ], [
            'password' => ['required', 'string', 'min:8'],
        ], [
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        $user->update([
            'password' => $password,
        ]);

        $this->info("Kata sandi untuk pengguna [{$user->name}] ({$user->email}) berhasil diperbarui.");

        return self::SUCCESS;
    }
}
