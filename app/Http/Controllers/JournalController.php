<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class JournalController extends Controller
{
    private function getCalendarData(Request $request)
    {
        $date = Carbon::now();
        if ($request->has('month') && $request->has('year')) {
            $date = Carbon::createFromDate($request->year, $request->month, 1);
        }
        if ($request->has('date')) {
             $date = Carbon::parse($request->date);
        }

        $currentMonth = $date->format('F');
        $currentYear = $date->year;
        $monthInt = $date->month;
        $prevDate = $date->copy()->subMonth();
        $nextDate = $date->copy()->addMonth();
        $daysInMonth = $date->daysInMonth;
        $startDayOfWeek = $date->copy()->startOfMonth()->dayOfWeekIso - 1; 

        $entries = Entry::whereYear('entry_date', $currentYear)
                        ->whereMonth('entry_date', $monthInt)
                        ->get()
                        ->keyBy(function($item) {
                            return $item->entry_date->format('j'); 
                        });

        return compact('currentMonth', 'currentYear', 'monthInt', 'prevDate', 'nextDate', 'daysInMonth', 'startDayOfWeek', 'entries');
    }

    public function index(Request $request)
    {
        $data = $this->getCalendarData($request);
        return view('journal.calendar', $data);
    }

    // --- PERUBAHAN UTAMA DI SINI ---
    public function create(Request $request)
    {
        $clickedDate = $request->query('date') ? Carbon::parse($request->query('date')) : now();
        $existingEntry = Entry::whereDate('entry_date', $clickedDate)->first();

        // Mengarahkan ke view 'journal.create' yang baru, bukan 'journal.calendar'
        return view('journal.create', [
            'selectedDate' => $clickedDate,
            'entryToEdit' => $existingEntry
        ]);
    }
    // --------------------------------

    // ... method lainnya tetap sama ...

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'rating' => 'required|integer|min:1|max:10',
            'mood' => 'nullable|string',
            'weather' => 'nullable|string', // Validasi baru
            'photos.*' => 'nullable|image|max:5120',
        ]);

        $entry = Entry::firstOrNew(['entry_date' => $request->date]);
        
        $entry->mood = $request->mood;
        $entry->weather = $request->weather; // Simpan weather
        $entry->rating = $request->rating;
        $entry->positive_highlight = $request->positive;
        $entry->negative_reflection = $request->negative;
        $entry->gratitude = $request->gratitude; // Simpan gratitude
        $entry->goals = $request->goals; // Simpan goals
        $entry->affirmations = $request->affirmations; // Simpan affirmations

        // --- LOGIC FOTO (TETAP SAMA SEPERTI SEBELUMNYA) ---
        $currentPhotos = $entry->photo_paths ?? [];

        if ($request->has('remove_photos')) {
            $photosToRemove = $request->remove_photos;
            foreach ($photosToRemove as $pathToRemove) {
                Storage::disk('public')->delete($pathToRemove);
            }
            $currentPhotos = array_values(array_diff($currentPhotos, $photosToRemove));
        }

        if ($request->hasFile('photos')) {
            foreach($request->file('photos') as $photo) {
                if (count($currentPhotos) < 4) {
                    $path = $photo->store('photos', 'public');
                    $currentPhotos[] = $path;
                }
            }
        }

        $entry->photo_paths = $currentPhotos;
        $entry->save();

        $dateObj = Carbon::parse($request->date);
        return redirect()->route('journal.index', [
            'month' => $dateObj->month, 
            'year' => $dateObj->year
        ])->with('success', 'Journal updated!');
    }

    public function gallery()
    {
        $entriesWithPhotos = Entry::whereNotNull('photo_paths')
                                ->where('photo_paths', '!=', '[]') 
                                ->orderBy('entry_date', 'desc')
                                ->get();
        return view('journal.gallery', compact('entriesWithPhotos'));
    }

    public function mood(Request $request)
    {
        $range = $request->query('range', 'month');
        $date = Carbon::now();
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        switch ($range) {
            case 'week': $start = $date->copy()->startOfWeek(); $end = $date->copy()->endOfWeek(); break;
            case 'year': $start = $date->copy()->startOfYear(); $end = $date->copy()->endOfYear(); break;
            case 'month': default: $start = $date->copy()->startOfMonth(); $end = $date->copy()->endOfMonth(); break;
        }
        
        $entries = Entry::whereBetween('entry_date', [$start, $end])->orderBy('entry_date', 'asc')->get();

        $totalEntries = $entries->count();
        $avgRating = $totalEntries > 0 ? round($entries->avg('rating'), 1) : 0;
        $maxRating = $entries->max('rating') ?? 0;
        $minRating = $entries->min('rating') ?? 0;

        $moodCounts = $entries->groupBy('mood')->map->count();
        $moodPercentages = [];
        $moodColors = ['happy' => '#34C759', 'neutral' => '#5AC8FA', 'sad' => '#5856D6', 'anxious' => '#FF9500', 'excited' => '#AF52DE'];
        
        foreach($moodColors as $mood => $color) {
            $count = $moodCounts->get($mood) ?? 0;
            $percent = $totalEntries > 0 ? ($count / $totalEntries) * 100 : 0;
            $moodPercentages[$mood] = ['count' => $count, 'percent' => round($percent), 'color' => $color];
        }

        $chartPoints = [];
        $svgPath = "";
        
        if ($totalEntries > 1) {
            $startTime = $start->timestamp;
            $endTime = $end->timestamp;
            $totalDuration = $endTime - $startTime;
            if($totalDuration == 0) $totalDuration = 1;
            $points = [];
            foreach($entries as $entry) {
                $entryTime = $entry->entry_date->timestamp;
                $x = (($entryTime - $startTime) / $totalDuration) * 1000;
                $y = 250 - (($entry->rating - 1) * 22.2); 
                $points[] = "$x,$y";
                $chartPoints[] = ['x' => $x, 'y' => $y, 'rating' => $entry->rating, 'date' => $entry->entry_date->format('M d'), 'mood' => $entry->mood, 'color' => $moodColors[$entry->mood] ?? '#999'];
            }
            $svgPath = "M" . $points[0]; 
            for ($i = 1; $i < count($points); $i++) { $svgPath .= " L" . $points[$i]; }
        }

        $insight = "Keep logging your days to see patterns!";
        return view('journal.mood', compact('entries', 'avgRating', 'maxRating', 'minRating', 'totalEntries', 'moodPercentages', 'chartPoints', 'svgPath', 'insight', 'range'));
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = collect();
        if ($query) {
            $results = Entry::where('positive_highlight', 'LIKE', "%{$query}%")
                            ->orWhere('negative_reflection', 'LIKE', "%{$query}%")
                            ->orWhere('mood', 'LIKE', "%{$query}%")
                            ->orderBy('entry_date', 'desc')
                            ->get();
        }
        return view('journal.search', compact('results', 'query'));
    }
}