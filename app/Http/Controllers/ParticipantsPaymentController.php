<?php

namespace App\Http\Controllers;

use App\Models\CategoryBudget;
use App\Models\classes;
use App\Models\participant;
use App\Models\ParticipantsPayment;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ParticipantsPaymentController extends Controller
{
    public function create()
    {
        $category_budget = CategoryBudget::all();
        $classes = Classes::with('programs')->get();
        $participants = Participant::all();
        $programs = Program::all();
        $users = User::all();

        $isSuperadmin = auth()->user()->hasRole('superadmin');
        return view('participants-payment.create', compact('category_budget', 'classes', 'participants', 'users', 'programs', 'isSuperadmin'));
    }

    public function store(Request $request)
{
    $isSuperadmin = auth()->user()->hasRole('superadmin');

    $request->validate([
        'class_id' => 'required|exists:classes,id',
        'category_fee' => 'required',
        'amount_fee' => 'required|numeric',
        'file_path' => 'required|file',
        'account_name' => 'nullable|string|max:255',
        'account_no' => 'nullable|string|max:255',
        'user_id' => $isSuperadmin ? 'nullable|exists:users,id' : 'nullable',
        'participant_id' => $isSuperadmin ? 'required|exists:participants,id' : 'nullable',
    ]);

    // ✅ Ambil class & relasi program
    $class = Classes::with('programs')->findOrFail($request->class_id);
    $program = $class->programs;
    $programId = $program->id;
    $programPIC = $program->user_id;

    // ✅ Tentukan participant ID
    if ($isSuperadmin) {
        $participantId = $request->participant_id;
        $userId = $request->user_id ?? $programPIC;
    } else {
        $participant = Participant::where('user_id', auth()->id())
            ->where('class_id', $request->class_id)
            ->first();

        if (!$participant) {
            return redirect()->back()->with('error', 'Anda tidak terdaftar pada kelas ini.');
        }

        $participantId = $participant->id;
        $userId = $programPIC;
    }

    if (!$userId) {
        return redirect()->back()->with('error', 'Program belum memiliki PIC yang ditugaskan.');
    }

    // ✅ Simpan file
    $file = $request->file('file_path');
    $filename = time() . '_' . $file->getClientOriginalName();
    $file->storeAs('public/payments', $filename);

    // ✅ Simpan pembayaran
    ParticipantsPayment::create([
        'program_id' => $programId,
        'class_id' => $request->class_id,
        'participants_id' => $participantId,
        'category_fee' => $request->category_fee,
        'amount_fee' => $request->amount_fee,
        'account_name' => $request->account_name,
        'account_no' => $request->account_no,
        'file_path' => 'payments/' . $filename,
        'status' => 'Pending',
        'user_id' => $userId,
    ]);

    return redirect()->route('payments.index')->with('success', 'Payment created successfully.');
}



    public function index()
    {
        $payments = ParticipantsPayment::with('classes','programs')->paginate(5);
        $programs['program_name'] = Program::get(["program_name"]);
        return view('payments.index',compact('payments'));
    }

    public function show($id)
    {
        $participantPayments = ParticipantsPayment::with([
            'participants', 
            'classes',
            'programs.category_budget',
            'programs.payments'
        ])->findOrFail($id);

        $programs = $participantPayments->programs;

        // Hitung total category_fee dari semua category_budget di program
        $totalCategoryFee = $programs->category_budget->sum('amount_fee');

        // Hitung total payment amount_fee yang statusnya Approve
        $totalApproved = $programs->payments->where('status', 'Approve')->sum('total_transfer');
        $totalApprovedParticipant = $programs->participants_payment->where('status', 'Approve')->sum('amount_fee');
        // Hitung remaining budget
        $remainingBudget = $totalCategoryFee - ($totalApproved+$totalApprovedParticipant);

        return view('payments.participant_popup', compact('participantPayments','remainingBudget'));
    }

    public function checkbypic(Request $request, $id)
    {

        DB::beginTransaction();
        try {
            $payment = ParticipantsPayment::findOrFail($id);
            $payment->update([
                'user_id' => $request->user_id,
                'status' => 'Check by PIC',
                'remarks' => $request->remarks,
            ]);
            DB::commit();
            return redirect()->back()->with('success', 'Payment is processed by PIC.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fail checking payment: ' . $e->getMessage());
            return redirect()->back()->withErrors(['debug' =>  $e->getMessage()]);
        }
    }

    public function checkbymanager(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $payment = ParticipantsPayment::findOrFail($id);
            $payment->update([
                'status' => 'Check by Manager',
                'remarks' => $request->remarks,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'Payment is processed by Manager.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed checking payment: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to check payment by manager: ' . $e->getMessage()]);
        }
    }

    public function approve(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $payment = ParticipantsPayment::findOrFail($id);
            $payment->update([
                'status' => 'Approve',
                'remarks' => $request->remarks,
            ]);

            $program = $payment->programs;
            $program->updateRealization();

            DB::commit();
            return redirect()->back()->with('success', 'Payment approved and realization updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to approve payment: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to approve payment: ' . $e->getMessage()]);
        }
    }

    public function reject(Request $request, $id)
    {
        $payment = ParticipantsPayment::findOrFail($id);
        $payment->update([
            'status' => 'Reject',
            'remarks' => $request->remarks,
        ]);

        return redirect()->back()->with('success', 'Payment rejected.');
    }

    public function edit($id)
    {
        $participantsPayment = ParticipantsPayment::findOrFail($id);
        $category_budget = CategoryBudget::all();
        $classes = classes::with('programs')->get();
        $participants = participant::all();
        $programs = Program::all();
        $pics = User::all();

        return view('participants-payment.edit', compact('participantsPayment', 'category_budget', 'classes', 'participants', 'pics', 'programs'));
    }

    public function update(Request $request, $id)
    {
        $payment = ParticipantsPayment::findOrFail($id);

        $validated = $request->validate([
            'category_fee' => 'required|string',
            'amount_fee' => 'required|numeric',
            'program_id' => 'required|exists:programs,id',
            'class_id' => 'required|exists:classes,id',
            'account_no' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'file_path' => 'nullable|file|max:2048',
        ]);

                         
        $payment->fill($validated);
        if ($request->hasFile('file_path')) {
            $payment->file_path = file_get_contents($request->file('file_path')->getRealPath());
        }
        $payment->status = 'Pending';
        $payment->save();

        return redirect()->route('payments.index')->with('success', 'Payment updated successfully.');
    }

    public function showDocument($id)
    {
        $payment = ParticipantsPayment::findOrFail($id);

        if (!$payment->file_path) {
            abort(404, 'File not found');
        }

        $filePath = storage_path('app/public/' . $payment->file_path);

        if (!file_exists($filePath)) {
            abort(404, 'File not found');
        }

        return response()->file($filePath);
    }
}