<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Entry;

class JournalController extends Controller
{
    // Halaman Calendar (Halaman Utama Jurnal)
    public function index()
    {
        // Ambil semua data entry untuk ditampilkan di kalender (logika sederhana)
        $entries = Entry::all();
        return view('journal.calendar', compact('entries'));
    }

    // Halaman Buat Entry Baru
    public function create()
    {
        return view('journal.create');
    }

    // Simpan Data ke Database
    public function store(Request $request)
    {
        // Validasi dan Simpan (Sederhana)
        $entry = new Entry();
        $entry->entry_date = now();
        $entry->mood = $request->mood; // Anda perlu sesuaikan input name di HTML nanti
        $entry->rating = $request->rating;
        $entry->positive_highlight = $request->positive;
        $entry->negative_reflection = $request->negative;
        $entry->save();

        return redirect()->route('journal.index');
    }

    // Halaman Gallery
    public function gallery()
    {
        return view('journal.gallery');
    }

    // Halaman Mood Analytics
    public function mood()
    {
        return view('journal.mood');
    }

    // Halaman Search
    public function search()
    {
        return view('journal.search');
    }
}