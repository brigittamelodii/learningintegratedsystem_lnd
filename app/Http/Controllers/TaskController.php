<?php

namespace App\Http\Controllers;

use App\Models\FormTask;
use App\Models\TaskCategories;
use App\Models\TaskDocument;
use App\Models\TaskFill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    private function checkPICAccess()
    {
        if (!auth()->user()->hasRole(['pic', 'superadmin', 'manager'])) {
            if (request()->expectsJson()) {
                return abort(response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.'
                ], 403));
            }

            abort(403, 'Unauthorized');
        }
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole(['pic', 'manager', 'superadmin'])) {
            $tasks = FormTask::where('user_id', $user->id)
                ->with(['taskCategories', 'taskFills'])
                ->orderByDesc('created_at')
                ->paginate(10);
            return view('tasks.pic.index', compact('tasks'));
        }

        $joinedTasks = FormTask::whereHas('taskFills', fn($q) => $q->where('user_id', $user->id))
            ->with(['userFill', 'taskCategories'])
            ->orderByDesc('created_at')
            ->get();

        return view('tasks.user.index', compact('joinedTasks'));
    }

    public function create()
    {
        $this->checkPICAccess();
        return view('tasks.pic.create');
    }

    public function store(Request $request)
    {
        $this->checkPICAccess();

        $validated = $request->validate([
            'subjectName' => 'required|string|max:225',
            'subjectDesc' => 'required|string',
            'due_date' => 'nullable|date|after:now',
            'categories' => 'required|array|min:1',
            'categories.*.categoryName' => 'required|string|max:225',
            'categories.*.categoryDesc' => 'nullable|string|max:225',
        ]);

        $task = null;

        DB::transaction(function () use ($validated, &$task) {
            $task = FormTask::create([
                'subjectName' => $validated['subjectName'],
                'subjectDesc' => $validated['subjectDesc'],
                'due_date' => $validated['due_date'],
                'accessCode' => FormTask::generateAccessCode(),
                'status' => FormTask::STATUS_OPEN,
                'user_id' => Auth::id(),
            ]);

            foreach ($validated['categories'] as $cat) {
                $task->taskCategories()->create([
                    'categoryName' => $cat['categoryName'],
                    'categoryDesc' => $cat['categoryDesc'] ?? null,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'redirect' => route('tasks.index'),
            'accessCode' => $task?->accessCode,
        ]);
    }

    public function show(FormTask $task, Request $request)
    {
    $user = Auth::user();
    $perPage = $request->get('per_page', 10); // Default 10 items per page
    $statusFilter = $request->get('status'); // Filter by status
    $categoriesPerPage = $request->get('categories_per_page', 6); // Categories pagination
    
    if ($user->hasRole(['pic', 'manager', 'superadmin']) && $task->user_id === $user->id) {
        // Paginate categories
        $taskCategories = $task->taskCategories()
            ->orderBy('created_at', 'asc')
            ->paginate($categoriesPerPage, ['*'], 'categories');
        
        // Build query with optional status filter for participants
        $query = $task->taskFills()
            ->with(['fillDocuments', 'user'])
            ->orderBy('created_at', 'desc');
        
        // Apply status filter if provided
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }
        
        // Paginate task fills with related data
        $taskFills = $query->paginate($perPage, ['*'], 'fills');
        
        // Get summary statistics
        $stats = [
            'total' => $task->taskFills()->count(),
            'draft' => $task->taskFills()->where('status', TaskFill::STATUS_DRAFT)->count(),
            'pending' => $task->taskFills()->where('status', TaskFill::STATUS_PENDING)->count(),
            'in_review' => $task->taskFills()->where('status', TaskFill::STATUS_IN_REVIEW)->count(),
            'accepted' => $task->taskFills()->where('status', TaskFill::STATUS_ACCEPTED)->count(),
            'rejected' => $task->taskFills()->where('status', TaskFill::STATUS_REJECTED)->count(),
        ];
        
        return view('tasks.pic.show', compact('task', 'taskCategories', 'taskFills', 'stats'));
    } 
    else {
        $userFill = $task->getUserFill($user->id);
        if (!$userFill) {
            abort(403, 'You have not joined this task.');
        }
        
        // Paginate categories for user view too
        $taskCategories = $task->taskCategories()
            ->orderBy('created_at', 'asc')
            ->paginate($categoriesPerPage, ['*'], 'categories');
        
        // Paginate user's documents if needed
        $documents = $userFill->fillDocuments()
            ->with(['taskCategory'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'docs');
        
        return view('tasks.user.show', compact('task', 'taskCategories', 'userFill', 'documents'));
    }
}

    public function join(Request $request)
    {
        $validated = $request->validate([
            'accessCode' => 'required|string|size:6'
        ]);

        $task = FormTask::where('accessCode', $validated['accessCode'])->first();
        
        if (!$task) {
            throw ValidationException::withMessages([
                'accessCode' => 'Invalid access code.'
            ]);
        }

        if (!$task->canUserJoin(Auth::id())) {
            throw ValidationException::withMessages([
                'accessCode' => 'You cannot join this task.'
            ]);
        }

        TaskFill::create([
            'form_task_id' => $task->id,
            'user_id' => Auth::id(),
            'fillerName' => Auth::user()->name,
            'status' => TaskFill::STATUS_DRAFT,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Successfully joined the task!',
            'redirect' => route('tasks.fill', $task->id)
        ]);
    } 

    public function fill(FormTask $task)
    {
        $userFill = $task->getUserFill(Auth::id());
        
        if (!$userFill) {
            abort(403, 'You have not joined this task.');
        }
        
        $task->load(['taskCategories']);
        $userFill->load(['fillDocuments']);
        
        return view('tasks.user.fill', compact('task', 'userFill'));
    }

    public function submitFill(Request $request, FormTask $task)
{
    $user = Auth::user();

    $validated = $request->validate([
        'fillerName' => 'required|string|max:100',
        'documents' => 'nullable|array',
        'documents.*.*' => 'nullable|file|max:10240',
        'document_desc' => 'required|array',
        'document_desc.*' => 'required|string|max:1000',
    ]);

    DB::transaction(function () use ($request, $task, $user, $validated) {
        $taskFill = $task->taskFills()->where('user_id', $user->id)->first();
        
        if (!$taskFill) {
            abort(403, 'You have not joined this task.');
        }

        // Update filler info
        $taskFill->update([
            'fillerName' => $validated['fillerName'],
            'status' => TaskFill::STATUS_PENDING,
        ]);

        $documents = $request->file('documents') ?? [];
        $descs = $validated['document_desc'] ?? [];

        foreach ($descs as $categoryId => $desc) {
            $category = TaskCategories::findOrFail($categoryId);
            $files = $documents[$categoryId] ?? [];

            // Find existing document for this category
            $existingDoc = $taskFill->fillDocuments()
                ->where('taskcategory_id', $categoryId)
                ->first();

            if ($existingDoc) {
                // Check if new files are uploaded for this category
                if (!empty($files)) {
                    // If new files are uploaded, delete old file and create new document
                    if ($existingDoc->documentFile && Storage::disk('public')->exists($existingDoc->documentFile)) {
                        Storage::disk('public')->delete($existingDoc->documentFile);
                    }
                    
                    // Update existing document with new file
                    $file = $files[0]; // Take first file if multiple
                    $existingDoc->update([
                        'documentFile' => $file->store('task_documents', 'public'),
                        'documentName' => $file->getClientOriginalName(),
                        'documentDesc' => $desc,
                        'status' => TaskDocument::STATUS_PENDING,
                        'comment' => null,
                    ]);
                } else {
                    // No new file uploaded, just update description
                    $existingDoc->update([
                        'documentDesc' => $desc,
                        'status' => TaskDocument::STATUS_PENDING,
                        'comment' => null,
                    ]);
                }
            } else {
                // No existing document, create new one
                if (!empty($files)) {
                    $file = $files[0]; // Take first file if multiple
                    TaskDocument::create([
                        'taskcategory_id' => $categoryId,
                        'task_fill_id' => $taskFill->id,
                        'documentFile' => $file->store('task_documents', 'public'),
                        'documentName' => $file->getClientOriginalName(),
                        'documentDesc' => $desc,
                        'status' => TaskDocument::STATUS_PENDING,
                        'comment' => null,
                    ]);
                } else {
                    // Create document with description only (no file)
                    TaskDocument::create([
                        'taskcategory_id' => $categoryId,
                        'task_fill_id' => $taskFill->id,
                        'documentFile' => null,
                        'documentName' => 'No file uploaded',
                        'documentDesc' => $desc,
                        'status' => TaskDocument::STATUS_PENDING,
                        'comment' => null,
                    ]);
                }
            }
        }
    });

    return redirect()->route('tasks.index')->with('success', 'Task submitted successfully.');
}

    public function review(FormTask $task)
    {
        $this->checkPICAccess();
        
        if ($task->user_id !== Auth::id()) {
            abort(403, 'Unauthorized to review this task.');
        }
        
        $task->load([
            'taskCategories',
            'taskFills' => function ($query) {
                $query->whereIn('status', [TaskFill::STATUS_PENDING, TaskFill::STATUS_IN_REVIEW]);
            },
            'taskFills.user',
            'taskFills.fillDocuments.taskCategory'
        ]);
        
        return view('tasks.pic.review', compact('task'));
    }

    public function submitReview(Request $request, FormTask $task)
    {
        $this->checkPICAccess();

        if ($task->user_id !== Auth::id()) {
            abort(403, 'Unauthorized to review this task.');
        }

        $validated = $request->validate([
            'task_fill_id' => 'required|exists:form_task_fills,id',
            'action' => 'required|in:accept,reject,submit',
            'documents' => 'required|array',
            'documents.*.status' => 'required|in:approved,rejected',
            'documents.*.comment' => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($validated, $task) {
                $taskFill = TaskFill::where('form_task_id', $task->id)
                    ->where('id', $validated['task_fill_id'])
                    ->firstOrFail();

                // Update documents
                foreach ($validated['documents'] as $docId => $docData) {
                    $document = TaskDocument::where('id', $docId)
                        ->where('task_fill_id', $taskFill->id)
                        ->firstOrFail();

                    $document->update([
                        'status' => $docData['status'] === 'approved' ? TaskDocument::STATUS_ACCEPTED : TaskDocument::STATUS_REJECTED,
                        'comment' => $docData['comment'] ?? null,
                    ]);
                }

                // Update task fill status
                $taskFill->update([
                    'status' => $validated['action'] === 'accept' ? TaskFill::STATUS_ACCEPTED : TaskFill::STATUS_REJECTED,
                ]);
            });

            return back()->with('success', 'Review submitted successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to submit review', [
                'task_id' => $task->id,
                'task_fill_id' => $validated['task_fill_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to submit review: ' . $e->getMessage());
        }
    }

    public function destroy(FormTask $task)
    {
        $this->checkPICAccess();
        
        if ($task->user_id !== Auth::id()) {
            abort(403, 'Unauthorized to delete this task.');
        }

        if ($task->taskFills()->exists()) {
            return redirect()->route('tasks.index')->with('error', 'Cannot delete task with participants.');
        }

        $task->taskCategories()->delete();
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    public function downloadDocument(TaskDocument $document)
    {
        $user = Auth::user();
        $taskFill = $document->taskFill;
        $task = $taskFill->formTask;

        // Check authorization
        if (!(
            ($user->hasRole(['pic','manager','superadmin']) && $task->user_id === $user->id)
            || $taskFill->user_id === $user->id
        )) {
            abort(403);
        }

        $path = $document->documentFile;

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($path, $document->documentName);
    }

    public function previewDocument(TaskDocument $document)
    {
        $user = Auth::user();
        $taskFill = $document->taskFill;
        $task = $taskFill->formTask;

        // Check authorization
        if (!(
            ($user->hasRole(['pic','manager','superadmin']) && $task->user_id === $user->id)
            || $taskFill->user_id === $user->id
        )) {
            abort(403);
        }

        $path = $document->documentFile;

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->response($path);
    }

    public function closeTask(FormTask $task)
    {
        try {
            $this->checkPICAccess();
            
            if ($task->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $task->close();

            return response()->json([
                'success' => true,
                'message' => 'Task closed successfully!'
            ]);
        } catch (\Throwable $e) {
            Log::error('Error closing task: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to close task'
            ], 500);
        }
    }

    public function reopenTask(FormTask $task)
    {
        $this->checkPICAccess();
        
        if ($task->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $task->update(['status' => FormTask::STATUS_OPEN]);
        
        return response()->json([
            'success' => true,
            'message' => 'Task reopened successfully!'
        ]);
    }

    public function getSubmissionDetails($fillId)
{
    try {
        $user = Auth::user();
        
        // Find the task fill with relationships
        $taskFill = TaskFill::with([
            'user',
            'formTask',
            'fillDocuments.taskCategory'
        ])->findOrFail($fillId);
        
        $task = $taskFill->formTask;
        
        // Check authorization - only task owner can view submission details
        if (!$user->hasRole(['pic', 'manager', 'superadmin']) || $task->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this submission.'
            ], 403);
        }
        
        // Prepare documents data
        $documents = $taskFill->fillDocuments->map(function ($document) {
            return [
                'id' => $document->id,
                'document_file' => $document->documentFile,
                'document_name' => $document->documentName,
                'document_desc' => $document->documentDesc,
                'status' => $document->status,
                'comment' => $document->comment,
                'category_name' => $document->taskCategory->categoryName ?? 'Unknown Category',
                'category_desc' => $document->taskCategory->categoryDesc ?? null,
                'created_at' => $document->created_at->toISOString(),
                'updated_at' => $document->updated_at->toISOString(),
            ];
        });
        
        // Prepare submission data
        $submissionData = [
            'id' => $taskFill->id,
            'task_id' => $task->id,
            'filler_name' => $taskFill->fillerName,
            'user_email' => $taskFill->user->email ?? null,
            'status' => $taskFill->status,
            'created_at' => $taskFill->created_at->toISOString(),
            'updated_at' => $taskFill->updated_at->toISOString(),
            'documents' => $documents,
            'task_name' => $task->subjectName,
        ];
        
        return response()->json([
            'success' => true,
            'submission' => $submissionData
        ]);
        
    } catch (\Exception $e) {
        Log::error('Failed to get submission details', [
            'fill_id' => $fillId,
            'error' => $e->getMessage(),
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch submission details.'
        ], 500);
    }
}

    public function removeDocument(TaskDocument $document)
    {
        $user = Auth::user();
        
        if ($document->taskFill->user_id !== $user->id) {
            abort(403);
        }
        
        if (!$document->taskFill->isDraft() && !$document->taskFill->isRejected()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove documents from submitted tasks.'
            ], 400);
        }
        
        if (Storage::disk('public')->exists($document->documentFile)) {
            Storage::disk('public')->delete($document->documentFile);
        }
        
        $document->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Document removed successfully!'
        ]);
    }

    public function edit(FormTask $task)
{
    $this->checkPICAccess();
    
    if ($task->user_id !== Auth::id()) {
        abort(403, 'Unauthorized to edit this task.');
    }
    
    // Cek apakah task sudah ada yang mengisi
    $hasSubmissions = $task->taskFills()->whereIn('status', [
        TaskFill::STATUS_PENDING, 
        TaskFill::STATUS_ACCEPTED
    ])->exists();
    
    $task->load(['taskCategories']);
    
    return view('tasks.pic.edit', compact('task', 'hasSubmissions'));
}

public function update(Request $request, FormTask $task)
{
    $this->checkPICAccess();
    
    if ($task->user_id !== Auth::id()) {
        abort(403, 'Unauthorized to edit this task.');
    }
    
    $validated = $request->validate([
        'subjectName' => 'required|string|max:225',
        'subjectDesc' => 'required|string',
        'due_date' => 'nullable|date|after:now',
        'categories' => 'required|array|min:1',
        'categories.*.id' => 'nullable|exists:task_categories,id',
        'categories.*.categoryName' => 'required|string|max:225',
        'categories.*.categoryDesc' => 'nullable|string|max:225',
        'categories.*.action' => 'nullable|in:update,delete',
    ]);
    
    // Cek apakah ada submission yang sudah diterima
    $hasAcceptedSubmissions = $task->taskFills()
        ->where('status', TaskFill::STATUS_ACCEPTED)->exists();
    
    if ($hasAcceptedSubmissions && $request->has('categories')) {
        // Jika ada submission yang sudah diterima, batasi edit kategori
        foreach ($validated['categories'] as $cat) {
            if (isset($cat['action']) && $cat['action'] === 'delete') {
                // Cek apakah kategori ini sudah ada dokumen yang diterima
                $hasAcceptedDocs = TaskDocument::where('taskcategory_id', $cat['id'])
                    ->where('status', TaskDocument::STATUS_ACCEPTED)
                    ->exists();
                    
                if ($hasAcceptedDocs) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete category with accepted documents.'
                    ], 400);
                }
            }
        }
    }
    
    DB::transaction(function () use ($validated, $task) {
        // Update task basic info
        $task->update([
            'subjectName' => $validated['subjectName'],
            'subjectDesc' => $validated['subjectDesc'],
            'due_date' => $validated['due_date'],
        ]);
        
        // Handle categories
        $existingCategoryIds = $task->taskCategories()->pluck('id')->toArray();
        $submittedCategoryIds = [];
        
        foreach ($validated['categories'] as $cat) {
            if (isset($cat['id'])) {
                // Update existing category
                $category = TaskCategories::find($cat['id']);
                if ($category && $category->task_id === $task->id) {
                    if (isset($cat['action']) && $cat['action'] === 'delete') {
                        // Delete category jika tidak ada dokumen yang diterima
                        $hasAcceptedDocs = TaskDocument::where('taskcategory_id', $cat['id'])
                            ->where('status', TaskDocument::STATUS_ACCEPTED)
                            ->exists();
                        
                        if (!$hasAcceptedDocs) {
                            // Delete related documents first
                            TaskDocument::where('taskcategory_id', $cat['id'])->delete();
                            $category->delete();
                        }
                    } else {
                        // Update category
                        $category->update([
                            'categoryName' => $cat['categoryName'],
                            'categoryDesc' => $cat['categoryDesc'] ?? null,
                        ]);
                        $submittedCategoryIds[] = $cat['id'];
                    }
                }
            } else {
                // Create new category
                $newCategory = $task->taskCategories()->create([
                    'categoryName' => $cat['categoryName'],
                    'categoryDesc' => $cat['categoryDesc'] ?? null,
                ]);
                $submittedCategoryIds[] = $newCategory->id;
            }
        }
        
        // Reset status draft submissions yang terpengaruh perubahan kategori
        $draftFills = $task->taskFills()->where('status', TaskFill::STATUS_DRAFT)->get();
        foreach ($draftFills as $fill) {
            // Hapus dokumen yang kategorinya sudah tidak ada
            $fill->fillDocuments()
                ->whereNotIn('taskcategory_id', $submittedCategoryIds)
                ->each(function ($doc) {
                    if (Storage::disk('public')->exists($doc->documentFile)) {
                        Storage::disk('public')->delete($doc->documentFile);
                    }
                    $doc->delete();
                });
        }
    });
    
    return response()->json([
        'success' => true,
        'message' => 'Task updated successfully!',
        'redirect' => route('tasks.show', $task->id)
    ]);
}
}