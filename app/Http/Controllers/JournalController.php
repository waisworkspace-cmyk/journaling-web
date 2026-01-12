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
    public function mood(Request $request)
    {
        // 1. Tentukan Rentang Waktu (Dinamis berdasarkan Request)
        $range = $request->query('range', 'month'); // Default 'month'
        $date = Carbon::now();
        $start = $date->copy()->startOfMonth();
        $end = $date->copy()->endOfMonth();

        switch ($range) {
            case 'week':
                $start = $date->copy()->startOfWeek();
                $end = $date->copy()->endOfWeek();
                break;
            case 'year':
                $start = $date->copy()->startOfYear();
                $end = $date->copy()->endOfYear();
                break;
            case 'month':
            default:
                $start = $date->copy()->startOfMonth();
                $end = $date->copy()->endOfMonth();
                break;
        }
        
        // 2. Ambil Data Entries
        $entries = Entry::whereBetween('entry_date', [$start, $end])
                        ->orderBy('entry_date', 'asc')
                        ->get();

        // 3. Kalkulasi Statistik Dasar
        $totalEntries = $entries->count();
        $avgRating = $totalEntries > 0 ? round($entries->avg('rating'), 1) : 0;
        $maxRating = $entries->max('rating') ?? 0;
        $minRating = $entries->min('rating') ?? 0;

        // 4. Kalkulasi Distribusi Mood
        $moodCounts = $entries->groupBy('mood')->map->count();
        $moodPercentages = [];
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
        $chartPoints = [];
        $svgPath = "";
        
        if ($totalEntries > 1) {
            // Kita gunakan timestamp atau day of year untuk menghitung jarak X yang akurat
            // Terutama jika rentang waktu lintas bulan/tahun
            $startTime = $start->timestamp;
            $endTime = $end->timestamp;
            $totalDuration = $endTime - $startTime;
            
            if($totalDuration == 0) $totalDuration = 1;

            $points = [];
            foreach($entries as $entry) {
                // Normalisasi X berdasarkan waktu (0 - 1000)
                $entryTime = $entry->entry_date->timestamp;
                $x = (($entryTime - $startTime) / $totalDuration) * 1000;
                
                // Normalisasi Y (Rating 1-10 menjadi Pixel 250-50)
                $y = 250 - (($entry->rating - 1) * 22.2); 
                
                $points[] = "$x,$y";
                
                $chartPoints[] = [
                    'x' => $x,
                    'y' => $y,
                    'rating' => $entry->rating,
                    'date' => $entry->entry_date->format('M d'),
                    'mood' => $entry->mood,
                    'color' => $moodColors[$entry->mood] ?? '#999'
                ];
            }
            
            $svgPath = "M" . $points[0]; 
            for ($i = 1; $i < count($points); $i++) {
                $svgPath .= " L" . $points[$i];
            }
        }

        // 6. Insight Sederhana (Pastikan use DB ada di atas file jika memakai DB::raw)
        $insight = "Keep logging your days to see patterns!";
        // ... (Logika insight biarkan sama atau sesuaikan jika perlu)

        // Tambahkan variabel 'range' ke compact agar bisa dipakai di View
        return view('journal.mood', compact(
            'entries', 'avgRating', 'maxRating', 'minRating', 
            'totalEntries', 'moodPercentages', 'chartPoints', 
            'svgPath', 'insight', 'range'
        ));
    }
    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = collect(); // Koleksi kosong default

        if ($query) {
            // Mencari di mood, positive_highlight, atau negative_reflection
            $results = Entry::where('positive_highlight', 'LIKE', "%{$query}%")
                            ->orWhere('negative_reflection', 'LIKE', "%{$query}%")
                            ->orWhere('mood', 'LIKE', "%{$query}%")
                            ->orderBy('entry_date', 'desc')
                            ->get();
        }

        return view('journal.search', compact('results', 'query'));
    }
}