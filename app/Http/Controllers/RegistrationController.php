<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Pendaftaran anggota baru dari website; akun aktif setelah disetujui Admin UKM. */
class RegistrationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'npm' => ['required', 'regex:/^\d{6,20}$/', 'unique:users,npm'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'regex:/^(\+62|62|0)8\d{7,12}$/'],
            'faculty' => ['required', 'string', 'max:255'],
            'batch_year' => ['required', 'integer', 'between:2000,'.(now()->year + 1)],
            'role' => ['required', Rule::in([Role::Member->value, Role::AnggotaLain->value])],
            'origin_ukm' => ['required_if:role,'.Role::AnggotaLain->value, 'nullable', 'string', 'max:255'],
            'photo' => ['required', 'image', 'max:2048'],
            'join_reason' => ['nullable', 'string', 'max:1000'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'npm.regex' => 'NPM harus berupa 6–20 digit angka.',
            'npm.unique' => 'NPM ini sudah terdaftar.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'phone.regex' => 'Nomor WhatsApp tidak valid (contoh: 081234567890).',
            'origin_ukm.required_if' => 'Asal UKM wajib diisi untuk anggota UKM lain.',
        ]);

        $data['photo'] = $request->file('photo')->store('users', 'public');
        $data['status'] = UserStatus::Pending;

        User::create($data);

        return redirect()->to(route('home').'#daftar')
            ->with('registered', 'Terima kasih! Pendaftaranmu sudah kami terima dan akan diverifikasi pengurus. Setelah disetujui, kamu bisa masuk ke panel anggota dengan email & password yang kamu buat.');
    }
}
