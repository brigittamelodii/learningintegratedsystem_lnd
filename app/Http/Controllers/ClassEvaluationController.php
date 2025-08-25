<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassEvaluation;
use App\Models\Evaluation;
use Illuminate\Support\Facades\Storage;

class ClassEvaluationController extends Controller
{
    public function form($classId)
    {
        $evaluations = Evaluation::all();
        return view('evaluation.upload', compact('classId', 'evaluations'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'eval_scores' => 'required|array',
            'eval_scores.*' => 'required|numeric|min:1|max:5',
            'eval_doc' => 'nullable|file|mimes:xlsx,xls'
        ]);

        $categories = ['Materi', 'Pengajar', 'Kepanitiaan'];
        $classId = $request->class_id;

        // Upload file if available
        $filePath = null;
        if ($request->hasFile('eval_doc')) {
            $filePath = $request->file('eval_doc')->store('evaluation_docs', 'public');
        }

        foreach ($categories as $index => $cat) {
            $evaluation = Evaluation::where('eval_cat', $cat)->first();

            if ($evaluation) {
                ClassEvaluation::create([
                    'class_id' => $classId,
                    'eval_id' => $evaluation->id,
                    'eval_score' => $request->eval_scores[$index],
                    'eval_doc' => $filePath,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Evaluasi berhasil disimpan.');
    }
}
