<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\Agenda;
use App\Models\evaluation;
use App\Models\participant;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class ClassesController extends Controller
{
   public function index(Request $request)
{
    $user = auth()->user();

   if ($user->hasRole('participant')) {
    $classIds = $user->participantClasses->pluck('id')
        ->merge($user->invitedClasses->pluck('id'))
        ->unique();

    $query = Classes::with('programs.user')->whereIn('id', $classIds);
    } else {
        $query = Classes::with('programs.user');
    }


    if ($search = $request->search) {
        $query->where(function ($q) use ($search) {
            $q->where('class_name', 'like', "%$search%")
              ->orWhereHas('programs', fn($q2) => $q2->where('program_name', 'like', "%$search%"));
        });
    }

    if ($request->program_id) {
        $query->where('program_id', $request->program_id);
    }

   if ($request->user_id) {
    $query->whereHas('programs.user', fn($q) => $q->where('id', $request->user_id));
    }

    if ($request->start_date) {
        $query->whereDate('start_date', '>=', $request->start_date);
    }

    if ($request->end_date) {
        $query->whereDate('end_date', '<=', $request->end_date);
    }

    if ($request->month && $request->year) {
        $query->whereMonth('start_date', $request->month)
              ->whereYear('start_date', $request->year);
    }

    return view('classes.index', [
        'classes' => $query->latest()->get(),
        'programs' => Program::all(),
        'user' => User::role('pic')->get(),
    ]);
}

    public function create()
    {
        $programs = Program::all();
        $users = User::role('pic')->get();
        return view('classes.create', compact('programs', 'users'));
    }

  
    public function getNextBatch(Request $request)
    {
        $programId = $request->input('program_id');
        
        if (!$programId) {
            return response()->json(['next_batch' => 1]);
        }

        // Get the highest batch number for this program
        $lastBatch = Classes::where('program_id', $programId)
            ->max('class_batch');

        $nextBatch = $lastBatch ? $lastBatch + 1 : 1;

        return response()->json(['next_batch' => $nextBatch]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'class_doc' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'program_id' => 'required|exists:programs,id',
            'class_loc' => 'nullable|string|max:255',
        ]);

        // Extract batch number from class_name
        $batchNumber = $this->extractBatchFromClassName($request->class_name);

        $class = new Classes($request->except('class_doc'));
        $class->class_batch = $batchNumber; // Set batch dari class_name

        if ($request->hasFile('class_doc')) {
            $filename = uniqid() . '_' . $request->file('class_doc')->getClientOriginalName();
            $class->class_doc = $request->file('class_doc')->storeAs('uploads', $filename, 'public');
        }

        $class->save();

        return redirect()->route('classes.index')->with('success', 'Class created successfully.');
    }
    
    public function edit($id)
    {
        $class = Classes::findOrFail($id);
        $programs = Program::all();
        $users = User::role('pic')->get();
        return view('classes.edit', compact('class', 'programs', 'users'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'class_name'   => 'required|string|max:255',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after_or_equal:start_date',
            'class_doc'    => 'nullable|file|mimes:pdf,doc,docx|max:2048',
            'program_id'   => 'required|exists:programs,id',
            'class_loc'    => 'nullable|string|max:255',
        ]);

        $class = Classes::findOrFail($id);
        $class->class_name  = $request->class_name;
        $class->start_date  = $request->start_date;
        $class->end_date    = $request->end_date;
        $class->program_id  = $request->program_id;
        
        // Extract and update batch number from class_name
        $class->class_batch = $this->extractBatchFromClassName($request->class_name);

        if ($request->hasFile('class_doc')) {
            if ($class->class_doc) {
                Storage::disk('public')->delete($class->class_doc);
            }
            $file = $request->file('class_doc');
            $filename = uniqid() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('uploads', $filename, 'public');
            $class->class_doc = $filePath;
        }

        $class->save();

        return redirect()->route('classes.index')->with('success', 'Class updated successfully.');
    }

    //Extract batch number from class name
    private function extractBatchFromClassName($className)
    {
        // Match pattern like "- Batch 1", "- Batch 2", etc.
        if (preg_match('/.*-\s*batch\s*(\d+)/i', $className, $matches)) {
            return (int)$matches[1];
        }
        
        // Alternative patterns
        if (preg_match('/.*batch\s*(\d+)/i', $className, $matches)) {
            return (int)$matches[1];
        }
        
        // If no batch found, return 1 as default
        return 1;
    }

    public function storeAgenda(Request $request, $id)
    {
        $request->validate([
            'materi_name' => 'required|array',
            'materi_name.*' => 'required|string|max:255',
            'materi_duration' => 'required|array',
            'materi_duration.*' => 'required|date_format:H:i',
            'file_path' => 'nullable|array',
            'file_path.*' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->materi_name as $i => $name) {
                $agenda = new Agenda();
                $agenda->class_id = $id;
                $agenda->materi_name = $name;
                $agenda->materi_duration = $request->materi_duration[$i];

                if ($request->hasFile("file_path.$i")) {
                    $file = $request->file("file_path.$i");
                    $filename = uniqid() . '_' . $file->getClientOriginalName();
                    $agenda->file_path = $file->storeAs('uploads', $filename, 'public');
                }

                $agenda->save();
            }

            DB::commit();
            return redirect()->route('classes.edit', $id)->with('success', 'Agenda berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan agenda: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        $class = Classes::findOrFail($id);

        foreach ($class->agenda as $agenda) {
            if ($agenda->file_path) {
                Storage::disk('public')->delete($agenda->file_path);
            }
            $agenda->delete();
        }

        if ($class->class_doc) {
            Storage::disk('public')->delete($class->class_doc);
        }

        $class->delete();

        return redirect()->route('classes.index')->with('success', 'Class deleted successfully.');
    }

    public function updateAgenda(Request $request, $id)
    {
        $request->validate([
            'agenda_ids' => 'required|array',
            'materi_name' => 'required|array',
            'materi_duration' => 'required|array',
            'file_path.*' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        foreach ($request->agenda_ids as $index => $agenda_id) {
            $agenda = Agenda::find($agenda_id);
            if ($agenda) {
                $agenda->materi_name = $request->materi_name[$index];
                $agenda->materi_duration = $request->materi_duration[$index];

                if ($request->hasFile("file_path.$index")) {
                    $file = $request->file("file_path.$index");
                    $filename = uniqid() . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('uploads', $filename, 'public');
                    $agenda->file_path = $filePath;
                }

                $agenda->save();
            }
        }

        return redirect()->route('classes.edit', $id)->with('success', 'Agenda berhasil diperbarui.');
    }

    public function show($id)
    {
        $class = Classes::with([
            'agenda',
            'participants',
            'classEvaluations.evaluation',
            'participantstemp'
            ])->findOrFail($id);

        
        $allEvaluations = Evaluation::orderBy('eval_cat')->orderBy('id')->get();

        // Ambil rata-rata score per eval_id untuk kelas ini
        $averageScores = DB::table('class_evaluation')
        ->select('eval_id', DB::raw('AVG(eval_score) as average_score'))
        ->where('class_id', $class->id)
        ->groupBy('eval_id')
        ->pluck('average_score', 'eval_id'); 

        $allParticipants = $class->participants->concat($class->participantstemp);

        return view('classes.view', compact('class', 'allEvaluations', 'averageScores', 'allParticipants'));
    }

    public function exportPdf($class_id)
    {
        $classes = Classes::with([
            'programs.pic',
            'agenda',
            'participants'
        ])->findOrFail($class_id);

        $total_participants = $classes->participants->count();
        $participants = $classes->participants;

        $pdf = Pdf::loadView('classes.report_pdf', compact('classes', 'total_participants', 'participants'))
             ->setPaper('A4', 'portrait');

        return $pdf->download('class_' . $classes->class_name . '_report.pdf');
    }

    public function duplicate($id)
    {
        $originalClass = Classes::findOrFail($id);
        $programs = Program::all();
        $users = User::role('pic')->get();

        return view('classes.duplicate', compact('originalClass', 'programs', 'users'));
    }
}