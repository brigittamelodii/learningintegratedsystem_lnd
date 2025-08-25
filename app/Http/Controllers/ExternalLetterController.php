<?php

namespace App\Http\Controllers;

use App\Models\ExternalLetter;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class ExternalLetterController extends Controller
{

  public function index(Request $request)
{
    $searchInternal = $request->search_internal;
    $searchExternal = $request->search_external;
    $yearInternal = $request->year_internal;
    $yearExternal = $request->year_external;
    $programInternal = $request->program_internal;
    $programExternal = $request->program_external;

    $programs = \App\Models\Program::orderBy('program_name')->get();

    // Internal Letters with pagination
    $internalLetters = \App\Models\InternalLetter::with(['user', 'program'])
        ->when($searchInternal, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('letter_no', 'like', "%$search%")
                  ->orWhere('subject', 'like', "%$search%")
                  ->orWhereHas('user', fn ($qu) => $qu->where('email', 'like', "%$search%"));
            });
        })
        ->when($yearInternal, fn($q) => $q->whereYear('letter_date', $yearInternal))
        ->when($programInternal, fn($q) => $q->where('program_id', $programInternal))
        ->orderByDesc('letter_date')
        ->paginate(10)
        ->appends($request->all());

    // External Letters with pagination
    $externalLetters = \App\Models\ExternalLetter::with(['user', 'program'])
        ->when($searchExternal, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('letter_no', 'like', "%$search%")
                  ->orWhere('subject', 'like', "%$search%")
                  ->orWhere('recipient_initial', 'like', "%$search%")
                  ->orWhereHas('user', fn ($qu) => $qu->where('email', 'like', "%$search%"));
            });
        })
        ->when($yearExternal, fn($q) => $q->whereYear('letter_date', $yearExternal))
        ->when($programExternal, fn($q) => $q->where('program_id', $programExternal))
        ->orderByDesc('letter_date')
        ->paginate(10)
        ->appends($request->all());

    return view('letters.index', compact('internalLetters', 'externalLetters', 'programs'));
}


    public function create()
    {
        $programs = Program::all();
        return view('letters.external.create', compact('programs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'letter_date' => 'required|date',
            'subject' => 'required|string|max:255',
            'recipient_initial' => 'required|string|max:10',
            'program_id' => 'nullable|exists:programs,id',
            'letter_document' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        $validated['user_id'] = auth()->id();

        // Generate ID kosong
        $newId = $this->getNextAvailableId(ExternalLetter::class);

        // Generate letter_no
        $date = Carbon::parse($validated['letter_date']);
        $romawi = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][$date->month - 1];
        $tahun = $date->year;
        $letterNo = "$newId/BWS L&D/{$validated['recipient_initial']}/$romawi/$tahun";

        // Simpan file jika ada
        if ($request->hasFile('letter_document')) {
            $file = $request->file('letter_document');
            $filename = uniqid().'_'.$file->getClientOriginalName();
            $validated['letter_document'] = $file->storeAs('letters/external', $filename, 'public');
        }

        // Simpan data
        $letter = new ExternalLetter($validated);
        $letter->id = $newId;
        $letter->letter_no = $letterNo;
        $letter->save();

        return redirect()->route('letters.index')->with('success', 'External letter created successfully.');
    }

    // Helper cari ID kosong terkecil
    protected function getNextAvailableId($model)
    {
        $ids = $model::orderBy('id')->pluck('id')->toArray();
        $expected = 1;
        foreach ($ids as $id) {
            if ($id != $expected) return $expected;
            $expected++;
        }
        return $expected;
    }

    public function edit($id)
{
    $letter = ExternalLetter::findOrFail($id);
    $programs = Program::all();
    return view('letters.external.edit', compact('letter', 'programs'));
}

public function update(Request $request, $id)
{
    $letter = ExternalLetter::findOrFail($id);

    $validated = $request->validate([
        'letter_date' => 'required|date',
        'subject' => 'required|string|max:255',
        'recipient_initial' => 'required|string|max:10',
        'program_id' => 'nullable|exists:programs,id',
        'letter_document' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
    ]);

    if ($request->hasFile('letter_document')) {
        // Hapus file lama kalau ada
        if ($letter->letter_document && Storage::disk('public')->exists($letter->letter_document)) {
            Storage::disk('public')->delete($letter->letter_document);
        }

        $file = $request->file('letter_document');
        $filename = uniqid() . '_' . $file->getClientOriginalName();
        $validated['letter_document'] = $file->storeAs('letters/external', $filename, 'public');
    }

    $letter->update($validated);

    return redirect()->route('letters.index')->with('success', 'Surat berhasil diperbarui.');
}

public function destroy($id)
{
    $letter = ExternalLetter::findOrFail($id);

    if ($letter->letter_document) {
        return back()->with('error', 'Tidak bisa menghapus surat yang sudah memiliki dokumen.');
    }

    $letter->delete();

    return back()->with('success', 'Surat berhasil dihapus.');
}


}
