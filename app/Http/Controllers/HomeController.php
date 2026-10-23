<?php

namespace App\Http\Controllers;

use App\Enums\ArtworkStatus;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Artwork;
use App\Models\Category;
use App\Models\Event;
use App\Models\OrganizationProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $artworks = Artwork::published()->with(['user', 'category'])->latest('curated_at')->take(24)->get();

        return view('home', [
            'profile' => OrganizationProfile::current(),
            'artworks' => $artworks,
            'categories' => Category::whereIn('id', $artworks->pluck('category_id')->unique())->orderBy('name')->get(),
            'events' => Event::upcoming()->take(6)->get(),
            // angka ringkas di bagian atas halaman
            'stats' => [
                'artworks' => Artwork::published()->count(),
                'events' => Event::upcoming()->count(),
                'members' => User::where('role', Role::Member)->where('status', UserStatus::Active)->count(),
            ],
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
