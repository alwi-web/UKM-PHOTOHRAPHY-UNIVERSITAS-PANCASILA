<?php

namespace App\Http\Controllers;

use App\Enums\ArtworkStatus;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Artwork;
use App\Models\Category;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $artworks = Artwork::published()->with(['user', 'category'])->latest('curated_at')->take(24)->get();

        return view('home', [
            'artworks' => $artworks,
            'categories' => Category::whereIn('id', $artworks->pluck('category_id')->unique())->orderBy('name')->get(),
            'events' => Event::upcoming()->take(6)->get(),
            'board' => User::where('status', UserStatus::Active)
                ->whereIn('role', [Role::AdminUkm, Role::AdminInventaris, Role::Kurator])
                ->orderByRaw("case role when 'admin_ukm' then 1 when 'admin_inventaris' then 2 else 3 end")
                ->take(8)
                ->get(),
        ]);
    }

    /** Dipanggil saat pengunjung membuka foto di lightbox, untuk insight karya. */
    public function recordView(Artwork $artwork): JsonResponse
    {
        abort_unless($artwork->is_featured && $artwork->status === ArtworkStatus::Approved, 404);

        $artwork->increment('views');

        return response()->json(['views' => $artwork->views]);
    }
}
