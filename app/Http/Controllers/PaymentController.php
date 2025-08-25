<?php

namespace App\Http\Controllers;

use App\Models\CategoryBudget;
use App\Models\ParticipantsPayment;
use App\Models\payments;
use App\Models\Program;
use App\Models\Tna;
use App\Models\User;
use Faker\Provider\ar_EG\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index()
{
    $payments = payments::with(['program.training_program', 'user'])
            ->orderByDesc('created_at')
            ->get(); 
    $participantPayments = ParticipantsPayment::with(['participants', 'programs', 'classes'])
            ->orderByDesc('created_at')
            ->get();
    return view('payments.index', compact('payments', 'participantPayments'));
}

public function create()
    {
        $programs = Program::with(['training_program.category.tna', 'category_budget', 'payments'])->get();
        $pics = User::role('pic')->get();

        $programList = $programs
            ->filter(function ($program) {
                return $program->category_budget && $program->category_budget->isNotEmpty();
            })
            ->map(function ($program) {
                $totalBudget = $program->category_budget->sum('amount_fee');
                $totalPayment = $program->payments->sum('total_transfer');

                return [
                    'program_id' => $program->id,
                    'label' => ($program->training_program->category->tna->tna_year ?? 'N/A') . ' - ' . ($program->program_name ?? 'N/A'),
                    'total_budget' => $totalBudget,
                    'total_payment' => $totalPayment,
                    'remaining' => $totalBudget - $totalPayment,
                ];
            })
            ->values(); // Ensure proper array indexing

        return view('payments.create', [
            'programs' => $programList,
            'pics' => $pics,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_fee' => 'required|string',
            'amount_fee' => 'required|numeric|min:0',
            'program_id' => 'required|exists:programs,id',
            'account_no' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'ppn_fee' => 'nullable|numeric|min:0',
            'pph_fee' => 'nullable|numeric|min:0',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
            'created_by_pics_id' => 'nullable|exists:users,id', // Changed from user_id to match form field
        ]);

        DB::beginTransaction();

        try {
            $amount = $request->amount_fee;
            $ppn = $request->ppn_fee ?? 0;
            $pph = $request->pph_fee ?? 0;

            $totalTransfer = $amount + $ppn - $pph;

            // Prepare payment data aligned with database structure
            $paymentData = [
                'category_fee' => $request->category_fee,
                'amount_fee' => $amount,
                'program_id' => $request->program_id,
                'account_no' => $request->account_no,
                'account_name' => $request->account_name,
                'ppn_fee' => $ppn,
                'pph_fee' => $pph,
                'total_transfer' => $totalTransfer,
                'status' => 'Pending', // Always start with Pending
                'user_id' => $request->created_by_pics_id ?? Auth::id(), // Use form field or current user
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Handle file upload - store as blob in database
            if ($request->hasFile('file_path')) {
                $file = $request->file('file_path');
                $paymentData['file_path'] = file_get_contents($file);
            }

            $payment = payments::create($paymentData);

            DB::commit();
            return redirect()->route('payments.index')->with('success', 'Payment created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment creation failed: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create payment: ' . $e->getMessage()])
                ->withInput();
        }
    }

public function bulkAction(Request $request)
{
    $validated = $request->validate([
        'selected_ids' => 'required|string',
        'type' => 'required|in:participants-payment,payments',
        'action' => 'required|in:approve,reject', // Add action validation
        'remarks' => 'nullable|string|max:1000'
    ]);

    $ids = explode(',', $validated['selected_ids']);
    $type = $validated['type'];
    $action = $validated['action'];
    $remarks = $validated['remarks'];
    
    // Convert action to proper status format
    $status = ucfirst($action); // 'approve' -> 'Approve', 'reject' -> 'Reject'

    try {
        if ($type === 'participants-payment') {
            $updated = ParticipantsPayment::whereIn('id', $ids)
                ->where('status', 'Check by Manager')
                ->update([
                    'status' => $status,
                    'remarks' => $remarks,
                    'updated_at' => now()
                ]);
        } else {
            $updated = payments::whereIn('id', $ids)
                ->where('status', 'Check by Manager')
                ->update([
                    'status' => $status,
                    'remarks' => $remarks,
                    'updated_at' => now()
                ]);
        }

        if ($updated === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No records were updated. Please ensure the selected items are in "Check by Manager" status.'
            ], 400);
        }

        if($status === 'Approve') {
            // Update realization for each program
            if ($type === 'participants-payment') {
                $payments = ParticipantsPayment::whereIn('id', $ids)->get();
            } else {
                $payments = payments::whereIn('id', $ids)->get();
            }

            foreach ($payments as $payment) {
                $program = $payment->program;
                if ($program) {
                    $program->updateRealization();
                    // If TNA is associated, update its realization
                    if ($program->training_program && $program->training_program->category && $program->training_program->category->tna) {
                        $tna = $program->training_program->category->tna;
                        $tna->updateRealization();
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully {$action}d {$updated} item(s)."
        ]);

    } catch (\Exception $e) {
        \Log::error('Bulk action error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while processing the bulk action.'
        ], 500);
    }
}
    


