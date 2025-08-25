<?php

namespace App\Http\Controllers;

use App\Models\CategoryBudget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryBudgetController extends Controller
{
    /**
     * Tampilkan form input budget.
     */
    // CategoryBudgetController.php
public function create($program_id)
{
    return view('category-budget.create', [
        'programId' => $program_id
    ]);
}

public function store(Request $request, $program_id)
{
    $validated = $request->validate([
        'category_budget.*.category_fee' => 'required|string',
        'category_budget.*.amount_fee' => 'required|numeric|min:0',
    ]);

    DB::beginTransaction();
    try {
        foreach ($validated['category_budget'] as $cbData) {
            $cbData['program_id'] = $program_id;
            CategoryBudget::create($cbData);
        }

        DB::commit();
        return redirect()->route('program.index')->with('success', 'Program Budget berhasil disimpan!');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors(['error' => 'Gagal menyimpan data: ' . $e->getMessage()])->withInput();
    }
}

}
