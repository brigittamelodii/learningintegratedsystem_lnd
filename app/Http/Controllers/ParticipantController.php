<?php

namespace App\Http\Controllers;

use App\Exports\FinalParticipantsExport;
use App\Exports\ParticipantsTempExport;
use App\Imports\FinalParticipantScoresImport;
use App\Imports\ParticipantsImport;
use App\Models\Classes;
use App\Models\ClassParticipant;
use App\Models\Participant;
use App\Models\ParticipantsTemp;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ParticipantController extends Controller
{
    // Status yang dianggap "dropped invitation" - tidak dihitung dalam attendance rate
    private $droppedStatuses = ['Absent - Sick', 'Absent - Maternity', 'Absent - Business'];
    
    // Status yang tetap dihitung dalam attendance rate (invited tapi tidak hadir)
    private $absentButCountedStatuses = ['Absent - Busy', 'Absent'];

    public function index(Request $request)
    {
        $query = participant::query()->with('classes');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('participant_name', 'like', "%$search%")
                  ->orWhere('karyawan_nik', 'like', "%$search%");
            })->orWhereHas('classes', function ($q) use ($search) {
                $q->where('class_batch', 'like', "%$search%")
                  ->orWhere('class_name', 'like', "%$search%");
            });
        }

        $participants = $query->get();
        $tempParticipants = ParticipantsTemp::with('classes')->get();

        return view('participants.index', compact('participants', 'tempParticipants'));
    }

    public function create()
    {
        $classes = Classes::all();
        $user = User::all();
        return view('participants.create', compact('classes', 'user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'participant_name' => 'required|string|max:255',
            'karyawan_nik' => 'required|string|max:255',
            'participant_position' => 'required|string|max:255',
            'participant_working_unit' => 'required|string|max:255',
            'pre_test' => 'nullable|numeric',
            'post_test' => 'nullable|numeric',
            'status' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'batch' => 'numeric',
        ]);

        // Simpan ke temp terlebih dahulu
        ParticipantsTemp::create($request->all());
        ClassParticipant::create($request->only(['class_id', 'batch']));

        return redirect()->route('participants.index')->with('success', 'Participant added to temporary table.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
            'class_id' => 'required|exists:classes,id',
            'batch' => 'required|string|max:255'
        ]);

        try {
            Excel::import(
                new ParticipantsImport($request->class_id, $request->batch),
                $request->file('file')
            );
            return back()->with('success', 'Participants imported successfully.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    public function indexByClass($class_id)
    {
        $class = Classes::findOrFail($class_id);

        $tempParticipants = ParticipantsTemp::where('class_id', $class_id)->get();
        $finalParticipants = participant::where('class_id', $class_id)->get();

        return view('participants.byClassIndex', compact(
            'class_id',
            'class',
            'tempParticipants',
            'finalParticipants'
        ));
    }

    public function editByClass($class_id)
    {
        $class = Classes::findOrFail($class_id);

        $tempParticipants = ParticipantsTemp::where('class_id', $class_id)->get();
        $finalParticipants = participant::where('class_id', $class_id)->get();

        $statusOptions = ['Invited', 'Present', 'Absent - Sick', 'Absent - Busy', 'Absent - Maternity','Absent - Business', 'Absent'];
        
        return view('participants.editByClass', compact(
            'class',
            'tempParticipants',
            'finalParticipants',
            'statusOptions'
        ));
    }

    public function editFinal($id)
    {
        $participant = participant::findOrFail($id);
        $statusOptions = ['Invited', 'Present', 'Absent - Sick', 'Absent - Busy', 'Absent - Maternity','Absent - Business', 'Absent'];
        return view('participants.editFinalParticipant', compact('participant', 'statusOptions'));
    }

    public function updateFinal(Request $request, $id)
    {
        $participant = participant::findOrFail($id);
        $participant->update($request->only(['pre_test', 'post_test', 'status']));
        return redirect()->route('participants.byClassIndex', ['class_id' => $participant->class_id])
            ->with('success', 'Peserta final berhasil diperbarui.');
    }

    public function editTemp($id)
    {
        $participant = ParticipantsTemp::findOrFail($id);
        $statusOptions = ['Invited', 'Present', 'Absent - Sick', 'Absent - Busy', 'Absent - Maternity','Absent - Business', 'Absent'];
        return view('participants.editTempParticipant', compact('participant', 'statusOptions'));
    }

    public function updateTemp(Request $request, $id)
    {
        try {
            \DB::beginTransaction();

            $participantTemp = ParticipantsTemp::findOrFail($id);
            $newStatus = $request->input('status');
            $classId = $participantTemp->class_id;

            // Jika status adalah "dropped invitation" - hapus dari temp
            if (in_array($newStatus, $this->droppedStatuses)) {
                $participantTemp->delete();
                \DB::commit();
                return redirect()->route('participants.byClassIndex', ['class_id' => $classId])
                    ->with('success', 'Undangan dibatalkan. Peserta dihapus dari daftar.');
            }

            // Jika status adalah Present - pindah ke final table
            if ($newStatus === 'Present') {
                // Cek apakah peserta sudah ada di tabel final dengan NIK yang sama
                $existingParticipant = Participant::where('class_id', $classId)
                    ->where('karyawan_nik', $participantTemp->karyawan_nik)
                    ->first();

                if ($existingParticipant) {
                    \DB::rollBack();
                    return redirect()->route('participants.byClassIndex', ['class_id' => $classId])
                        ->withErrors(['error' => 'Peserta dengan NIK ' . $participantTemp->karyawan_nik . ' sudah ada di tabel final.']);
                }

                // Pindahkan data ke tabel Participant (final)
                Participant::create([
                    'class_id' => $participantTemp->class_id,
                    'karyawan_nik' => $participantTemp->karyawan_nik,
                    'participant_name' => $participantTemp->participant_name,
                    'participant_position' => $participantTemp->participant_position,
                    'participant_working_unit' => $participantTemp->participant_working_unit,
                    'status' => $newStatus,
                    'pre_test' => $participantTemp->pre_test,
                    'post_test' => $participantTemp->post_test,
                    'user_id' => $participantTemp->user_id
                ]);

                // Hapus dari tabel sementara setelah berhasil hadir
                $participantTemp->delete();

                \DB::commit();
                return redirect()->route('participants.byClassIndex', ['class_id' => $classId])
                    ->with('success', 'Peserta berhasil hadir dan dipindahkan ke tabel final.');
            }

            // Untuk status lainnya (Invited, Absent-Busy, Absent) - tetap di temp dengan status updated
            $participantTemp->update(['status' => $newStatus]);

            \DB::commit();
            return redirect()->route('participants.byClassIndex', ['class_id' => $classId])
                ->with('success', 'Status peserta berhasil diperbarui.');

        } catch (Exception $e) {
            \DB::rollBack();
            Log::error('Update Temp Participant Error: ' . $e->getMessage());
            
            return redirect()->back()->withErrors([
                'error' => 'Gagal memperbarui peserta: ' . $e->getMessage()
            ]);
        }
    }

    public function updateByClass(Request $request, $classId)
    {
        try {
            \DB::beginTransaction();

            $participantsData = $request->input('participants');

            if (!$participantsData) {
                return redirect()->back()->withErrors(['error' => 'Tidak ada data peserta yang diperbarui.']);
            }

            $processedCount = 0;
            $errorMessages = [];

            foreach ($participantsData as $participantId => $values) {
                $source = $values['source'];
                $status = $values['status'] ?? null;
                $preTest = $values['pre_test'] ?? null;
                $postTest = $values['post_test'] ?? null;

                if ($source === 'temp') {
                    $tempParticipant = ParticipantsTemp::find($participantId);
                    if ($tempParticipant) {
                        // Jika status adalah "dropped invitation" - hapus dari temp
                        if (in_array($status, $this->droppedStatuses)) {
                            $tempParticipant->delete();
                            $processedCount++;
                            continue;
                        }

                        // Jika status adalah Present - pindah ke final table
                        if ($status === 'Present') {
                            // Cek duplikasi
                            $existingParticipant = Participant::where('class_id', $classId)
                                ->where('karyawan_nik', $tempParticipant->karyawan_nik)
                                ->first();

                            if ($existingParticipant) {
                                $errorMessages[] = "Peserta dengan NIK {$tempParticipant->karyawan_nik} sudah ada di tabel final.";
                                continue;
                            }

                            Participant::create([
                                'class_id' => $classId,
                                'karyawan_nik' => $tempParticipant->karyawan_nik,
                                'participant_name' => $tempParticipant->participant_name,
                                'participant_position' => $tempParticipant->participant_position,
                                'participant_working_unit' => $tempParticipant->participant_working_unit,
                                'status' => $status,
                                'pre_test' => null,
                                'post_test' => null,
                                'user_id' => $tempParticipant->user_id,
                            ]);

                            $tempParticipant->delete();
                            $processedCount++;
                        } else {
                            // Untuk status lainnya (Invited, Absent-Busy, Absent) - update di temp
                            $tempParticipant->update(['status' => $status]);
                            $processedCount++;
                        }
                    }
                }

                if ($source === 'main') {
                    $participant = Participant::find($participantId);
                    if ($participant) {
                        // Validasi nilai test
                        if ($preTest !== null && ($preTest < 0 || $preTest > 100)) {
                            $errorMessages[] = "Nilai pre test untuk NIK {$participant->karyawan_nik} harus antara 0-100.";
                            continue;
                        }
                        if ($postTest !== null && ($postTest < 0 || $postTest > 100)) {
                            $errorMessages[] = "Nilai post test untuk NIK {$participant->karyawan_nik} harus antara 0-100.";
                            continue;
                        }

                        $participant->update([
                            'status' => $status,
                            'pre_test' => $preTest,
                            'post_test' => $postTest,
                        ]);
                        $processedCount++;
                    }
                }
            }

            if (!empty($errorMessages)) {
                \DB::rollBack();
                return redirect()->back()->withErrors(['error' => implode(' | ', $errorMessages)]);
            }

            \DB::commit();
            return redirect()->route('participants.byClassIndex', ['class_id' => $classId])
                ->with('success', "Berhasil memperbarui {$processedCount} data peserta.");

        } catch (Exception $e) {
            \DB::rollBack();
            Log::error('Update By Class Error: ' . $e->getMessage());
            
            return redirect()->back()->withErrors([
                'error' => 'Gagal memperbarui data peserta: ' . $e->getMessage()
            ]);
        }
    }

    // Untuk global (tanpa class)
    public function destroy($id)
    {
        // Cek di tabel final (Participant)
        $participant = participant::find($id);
        if ($participant && in_array($participant->status, $this->droppedStatuses)) {
            $participant->delete();
            return back()->with('success', 'Peserta final berhasil dihapus.');
        }

        // Cek di tabel sementara (ParticipantsTemp)
        $participantTemp = ParticipantsTemp::find($id);
        if ($participantTemp && in_array($participantTemp->status, $this->droppedStatuses)) {
            $participantTemp->delete();
            return back()->with('success', 'Peserta sementara berhasil dihapus.');
        }

        return back()->withErrors(['error' => 'Only participants with dropped invitation status can be deleted.']);
    }

    public function importFinalScores(Request $request, $classId)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            \DB::beginTransaction();
            
            $import = new FinalParticipantScoresImport($classId);
            Excel::import($import, $request->file('file'));

            \DB::commit();

            return redirect()->back()->with([
                'success' => "Import berhasil: {$import->log['inserted']} peserta ditambahkan, {$import->log['updated']} peserta diperbarui, {$import->log['skipped']} peserta dilewati.",
                'import_log' => $import->log
            ]);

        } catch (Exception $e) {
            \DB::rollBack();
            Log::error('Import Final Scores Error: ' . $e->getMessage());
            
            return redirect()->back()->withErrors([
                'error' => 'Gagal mengimport file: ' . $e->getMessage()
            ]);
        }
    }

    public function destroyByClass($class_id, $id)
    {
        // Cek di tabel final (Participant)
        $participant = participant::where('class_id', $class_id)->find($id);
        if ($participant && in_array($participant->status, $this->droppedStatuses)) {
            $participant->delete();
            return back()->with('success', 'Peserta final berhasil dihapus.');
        }

        // Cek di tabel sementara (ParticipantsTemp)
        $participantTemp = ParticipantsTemp::where('class_id', $class_id)->find($id);
        if ($participantTemp && in_array($participantTemp->status, $this->droppedStatuses)) {
            $participantTemp->delete();
            return back()->with('success', 'Peserta sementara berhasil dihapus.');
        }

        return back()->withErrors(['error' => 'Only participants with dropped invitation status can be deleted.']);
    }

    public function deleteTemp($id)
    {
        $participantTemp = ParticipantsTemp::findOrFail($id);
        $participantTemp->delete();
        return redirect()->route('participants.index')->with('success', 'Temporary participant deleted successfully.');
    }

    public function exportTempParticipants($class_id)
    {
        return Excel::download(new ParticipantsTempExport($class_id), 'peserta_sementara_kelas_' . $class_id . '.xlsx');
    }

    // Method untuk menghitung attendance rate di dashboard
    public function getAttendanceRate($class_id)
    {
        // Total invited (yang masih ada di temp + yang di final)
        $totalInvited = ParticipantsTemp::where('class_id', $class_id)->count() + 
                       Participant::where('class_id', $class_id)->count();
        
        // Total present (hanya yang ada di final dengan status Present)
        $totalPresent = Participant::where('class_id', $class_id)
                                 ->where('status', 'Present')
                                 ->count();
        
        // Attendance rate calculation
        $attendanceRate = $totalInvited > 0 ? ($totalPresent / $totalInvited) * 100 : 0;
        
        return [
            'total_invited' => $totalInvited,
            'total_present' => $totalPresent,
            'attendance_rate' => round($attendanceRate, 2)
        ];
    }
}
