<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\TrainingProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function create(Request $request)
    {
        $category = null;
        if ($request->has('category_id')) {
            $category = Category::find($request->get('category_id'));
        }

        return view('category.create', [
            'category' => $category,
            'tnaId' => $request->get('tna_id'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tna_id' => 'required|exists:tna,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $category = Category::create($validated);

        return back()->with('success', 'Kategori berhasil dibuat!')
            ->withInput(['tna_id' => $validated['tna_id']]);
    }

    public function storeTrainingProgram(Request $request)
    {
        $validated = $request->validate(
            [
            'category_id' => 'required|exists:categories,id',
            'training_programs' => 'required|array',
            'training_programs.*.tp_name' => 'required|string|max:255',
            'training_programs.*.tp_duration' => 'required|date_format:H:i',
            'training_programs.*.tp_invest' => 'nullable|numeric',
            'training_programs.*.tp_realization' => 'nullable|numeric',
            ]);

        DB::beginTransaction();
        try {
            foreach ($validated['training_programs'] as $tpData) {
                $tpData['category_id'] = $validated['category_id'];
                TrainingProgram::create($tpData);
            }

            DB::commit();
            return redirect()
                ->route('tna.index', ['category_id' => $validated['category_id']])
                ->with('success', 'Program pelatihan berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan: ' . $e->getMessage()])
                         ->withInput();
        }
    }

    /**
     * Store multiple categories with their training programs
     */
    public function storeMultiple(Request $request)
    {
        $validated = $request->validate([
            'tna_id' => 'required|exists:tna,id',
            'categories' => 'required|array',
            'categories.*.name' => 'required|string|max:255',
            'categories.*.training_programs' => 'nullable|array',
            'categories.*.training_programs.*.tp_name' => 'required|string|max:255',
            'categories.*.training_programs.*.tp_duration' => 'required|date_format:H:i',
            'categories.*.training_programs.*.tp_invest' => 'nullable|numeric',
        ]);

        DB::beginTransaction();

        try {
            foreach ($validated['categories'] as $catData) {
                $category = Category::create([
                    'tna_id' => $validated['tna_id'],
                    'name' => $catData['name'],
                ]);

                if (!empty($catData['training_programs'])) {
                    foreach ($catData['training_programs'] as $tpData) {
                        $tpData['category_id'] = $category->id;
                        TrainingProgram::create($tpData);
                    }
                }
            }

            DB::commit();
            return redirect()->route('tna.index')->with('success', 'Semua kategori & program berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()])
                         ->withInput();
        }
    }

    public function storeSingleTrainingProgram(Request $request)
{
    $validated = $request->validate([
        'category_id' => 'required|exists:categories,id',
        'tp_name' => 'required|string|max:255',
        'tp_duration' => 'required|date_format:H:i',
        'tp_invest' => 'nullable|numeric',
    ]);

    DB::beginTransaction();
    try {
        TrainingProgram::create($validated);

        DB::commit();
        return redirect()
            ->route('tna.index', ['category_id' => $validated['category_id']])
            ->with('success', 'Program pelatihan berhasil ditambahkan!');
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Failed to create training program: ' . $e->getMessage());
        return back()->withErrors(['error' => 'Gagal menyimpan: ' . $e->getMessage()])
                     ->withInput();
    }
}
}
