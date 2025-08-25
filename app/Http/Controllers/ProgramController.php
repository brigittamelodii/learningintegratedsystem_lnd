<?php 
namespace App\Http\Controllers;

use App\Models\CategoryBi;
use App\Models\CategoryBudget;
use App\Models\classes;
use App\Models\evaluation;
use App\Models\ParticipantsPayment;
use App\Models\payments;
use App\Models\Program;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;


class ProgramController extends Controller
{
    public function index(Request $request)
{
    $bi_code = $request->input('bi_code');
    $tp_id = $request->input('tp_id');
    $user_id = $request->input('user_id'); // ganti dari pic_id
    $query = $request->input('query');

    $programs = Program::with(['training_program', 'user']); // Ganti relasi dari 'pic' ke 'user'

    if ($bi_code) {
        $programs->where('bi_code', $bi_code);
    }

    if ($tp_id) {
        $programs->where('tp_id', $tp_id);
    }

    if ($user_id) {
        $programs->where('user_id', $user_id);
    }

    if ($tna_years = $request->input('tna_year')) {
        $programs->whereHas('training_program.category.tna', function ($query) use ($tna_years) {
            $query->where('tna_year', $tna_years);
        });
    }

    $tna_years = TrainingProgram::with('category.tna')
        ->get()
        ->pluck('category.tna.tna_year')
        ->filter()
        ->unique()
        ->sort()
        ->values();

    if ($query) {
        $programs->where(function ($q) use ($query) {
            $q->where('program_name', 'like', "%{$query}%")
              ->orWhere('program_loc', 'like', "%{$query}%")
              ->orWhere('program_type', 'like', "%{$query}%")
              ->orWhereHas('training_program', function ($q2) use ($query) {
                  $q2->where('tp_name', 'like', "%{$query}%")
                     ->orWhereHas('category', function ($q3) use ($query) {
                         $q3->where('name', 'like', "%{$query}%");
                     });
              })
              ->orWhereHas('user', function ($q2) use ($query) {
                  $q2->where('email', 'like', "%{$query}%"); // atau bisa diganti display nama
              });
        });
    }

    $programs = $programs->orderBy('created_at', 'desc')->paginate(10);
    $programs->appends($request->all());

    $training_programs = TrainingProgram::with('category.tna')->get();
    $users = User::role('pic')->get(); // sesuai dengan format Spatie
    $category_bi = CategoryBi::all();

    return view('program.index', compact('programs', 'training_programs', 'users', 'category_bi', 'tna_years'));
}

public function create()
{
    $training_programs = TrainingProgram::with('category')->get();
    $category_bi = CategoryBi::all();
    $users = User::role('pic')->get(); // sesuai dengan format Spatie


    return view('program.create', compact('training_programs', 'category_bi', 'users'));
}

public function store(Request $request)
{
    Log::debug('Program Store Request Data:', $request->all());

    $validated = $request->validate([
        'program_name' => 'required|string|max:255',
        'program_loc' => 'required|string|max:255',
        'program_realization' => 'nullable|numeric',
        'program_type' => 'required|string|max:255',
        'program_act_type' => 'required|string|max:255',
        'program_unit_int' => 'required|string|max:255',
        'program_remarks' => 'nullable|string|max:255',
        'program_duration' => 'required|date_format:H:i',
        'tp_id' => 'required|exists:training_programs,id',
        'bi_code' => 'required|exists:category_bi,bi_code',
        'user_id' => 'required|exists:users,id',
        'facilitator_name' => 'nullable|string|max:255',
        'program_document' => 'nullable|file|mimes:pdf,doc,docx|max:2048', // ✅ Ubah jadi nullable
    ], [
        // Custom error messages
        'program_name.required' => 'Program name is required.',
        'program_loc.required' => 'Program location is required.',
        'program_duration.required' => 'Program duration is required.',
        'program_duration.date_format' => 'Duration must be in HH:MM format.',
        'tp_id.required' => 'Please select a training program.',
        'tp_id.exists' => 'Selected training program is invalid.',
        'bi_code.required' => 'Please select a BI Code.',
        'bi_code.exists' => 'Selected BI Code is invalid.',
        'user_id.required' => 'Please select a PIC.',
        'user_id.exists' => 'Selected PIC is invalid.',
        'program_document.mimes' => 'Document must be PDF, DOC, or DOCX format.',
        'program_document.max' => 'Document size must not exceed 2MB.',
    ]);

    DB::beginTransaction();
    try {
        // ✅ Handle file upload
        if($request->hasFile('program_document')) {
            $file = $request->file('program_document');
            $filename = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('program_documents', $filename, 'public');
            $validated['program_document'] = $filePath;
        }
        
        // ✅ Create program dengan validated data langsung
        $program = Program::create($validated);

        DB::commit();

        session(['program_id' => $program->id]);

        return redirect()->route('category-budget.create', ['program_id' => $program->id])
            ->with('success', 'Program created successfully. Proceed to Step 2.');
            
    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        Log::error('Validation failed: ' . json_encode($e->errors()));
        return back()->withErrors($e->errors())->withInput();
        
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to create program: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return back()->withErrors([
            'error' => 'Failed to create program: ' . $e->getMessage()
        ])->withInput();
    }
}



    public function cancel()
    {
        session()->forget('program_id');
        return redirect()->route('program.create')->with('success', 'Program creation canceled.');
    }

    public function edit($id)
{
    $program = Program::findOrFail($id);
    $training_programs = TrainingProgram::all();
    $category_budget = CategoryBudget::where('program_id', $id)->get();
    $category_bi = CategoryBi::all();
    $users = User::role('pic')->get(); // Ambil user yang punya role 'pic'

    return view('program.edit', compact('program', 'training_programs', 'category_bi', 'users', 'category_budget'));
}

