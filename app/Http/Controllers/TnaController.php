<?php

namespace App\Http\Controllers;

use App\Models\category;
use App\Models\Tna;
use App\Models\TrainingProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TnaController extends Controller
{
    public function create()
    {
        return view('tna.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'tna_document' => 'required|file|mimes:pdf,doc,docx|max:2048',
            'tna_year' => 'required|numeric|digits:4',
            'tna_min_budget' => 'required|numeric',
            'tna_remarks' => 'nullable|string',
        ]);

        if ($request->hasFile('tna_document')) {
            $file = $request->file('tna_document');
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('uploads', $filename, 'public');
        }

        $tna = Tna::create([
            'tna_year' => $request->tna_year,
            'tna_document' => $filePath ?? '',
            'tna_min_budget' => $request->tna_min_budget,
            'tna_remarks' => $request->tna_remarks,
            'tna_realization' => 0,
        ]);
        
        // 2. Ambil category_id yang terkait dengan tna_id tersebut
        $categoryIds = Category::where('tna_id', $tna->id)->pluck('id');
        
        // 3. Hitung total tp_realization dari training_programs yang termasuk dalam category_id tadi
        $totalRealization = TrainingProgram::whereIn('category_id', $categoryIds)->sum('tp_realization');
        
        // 4. Update nilai tna_realization pada TNA
        $tna->update(['tna_realization' => $totalRealization]);
        
        return redirect()->route('category.create', ['tna_id' => $tna->id])
        ->with('success', 'TNA berhasil disimpan!');
    }

    public function index(Request $request)
{
    $query = Tna::query();

    if ($request->filled('tna_year')) {
        $query->where('tna_year', $request->tna_year);
    }


    $tnas = $query->orderBy('tna_year', 'desc')->get(); // 👈 Ganti dari ->latest() ke ->orderBy()

    return view('tna.index', compact('tnas'));
}

public function destroy($tna_id)
{
    $tna = Tna::findOrFail($tna_id);

    // Hapus file jika ada
    if ($tna->tna_document) {
        Storage::disk('public')->delete($tna->tna_document);
    }

    $tna->delete();

    return redirect()->route('tna.index')->with('success', 'TNA berhasil dihapus.');
    
}

// Menampilkan form edit TNA
public function edit($tna_id)
{
    $tna = Tna::findOrFail($tna_id);

    // Ambil kategori yang berhubungan dengan TNA
    $categories = category::where('tna_id', $tna_id)->get();

    // Ambil program pelatihan berdasarkan kategori
    $trainingPrograms = TrainingProgram::whereIn('category_id', $categories->pluck('id'))->get();

    return view('tna.edit', compact('tna', 'categories', 'trainingPrograms'));
}




// Menyimpan perubahan TNA
public function update(Request $request, $id)
{
    switch ($request->form_type) {
        case 'tna':
            $tna = Tna::findOrFail($id);
            $tna->tna_year = $request->tna_year;
            $tna->tna_min_budget = $request->tna_min_budget;
            $tna->tna_remarks = $request->tna_remarks;

            if ($request->hasFile('tna_document')) {
                $path = $request->file('tna_document')->store('tna_documents', 'public');
                $tna->tna_document = $path;
            }

            $tna->save();
            return back()->with('success', 'Data TNA berhasil diperbarui.');
        
        case 'category':
            $category = Category::findOrFail($request->category_id);
            $category->name = $request->name;
            $category->description = $request->description;
            $category->save();
            return back()->with('success', 'Kategori berhasil diperbarui.');

        case 'program':
            $program = TrainingProgram::findOrFail($request->program_id);
            $program->tp_name = $request->tp_name;
            $program->tp_duration = $request->tp_duration;
            $program->tp_invest = $request->tp_invest;
            $program->tp_realization = $request->tp_realization;
            $program->save();
            return back()->with('success', 'Program pelatihan berhasil diperbarui.');

        default:
            return back()->with('error', 'Jenis form tidak dikenali.');
    }
}

public function show(Request $request, $tnaId)
{
    $tna = Tna::with('category.training_program.programs')->findOrFail($tnaId);

    $allCategories = $tna->category;

    // Ambil semua training programs (unfiltered)
    $allPrograms = $allCategories->flatMap->training_program;

    // Filter by search & category
    $filteredPrograms = $allPrograms;

    if ($request->filled('search')) {
        $filteredPrograms = $filteredPrograms->filter(function ($program) use ($request) {
            return str_contains(strtolower($program->tp_name), strtolower($request->search));
        });
    }

    if ($request->filled('category')) {
        $filteredPrograms = $filteredPrograms->where('category_id', $request->category);
    }

    // Ambil kategori yang digunakan oleh hasil filter
    $filteredCategoryIds = $filteredPrograms->pluck('category_id')->unique();
    $filteredCategories = $allCategories->whereIn('id', $filteredCategoryIds);

    return view('tna.view', [
        'tna' => $tna,
        'allCategories' => $allCategories,
        'selectedCategoryId' => $request->category,
        'categories' => $filteredCategories,
        'trainingPrograms' => $filteredPrograms
    ]);
}


}
