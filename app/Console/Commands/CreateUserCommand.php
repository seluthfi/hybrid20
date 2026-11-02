<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateUserCommand extends Command
{
    /**
     * Nama dan signature perintah konsol.
     */
    protected $signature = 'hybrid:create-user 
                            {name? : Nama pengguna} 
                            {email? : Alamat email pengguna} 
                            {--password= : Kata sandi awal (opsional)}';

    /**
     * Deskripsi perintah konsol.
     */
    protected $description = 'Membuat akun pengguna baru untuk aplikasi HYBRID 20';

    /**
     * Eksekusi perintah konsol.
     */
    public function handle(): int
    {
        $name = $this->argument('name') ?: $this->ask('Masukkan nama pengguna');
        $email = $this->argument('email') ?: $this->ask('Masukkan alamat email');

        $password = $this->option('password');
        if (! $password) {
            $password = $this->secret('Masukkan kata sandi');
            $passwordConfirm = $this->secret('Konfirmasi kata sandi');

            if ($password !== $passwordConfirm) {
                $this->error('Konfirmasi kata sandi tidak cocok.');
                return self::FAILURE;
            }
        }

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $this->info("Pengguna [{$user->name}] ({$user->email}) berhasil dibuat.");

        return self::SUCCESS;
    }
}
