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
        // ... (KODE INI TETAP SAMA SEPERTI SEBELUMNYA) ...
        // Copy paste function getCalendarData dari jawaban sebelumnya
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

    // ... imports tetap sama

public function create(Request $request)
{
    $data = $this->getCalendarData($request);
    $clickedDate = $request->query('date') ? Carbon::parse($request->query('date')) : now();
    $existingEntry = Entry::whereDate('entry_date', $clickedDate)->first();

    $data['selectedDate'] = $clickedDate;
    $data['entryToEdit'] = $existingEntry;

    // LOGIKA BARU:
    // 1. Jika Entry ADA dan TIDAK ADA parameter 'edit=1' di URL -> Tampilkan Preview
    // 2. Jika Entry TIDAK ADA -> Langsung tampilkan Form Create
    // 3. Jika Entry ADA dan ADA parameter 'edit=1' -> Tampilkan Form Edit
    
    if ($existingEntry && !$request->has('edit')) {
        $data['showPreviewModal'] = true; // Flag baru untuk preview
        $data['showCreateModal'] = false;
    } else {
        $data['showPreviewModal'] = false;
        $data['showCreateModal'] = true;  // Flag lama untuk form
    }

    return view('journal.calendar', $data);
}

// ... method store dan lainnya tetap sama

    // UPDATE LOGIKA STORE UNTUK MULTIPLE PHOTOS & DELETE
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'rating' => 'required|integer|min:1|max:10',
            'mood' => 'nullable|string',
            'photos.*' => 'nullable|image|max:5120', // Validasi array foto
        ]);

        $entry = Entry::firstOrNew(['entry_date' => $request->date]);
        
        $entry->mood = $request->mood;
        $entry->rating = $request->rating;
        $entry->positive_highlight = $request->positive;
        $entry->negative_reflection = $request->negative;

        // 1. Ambil foto lama (sebagai array)
        $currentPhotos = $entry->photo_paths ?? [];

        // 2. Proses Penghapusan Foto Lama
        if ($request->has('remove_photos')) {
            $photosToRemove = $request->remove_photos; // Array path foto yang mau dihapus
            
            // Hapus file fisik
            foreach ($photosToRemove as $pathToRemove) {
                Storage::disk('public')->delete($pathToRemove);
            }

            // Filter array: simpan hanya yang TIDAK ada di list hapus
            $currentPhotos = array_values(array_diff($currentPhotos, $photosToRemove));
        }

        // 3. Proses Upload Foto Baru
        if ($request->hasFile('photos')) {
            foreach($request->file('photos') as $photo) {
                // Cek limit max 4
                if (count($currentPhotos) < 4) {
                    $path = $photo->store('photos', 'public');
                    $currentPhotos[] = $path;
                }
            }
        }

        // Simpan array gabungan
        $entry->photo_paths = $currentPhotos;
        $entry->save();

        $dateObj = Carbon::parse($request->date);
        return redirect()->route('journal.index', [
            'month' => $dateObj->month, 
            'year' => $dateObj->year
        ])->with('success', 'Journal updated!');
    }

    // ... imports

public function gallery()
{
    // Ambil semua entry yang kolom photo_paths-nya tidak null dan isinya tidak kosong
    // Urutkan dari tanggal terbaru
    $entriesWithPhotos = Entry::whereNotNull('photo_paths')
                            ->where('photo_paths', '!=', '[]') 
                            ->orderBy('entry_date', 'desc')
                            ->get();

    return view('journal.gallery', compact('entriesWithPhotos'));
}

// ... functions lain
    // ... method index, create, store, gallery tetap sama ...

    public function mood(Request $request)
    {
        // 1. Tentukan Rentang Waktu (Default: Bulan Ini)
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();
        
        // 2. Ambil Data Entries
        $entries = Entry::whereBetween('entry_date', [$start, $end])
                        ->orderBy('entry_date', 'asc')
                        ->get();

        // 3. Kalkulasi Statistik Dasar
        $totalEntries = $entries->count();
        $avgRating = $totalEntries > 0 ? round($entries->avg('rating'), 1) : 0;
        $maxRating = $entries->max('rating') ?? 0;
        $minRating = $entries->min('rating') ?? 0;

        // 4. Kalkulasi Distribusi Mood (Untuk Donut Chart)
        $moodCounts = $entries->groupBy('mood')->map->count();
        $moodPercentages = [];
        // Warna hardcoded sesuai desain HTML kamu
        $moodColors = [
            'happy' => '#34C759', 
            'neutral' => '#5AC8FA', 
            'sad' => '#5856D6', 
            'anxious' => '#FF9500', 
            'excited' => '#AF52DE'
        ];
        
        foreach($moodColors as $mood => $color) {
            $count = $moodCounts->get($mood) ?? 0;
            $percent = $totalEntries > 0 ? ($count / $totalEntries) * 100 : 0;
            $moodPercentages[$mood] = [
                'count' => $count,
                'percent' => round($percent),
                'color' => $color
            ];
        }

        // 5. Kalkulasi Koordinat SVG untuk Line Chart
        // Canvas SVG: Width 1000px, Height 300px
        // Y Axis: Rating 10 = y:50, Rating 1 = y:250
        $chartPoints = [];
        $svgPath = "";
        
        if ($totalEntries > 1) {
            $firstDay = $entries->first()->entry_date->day;
            $lastDay = $entries->last()->entry_date->day;
            $daySpan = $lastDay - $firstDay;
            if($daySpan == 0) $daySpan = 1;

            $points = [];
            foreach($entries as $entry) {
                // Normalisasi X (0 - 1000)
                $x = (($entry->entry_date->day - $firstDay) / $daySpan) * 1000;
                
                // Normalisasi Y (Rating 1-10 menjadi Pixel 250-50)
                // Rumus: 250 - ((rating - 1) * (200 / 9))
                $y = 250 - (($entry->rating - 1) * 22.2); 
                
                $points[] = "$x,$y";
                
                // Simpan data point untuk tooltip
                $chartPoints[] = [
                    'x' => $x,
                    'y' => $y,
                    'rating' => $entry->rating,
                    'date' => $entry->entry_date->format('M d'),
                    'mood' => $entry->mood,
                    'color' => $moodColors[$entry->mood] ?? '#999'
                ];
            }
            
            // Buat Path SVG (Smooth Curve logic sederhana)
            // Untuk simplisitas, kita pakai Polyline dulu atau curve manual
            // Disini saya pakai Line biasa agar akurat
            $svgPath = "M" . $points[0]; 
            for ($i = 1; $i < count($points); $i++) {
                $svgPath .= " L" . $points[$i];
            }
        }

        // 6. Insight Sederhana
        $insight = "Keep logging your days to see patterns!";
        if ($totalEntries > 5) {
            // Cari hari dengan rating rata-rata tertinggi
            $bestDay = Entry::select(DB::raw('DAYNAME(entry_date) as day'), DB::raw('avg(rating) as avg_rating'))
                ->groupBy('day')
                ->orderByDesc('avg_rating')
                ->first();
                
            if($bestDay) {
                $insight = "You tend to feel most energetic on <span class='font-semibold text-white'>{$bestDay->day}s</span>. Consider scheduling demanding tasks then.";
            }
        }

        return view('journal.mood', compact(
            'entries', 'avgRating', 'maxRating', 'minRating', 
            'totalEntries', 'moodPercentages', 'chartPoints', 
            'svgPath', 'insight'
        ));
    }
    public function search() { return view('journal.search'); }
}