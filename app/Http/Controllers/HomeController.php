<?php

namespace App\Http\Controllers;

use App\Enums\ArtworkStatus;
use App\Models\Artwork;
use App\Models\Category;
use App\Models\Event;
use App\Models\OrganizationProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $latest = Artwork::published()->with(['user', 'category'])->latest('curated_at')->take(5)->get();
        $nominees = Artwork::weeklyNominees();

        return view('home', [
            'profile' => OrganizationProfile::current(),
            // slider di atas: nominasi foto mingguan, atau karya terbaru bila belum ada nominasi
            'slides' => $nominees['artworks']->isNotEmpty() ? $nominees['artworks'] : $latest,
            'nomineeWeek' => $nominees['week'],
            'latest' => $latest,
            'events' => Event::upcoming()->take(6)->get(),
        ]);
    }

    public function profile(): View
    {
        return view('profile', ['profile' => OrganizationProfile::current()]);
    }

    public function gallery(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $artworks = Artwork::published()->with(['user', 'category'])
            ->when($search !== '', fn ($query) => $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))))
            ->latest('curated_at')
            ->take(60)
            ->get();

        return view('gallery', [
            'profile' => OrganizationProfile::current(),
            'artworks' => $artworks,
            'categories' => Category::whereIn('id', $artworks->pluck('category_id')->unique())->orderBy('name')->get(),
            'search' => $search,
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