// Tampilkan seluruh pembayaran umum (payments)
public function show($id)
{
    $payment = payments::with([
        'program.category_budget', 
        'program.payments', 
        'program.training_program', 
        'program.participants_payment',
        'users'
    ])->findOrFail($id);

    $program = $payment->program;
    $participant = $program->participants_payment;
    

    // Hitung total category_fee dari semua category_budget di program
    $totalCategoryFee = $program->category_budget->sum('amount_fee');

    // Hitung total payment amount_fee yang statusnya Approve
    $totalApproved = $program->payments->where('status', 'Approve')->sum('total_transfer');
    $totalApprovedParticipant = $program->participants_payment->where('status', 'Approve')->sum('amount_fee');
    // Hitung remaining budget
    $remainingBudget = $totalCategoryFee - ($totalApproved+$totalApprovedParticipant);

    return view('payments.general_popup', compact('payment', 'remainingBudget'));
}

public function checkbypic(Request $request, $id)
{
    DB::beginTransaction();
    try {
        $payment = payments::findOrFail($id);
        $payment->update([
        'status' => 'Check by PIC',
        'remarks'=> $request->remarks,
    ]);
        DB::commit();
        return back()->with('success', 'Payment is processed by PIC.');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Fail checking payment: ' . $e->getMessage());
        return redirect()->back()->withErrors(['debug' => $e->getMessage()]);
    }
    
}

public function checkbymanager(Request $request, $id)
{
    $payment = payments::findOrFail($id);
    $payment->update([
        'status' => 'Check by Manager',
        'remarks'=> $request->remarks,
    ]);
    return back()->with('success', 'Payment is processed by Manager.');

}

public function approve(Request $request, $id)
{

    DB::beginTransaction();
    try {
        $payment = payments::findOrFail($id);
        $payment->update([
            'status' => 'Approve',
            'remarks' => $request->remarks,
            'approved_at' => now(),
        ]);

        $program = $payment->program;
        $program->updateRealization(); // ✅ UPDATE GABUNGAN

        DB::commit();
        return redirect()->back()->with('success', 'Payment approved and realization updated.');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Gagal approve payment: ' . $e->getMessage());
        return back()->withErrors(['debug' => $e->getMessage()]);
    }
}


public function reject(Request $request, $id)
{
    $payment = payments::findOrFail($id);
    $payment->update([
        'status' => 'Reject',
        'remarks'=> $request->remarks,
    ]);
    return back()->with('success', 'Payment rejected.');
}

public function edit($id)
{
    $payment = payments::findOrFail($id);
    $pics = User::role('pic')->get();

    return view('payments.edit', compact('payment','pics'));
}

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_fee' => 'required|string',
            'amount_fee' => 'required|numeric|min:0',
            'account_no' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'ppn_fee' => 'nullable|numeric|min:0',
            'pph_fee' => 'nullable|numeric|min:0',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:2048',
            'user_id' => 'required|exists:users,id',
        ]);

        $payment = payments::findOrFail($id);
        DB::beginTransaction();

        try {
            $amount = $request->amount_fee;
            $ppn = $request->ppn_fee ?? 0;
            $pph = $request->pph_fee ?? 0;
            $totalTransfer = $amount + $ppn - $pph;

            $updateData = [
                'category_fee' => $request->category_fee,
                'amount_fee' => $amount,
                'ppn_fee' => $ppn,
                'pph_fee' => $pph,
                'total_transfer' => $totalTransfer,
                'account_no' => $request->account_no,
                'account_name' => $request->account_name,
                'user_id' => $request->user_id,
                'updated_at' => now(),
            ];

            // Handle file upload - store as blob in database
            if ($request->hasFile('file_path')) {
                $file = $request->file('file_path');
                $updateData['file_path'] = file_get_contents($file);
            }

            $payment->update($updateData);

            // Update program realization if status is Approve
            if ($payment->status === 'Approve') {
                $program = $payment->program;
                $program->updateRealization();
            }

            DB::commit();
            return redirect()->route('payments.index')->with('success', 'Payment updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment update failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update payment: ' . $e->getMessage()])
                        ->withInput();
        }
    }



public function showDocument($id)
{
    $payment = payments::findOrFail($id);

    if (!$payment->file_path) {
        abort(404, 'File not found');
    }

    return response($payment->file_path)
        ->header('Content-Type', 'application/pdf')
        ->header('Content-Disposition', 'inline; filename="payment-document.pdf"');
}
}
