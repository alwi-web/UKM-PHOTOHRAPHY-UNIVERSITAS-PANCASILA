<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Livewire\Livewire;
use Symfony\Component\HttpFoundation\Response;

/** User yang membuka panel milik role lain diarahkan ke panelnya sendiri. */
class RedirectToOwnPanel
{
    public function handle(Request $request, Closure $next): Response
    {
        // request Livewire (widget, tabel, aksi) tidak dialihkan; aksesnya tetap dijaga policy
        if (Livewire::isLivewireRequest()) {
            return $next($request);
        }

        $own = $request->user()?->role->panel();

        if ($own && $own !== Filament::getCurrentPanel()?->getId()) {
            return redirect()->to(Filament::getPanel($own)->getUrl());
        }

        return $next($request);
    }
}