    public function update(Request $request, $id)
{
    $request->validate([
        'program_name' => 'required|string|max:255',
        'program_loc' => 'required|string|max:255',
        'program_type' => 'required|string',
        'program_act_type' => 'required|string',
        'program_unit_int' => 'required|string',
        'program_remarks' => 'nullable|string',
        'program_duration' => 'nullable|date_format:H:i',
        'tp_id' => 'required|exists:training_programs,id',
        'bi_code' => 'required|exists:category_bi,bi_code',
        'user_id' => 'required|exists:users,id', // Ganti pic_id ke user_id
        'category_budget.*.id' => 'nullable|exists:category_budget,id',
        'category_budget.*.category_fee' => 'required|string',
        'category_budget.*.amount_fee' => 'required|numeric|min:0',
        'category_budget_new.*.category_fee' => 'required|string',
        'category_budget_new.*.amount_fee' => 'required|numeric|min:0',
    ]);

    $program = Program::findOrFail($id);

    DB::beginTransaction();

    try {
        $program->update([
            'program_name' => $request->program_name,
            'program_loc' => $request->program_loc,
            'program_realization' => $request->program_realization,
            'program_type' => $request->program_type,
            'program_act_type' => $request->program_act_type,
            'program_unit_int' => $request->program_unit_int,
            'program_remarks' => $request->program_remarks,
            'program_duration' => $request->program_duration,
            'tp_id' => $request->tp_id,
            'bi_code' => $request->bi_code,
            'user_id' => $request->user_id, // Ganti pic_id ke user_id
            'facilitator_name' => $request->facilitator_name,
        ]);

        // Update existing budgets
        foreach ($request->category_budget as $budgetInput) {
            if (!empty($budgetInput['id'])) {
                $categoryBudget = CategoryBudget::find($budgetInput['id']);
                if ($categoryBudget) {
                    $categoryBudget->update([
                        'category_fee' => $budgetInput['category_fee'],
                        'amount_fee' => $budgetInput['amount_fee'],
                    ]);
                }
            }
        }

        // Add new budgets
        if ($request->has('category_budget_new')) {
            foreach ($request->category_budget_new as $newBudget) {
                if (!empty($newBudget['category_fee']) && isset($newBudget['amount_fee'])) {
                    $program->category_budget()->create([
                        'category_fee' => $newBudget['category_fee'],
                        'amount_fee' => $newBudget['amount_fee'],
                    ]);
                }
            }
            $program->training_program->recalculateRealization();
        }

        DB::commit();

        return redirect()->route('program.index')->with('success', 'Program updated successfully.');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Failed to update program: ' . $e->getMessage());
        return back()->withErrors(['error' => 'Update failed: ' . $e->getMessage()])->withInput();
    }
}

    public function destroy($id)
    {
        $program = Program::findOrFail($id);
        $program->delete();

        return redirect()->route('program.index')->with('success', 'Program deleted successfully.');
    }

    public function show($id)
{
    $program = Program::findOrFail($id);
    $category_bi = CategoryBi::has('program')->get();
    $user = User::role('pic')->find($program->pic_id); // Ganti dari pic ke user
    $genPayment = payments::where('program_id', $program->id)->get();
    $partPayment = ParticipantsPayment::where('program_id', $program->id)->get();

    // Gabungkan dan total per category_fee
    $mergedPayments = $genPayment->concat($partPayment);
    $realizationByCategory = $mergedPayments->groupBy('category_fee')->map(function ($group) {
        return $group->sum('amount_fee');
    });

    // Ambil semua kelas dalam program
    $classes = Classes::where('program_id', $program->id)->with('participants')->paginate(5);
    $classIds = $classes->pluck('id'); // gunakan dari hasil $classes langsung

    // Ambil semua jenis evaluasi
    $allEvaluations = Evaluation::orderBy('eval_cat')->orderBy('id')->get();

    // Ambil rata-rata skor dari seluruh evaluasi pada kelas-kelas dalam program ini
    $averageScores = DB::table('class_evaluation')
        ->select('eval_id', DB::raw('AVG(eval_score) as average_score'))
        ->whereIn('class_id', $classIds)
        ->groupBy('eval_id')
        ->pluck('average_score', 'eval_id'); // [eval_id => avg]

    return view('program.view', compact(
        'program',
        'category_bi',
        'user',
        'classes',
        'realizationByCategory',
        'genPayment',
        'partPayment',
        'allEvaluations',
        'averageScores'
    ));
}

public function exportPdf($id)
{
    $program = Program::with([
        'training_program',
        'category_budget',
        'classes.participants',
        'payments' => function ($q) {
            $q->where('status', 'Approve');
        },
        'participants_payment' => function ($q) {
            $q->where('status', 'Approve');
        },
    ])->findOrFail($id);

    $genPayment = $program->payments;
    $partPayment = $program->participants_payment;

    $realization = $genPayment->concat($partPayment)->sum('amount_fee');

    // Evaluasi
    $classIds = $program->classes->pluck('id');
    $allEvaluations = Evaluation::orderBy('eval_cat')->orderBy('id')->get();
    $averageScores = DB::table('class_evaluation')
        ->select('eval_id', DB::raw('AVG(eval_score) as average_score'))
        ->whereIn('class_id', $classIds)
        ->groupBy('eval_id')
        ->pluck('average_score', 'eval_id');

    $pdf = Pdf::loadView('program.pdf', compact('program', 'genPayment', 'partPayment', 'realization', 'allEvaluations', 'averageScores'));
    return $pdf->download('Program_Report_' . Str::slug($program->program_name) . '.pdf');
}



}
