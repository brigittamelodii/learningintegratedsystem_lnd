<?php

namespace App\Http\Controllers;

use App\Models\payments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ParticipantsPayment;

class BulkActionController extends Controller
{
    /**
     * Handle bulk approve action
     */
    public function bulkApprove(Request $request)
    {
        // Add debugging
        Log::info('Bulk approve method called', [
            'request_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        try {
            // Validate request
            $validated = $request->validate([
                'selected_ids' => 'required|string',
                'type' => 'required|string|in:payments,participants-payment',
                'remarks' => 'nullable|string|max:500'
            ]);

            Log::info('Validation passed', $validated);

            // Parse selected IDs
            $selectedIds = explode(',', $request->selected_ids);
            $selectedIds = array_filter(array_map('trim', $selectedIds));
            
            if (empty($selectedIds)) {
                Log::warning('No items selected for approval');
                return response()->json([
                    'success' => false,
                    'message' => 'No items selected for approval.'
                ], 400);
            }

            $type = $request->type;
            $remarks = $request->remarks;
            $updatedCount = 0;

            Log::info('Starting bulk approve transaction', [
                'type' => $type,
                'selected_ids' => $selectedIds,
                'remarks' => $remarks
            ]);

            DB::beginTransaction();

            if ($type === 'payments') {
                // Handle general payments
                $updatedCount = payments::whereIn('id', $selectedIds)
                    ->where('status', 'Check by Manager')
                    ->update([
                        'status' => 'Approve',
                        'remarks' => $remarks,
                        'approved_at' => now(),
                        'approved_by' => auth()->id() ?? null,
                        'updated_at' => now()
                    ]);
                    
                Log::info('General payments updated', ['count' => $updatedCount]);
                    
            } elseif ($type === 'participants-payment') {
                // Handle participant payments
                $updatedCount = ParticipantsPayment::whereIn('id', $selectedIds)
                    ->where('status', 'Check by Manager')
                    ->update([
                        'status' => 'Approve',
                        'remarks' => $remarks,
                        'approved_at' => now(),
                        'approved_by' => auth()->id() ?? null,
                        'updated_at' => now()
                    ]);
                    
                Log::info('Participant payments updated', ['count' => $updatedCount]);
            }

            DB::commit();

            Log::info('Bulk approve completed successfully', [
                'type' => $type,
                'selected_ids' => $selectedIds,
                'updated_count' => $updatedCount,
                'user_id' => auth()->id() ?? 'guest'
            ]);

            // Return JSON response for AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully approved {$updatedCount} " . str_replace('-', ' ', $type) . "(s).",
                    'updated_count' => $updatedCount
                ]);
            }

            // Return redirect for regular form submission
            return redirect()->back()->with('success', "Successfully approved {$updatedCount} " . str_replace('-', ' ', $type) . "(s).");

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            Log::error('Validation failed in bulk approve', [
                'errors' => $e->validator->errors()->all()
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
                ], 422);
            }
            
            return redirect()->back()->withErrors($e->validator)->withInput();
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bulk approve failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while processing bulk approval: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while processing bulk approval.');
        }
    }

    /**
     * Handle bulk reject action
     */
    public function bulkReject(Request $request)
    {
        // Add debugging
        Log::info('Bulk reject method called', [
            'request_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        try {
            // Validate request
            $validated = $request->validate([
                'selected_ids' => 'required|string',
                'type' => 'required|string|in:payments,participants-payment',
                'remarks' => 'nullable|string|max:500'
            ]);

            Log::info('Validation passed', $validated);

            // Parse selected IDs
            $selectedIds = explode(',', $request->selected_ids);
            $selectedIds = array_filter(array_map('trim', $selectedIds));
            
            if (empty($selectedIds)) {
                Log::warning('No items selected for rejection');
                return response()->json([
                    'success' => false,
                    'message' => 'No items selected for rejection.'
                ], 400);
            }

            $type = $request->type;
            $remarks = $request->remarks;
            $updatedCount = 0;

            Log::info('Starting bulk reject transaction', [
                'type' => $type,
                'selected_ids' => $selectedIds,
                'remarks' => $remarks
            ]);

            DB::beginTransaction();

            if ($type === 'payments') {
                // Handle general payments
                $updatedCount = payments::whereIn('id', $selectedIds)
                    ->whereIn('status', ['Pending', 'Check by PIC', 'Check by Manager'])
                    ->update([
                        'status' => 'Reject',
                        'remarks' => $remarks,
                        'rejected_at' => now(),
                        'rejected_by' => auth()->id() ?? null,
                        'updated_at' => now()
                    ]);
                    
                Log::info('General payments rejected', ['count' => $updatedCount]);
                    
            } elseif ($type === 'participants-payment') {
                // Handle participant payments
                $updatedCount = ParticipantsPayment::whereIn('id', $selectedIds)
                    ->whereIn('status', ['Pending', 'Check by PIC', 'Check by Manager'])
                    ->update([
                        'status' => 'Reject',
                        'remarks' => $remarks,
                        'rejected_at' => now(),
                        'rejected_by' => auth()->id() ?? null,
                        'updated_at' => now()
                    ]);
                    
                Log::info('Participant payments rejected', ['count' => $updatedCount]);
            }

            DB::commit();

            Log::info('Bulk reject completed successfully', [
                'type' => $type,
                'selected_ids' => $selectedIds,
                'updated_count' => $updatedCount,
                'user_id' => auth()->id() ?? 'guest'
            ]);

            // Return JSON response for AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Successfully rejected {$updatedCount} " . str_replace('-', ' ', $type) . "(s).",
                    'updated_count' => $updatedCount
                ]);
            }

            // Return redirect for regular form submission
            return redirect()->back()->with('success', "Successfully rejected {$updatedCount} " . str_replace('-', ' ', $type) . "(s).");

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            Log::error('Validation failed in bulk reject', [
                'errors' => $e->validator->errors()->all()
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
                ], 422);
            }
            
            return redirect()->back()->withErrors($e->validator)->withInput();
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bulk reject failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while processing bulk rejection: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'An error occurred while processing bulk rejection.');
        }
    }
}