<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Pendaftaran kaderisasi dari website, khusus mahasiswa yang belum pernah ikut kaderisasi. */
class RegistrationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        // izinkan nomor diketik dengan spasi atau tanda strip, mis. 0812-3456-7890
        $request->merge(['phone' => preg_replace('/[\s-]/', '', (string) $request->input('phone'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'npm' => [
                'required',
                'regex:/^\d{6,20}$/',
                Rule::unique('users', 'npm'),
                Rule::unique('registrations', 'npm'),
            ],
            'faculty' => ['required', Rule::in(config('ukm.faculties'))],
            'phone' => ['required', 'regex:/^(\+62|62|0)8\d{7,12}$/'],
            'not_cadre_yet' => ['accepted'],
        ], [
            'npm.regex' => 'NPM harus berupa 6–20 digit angka.',
            'npm.unique' => 'NPM ini sudah terdaftar. Hubungi pengurus jika merasa belum pernah mendaftar.',
            'phone.regex' => 'Nomor WhatsApp tidak valid (contoh: 081234567890).',
            'not_cadre_yet.accepted' => 'Pendaftaran ini khusus mahasiswa yang belum pernah mengikuti kaderisasi.',
        ]);

        Registration::create([
            'name' => $data['name'],
            'npm' => $data['npm'],
            'faculty' => $data['faculty'],
            'phone' => $data['phone'],
        ]);

        return redirect()->to(route('home').'#daftar')
            ->with('registered', 'Terima kasih! Pendaftaranmu sudah kami terima. Pengurus akan menghubungimu lewat WhatsApp untuk info jadwal kaderisasi.');
    }
}
