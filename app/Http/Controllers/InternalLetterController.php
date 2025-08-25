<?php

namespace App\Http\Controllers;

use App\Models\InternalLetter;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InternalLetterController extends Controller
{
    public function create()
    {
        $programs = Program::all();
        return view('letters.internal.create', compact('programs'));
    }
    
    public function store(Request $request)
    {
    $validated = $request->validate([
        'letter_date' => 'required|date',
        'subject' => 'required|string|max:255',
        'program_id' => 'nullable|exists:programs,id',
        'letter_document' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
    ]);

    $validated['user_id'] = auth()->id();

    // Generate ID kosong
    $newId = $this->getNextAvailableId(InternalLetter::class);

    // Hitung romawi bulan dan tahun
    $date = \Carbon\Carbon::parse($validated['letter_date']);
    $romawiBulan = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'][$date->month - 1];
    $tahun = $date->year;

    // Generate letter_no
    $letterNo = "$newId/MEMO DIKLAT/$romawiBulan/$tahun";

    // Simpan dokumen jika ada
    if ($request->hasFile('letter_document')) {
        $file = $request->file('letter_document');
        $filename = uniqid().'_'.$file->getClientOriginalName();
        $validated['letter_document'] = $file->storeAs('letters/internal', $filename, 'public');
    }

    // Simpan surat
    $letter = new InternalLetter($validated);
    $letter->id = $newId;
    $letter->letter_no = $letterNo;
    $letter->save();

    return redirect()->route('letters.index')->with('success', 'Surat berhasil disimpan.');
}

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
    $letter = InternalLetter::findOrFail($id);
    $programs = Program::all();
    return view('letters.internal.edit', compact('letter', 'programs'));
}

public function update(Request $request, $id)
{
    $letter = InternalLetter::findOrFail($id);

    $validated = $request->validate([
        'letter_date' => 'required|date',
        'subject' => 'required|string|max:255',
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

    return redirect()->route('external-letters.index')->with('success', 'Surat berhasil diperbarui.');
}

public function destroy($id)
{
    $letter = InternalLetter::findOrFail($id);

    if ($letter->letter_document) {
        return back()->with('error', 'Tidak bisa menghapus surat yang sudah memiliki dokumen.');
    }

    $letter->delete();

    return back()->with('success', 'Surat berhasil dihapus.');
}


}
