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

    public function create(Request $request)
    {
        $data = $this->getCalendarData($request);
        $clickedDate = $request->query('date') ? Carbon::parse($request->query('date')) : now();
        $existingEntry = Entry::whereDate('entry_date', $clickedDate)->first();

        $data['showCreateModal'] = true;
        $data['selectedDate'] = $clickedDate;
        $data['entryToEdit'] = $existingEntry;

        return view('journal.calendar', $data);
    }

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

    public function gallery() { return view('journal.gallery'); }
    public function mood() { return view('journal.mood'); }
    public function search() { return view('journal.search'); }
}