<?php

use App\Http\Controllers\InternalLetterController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    CategoryBudgetController,
    CategoryController,
    ClassesController,
    ClassEvaluationController,
    DashboardController,
    DropdownController,
    ExternalLetterController,
    ParticipantController,
    ParticipantsPaymentController,
    PaymentController,
    ProfileController,
    ProgramController,
    TaskCategoryController,
    TaskDocumentController,
    TnaController,
    TrainingOperationsMonitoringController,
    UserManagementController
};
use App\Models\ParticipantsPayment;

// Halaman utama
Route::get('/', fn() => view('auth.login'));


Route::middleware('auth')->group(function () {
    // Dashboard 
    
    // Satu-satunya route dashboard utama
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard.index');

    Route::get('/pic-dashboard', [DashboardController::class, 'picDashboard'])
    ->name('dashboard.pic')
    ->middleware('role:manager|executive|pic');

Route::get('/participant-dashboard', [DashboardController::class, 'participantDashboard'])
    ->name('dashboard.participant')
    ->middleware('role:participant');
    
    // Dashboard API endpoints for dynamic content
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        // Calendar events API
        Route::get('/calendar-events', [DashboardController::class, 'getCalendarEvents'])
             ->name('calendar-events');
        
        // Class details modal
        Route::get('/class-details/{classId}', [DashboardController::class, 'getClassDetails'])
             ->name('class-details');
        
        // Admin-specific dashboard APIs
        Route::middleware('role:superadmin|manager')->group(function () {
            Route::get('/tna-details/{tnaId}', [DashboardController::class, 'getTnaDetails'])
                 ->name('tna-details');
            Route::get('/export-report', [DashboardController::class, 'exportReport'])
                 ->name('export-report');
            Route::get('/export-tna-report', [DashboardController::class, 'exportTnaReport'])
                 ->name('export-tna-report');
        });
        
        // PIC-specific dashboard APIs
        Route::middleware('role:pic')->group(function () {
            Route::post('/quick-add-class', [DashboardController::class, 'quickAddClass'])
                 ->name('quick-add-class');
            Route::post('/complete-task/{taskId}', [DashboardController::class, 'completeTask'])
                 ->name('complete-task');
            Route::post('/update-participant-status/{participantId}', [DashboardController::class, 'updateParticipantStatus'])
                 ->name('update-participant-status');
        });
        
        // Participant-specific dashboard APIs
        Route::middleware('role:participant')->group(function () {
            Route::get('/my-classes', [DashboardController::class, 'getMyClasses'])
                 ->name('my-classes');
            Route::post('/confirm-attendance/{classId}', [DashboardController::class, 'confirmAttendance'])
                 ->name('confirm-attendance');
            Route::post('/submit-feedback/{classId}', [DashboardController::class, 'submitFeedback'])
                 ->name('submit-feedback');
            Route::get('/certificate/{classId}', [DashboardController::class, 'downloadCertificate'])
                 ->name('certificate');
        });
    });
    
    // PIC-specific routes
    Route::prefix('pic')->name('pic.')->middleware('role:pic')->group(function () {
        Route::get('/class-details/{classId}', [DashboardController::class, 'getPicClassDetails'])
             ->name('class-details');
        Route::get('/class-participants/{classId}', [DashboardController::class, 'getClassParticipants'])
             ->name('class-participants');
        Route::get('/class-payments/{classId}', [DashboardController::class, 'getClassPayments'])
             ->name('class-payments');
    });
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // TNA
    Route::resource('tna', TnaController::class);
    Route::get('/tna/{tnaId}/view', [TnaController::class, 'show'])->name('tna.view');

    // Category
    Route::get('/category/create', [CategoryController::class, 'create'])->name('category.create');
    Route::post('/category/store', [CategoryController::class, 'store'])->name('category.store');
    Route::post('/category/store-multiple', [CategoryController::class, 'storeMultiple'])->name('category.store.multiple');
    Route::post('/category/training-program/store', [CategoryController::class, 'storeTrainingProgram'])->name('training_program.store');
    Route::post('/category/store-training-program', [CategoryController::class, 'storeSingleTrainingProgram'])
    ->name('category.store.training.program');

    // Program
    Route::resource('program', ProgramController::class);
    Route::get('/program/{id}/view', [ProgramController::class, 'show'])->name('program.view');
    Route::get('/program/cancel', [ProgramController::class, 'cancel'])->name('program.cancel');
    Route::get('/program/search', [ProgramController::class, 'search'])->name('program.search');
    Route::get('/program/filter', [ProgramController::class, 'filter'])->name('program.filter');
    Route::get('/program/{id}/export-pdf', [ProgramController::class, 'exportPdf'])->name('program.export.pdf');
    Route::get('/program/{program_id}/budget', [CategoryBudgetController::class, 'create'])->name('category-budget.create');
    Route::post('/program/{program_id}/budget', [CategoryBudgetController::class, 'store'])->name('category-budget.store');

    // Classes
    Route::prefix('class')->group(function () {
        Route::get('/', [ClassesController::class, 'index'])->name('classes.index');
        Route::get('/create', [ClassesController::class, 'create'])->name('classes.create');
        Route::post('/store', [ClassesController::class, 'store'])->name('classes.store');
        Route::get('/{id}/edit', [ClassesController::class, 'edit'])->name('classes.edit');
        Route::put('/{id}', [ClassesController::class, 'update'])->name('classes.update');
        Route::put('/{id}/update-agenda', [ClassesController::class, 'updateAgenda'])->name('classes.updateAgenda');
        Route::get('/{id}/view', [ClassesController::class, 'show'])->name('classes.view');
        Route::post('/{id}/store-agenda', [ClassesController::class, 'storeAgenda'])->name('classes.storeAgenda');
        Route::delete('/{id}', [ClassesController::class, 'destroy'])->name('classes.destroy');
        Route::get('/duplicate/{id}', [ClassesController::class, 'duplicate'])->name('classes.duplicate');
        Route::get('/{class_id}/export-pdf', [ClassesController::class, 'exportPdf'])->name('classes.exportPdf');
        Route::get('/get-next-batch', [ClassesController::class, 'getNextBatch'])->name('classes.get-next-batch');
    });

    // Participants
    Route::resource('participants', ParticipantController::class)->except(['edit', 'update']);
    Route::get('/participants/search', [ParticipantController::class, 'search'])->name('participant.search');
    Route::get('/participants/filter', [ParticipantController::class, 'filter'])->name('participant.filter');
    Route::get('/participants/export', [ParticipantController::class, 'export'])->name('participant.export');
    Route::get('/participants/{id}/view', [ParticipantController::class, 'show'])->name('participant.view');
    Route::post('/participants/import', [ParticipantController::class, 'import'])->name('participants.import');
    Route::delete('/participants/{id}', [ParticipantController::class, 'destroy'])->name('participants.destroy');
    
    Route::get('/participants/export-final-template/{class_id}', [ParticipantController::class, 'exportFinalTemplate'])
    ->name('participants.export.final-template');

   Route::post('/participants/import-final-scores/{class}', [ParticipantController::class, 'importFinalScores'])
    ->name('participants.import.final-scores');


    // Participants by Class
    Route::prefix('classes/{class_id}/participants')->group(function () {
        Route::get('/', [ParticipantController::class, 'indexByClass'])->name('participants.byClassIndex');
        Route::get('/edit', [ParticipantController::class, 'editByClass'])->name('participants.editByClass');
        Route::put('/update', [ParticipantController::class, 'updateByClass'])->name('participants.updateByClass');
        Route::delete('/{id}', [ParticipantController::class, 'destroyByClass'])->name('participants.destroyByClass');
    });

    // Edit/Update Final & Temp
    Route::prefix('participants')->group(function () {
        Route::get('/final/{id}/edit', [ParticipantController::class, 'editFinal'])->name('participants.editFinal');
        Route::get('/temp/{id}/edit', [ParticipantController::class, 'editTemp'])->name('participants.editTemp');
        Route::put('/final/{id}', [ParticipantController::class, 'updateFinal'])->name('participants.updateFinal');
        Route::put('/temp/{id}', [ParticipantController::class, 'updateTemp'])->name('participants.updateTemp');
        Route::delete('/temp/{id}', [ParticipantController::class, 'deleteTemp'])->name('participants.deleteTemp');
        Route::get('/{id}/generate-report', [ParticipantController::class, 'generateReport'])->name('participant.generateReport');
    });
    Route::get('/participants/temp/export/{class_id}', [ParticipantController::class, 'exportTempParticipants'])
    ->name('participants.temp.export');


    // Payments (General)
    Route::resource('payments', PaymentController::class);
    Route::get('/payments/{id}/view', [PaymentController::class, 'show'])->name('payments.view');
    Route::post('/payments/{id}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
    Route::post('/payments/{id}/reject', [PaymentController::class, 'reject'])->name('payments.reject');
    Route::get('/payments/{id}/document', [PaymentController::class, 'show'])->name('payments.document');
    Route::get('/payments/{id}/showDocument', [PaymentController::class, 'showDocument'])->name('payments.showDocument');
    Route::get('/programs/{programId}/remaining-budget', [PaymentController::class, 'getRemainingBudget']);
    Route::post('/payments/{id}/checkbypic', [PaymentController::class, 'checkbypic'])->name('payments.checkbypic');
    Route::post('/payments/{id}/checkbymanager', [PaymentController::class, 'checkbymanager'])->name('payments.checkbymanager');
    Route::post('/{id}/approve', [PaymentController::class, 'approve'])->name('payments.approve');
    Route::post('/{id}/reject', [PaymentController::class, 'reject'])->name('payments.reject');

    // Participant Payments (Detail)
    Route::prefix('participants-payment')->group(function () {
        Route::get('/', [ParticipantsPaymentController::class, 'index'])->name('participants-payment.index');
        Route::get('/create', [ParticipantsPaymentController::class, 'create'])->name('participants-payment.create');
        Route::post('/store', [ParticipantsPaymentController::class, 'store'])->name('participants-payment.store');
        Route::get('/{id}', [ParticipantsPaymentController::class, 'show'])->name('participants-payment.show');
        Route::get('/{id}/edit', [ParticipantsPaymentController::class, 'edit'])->name('participants-payment.edit');
        Route::put('/{id}', [ParticipantsPaymentController::class, 'update'])->name('participants-payment.update');
        Route::get('/{id}/document', [ParticipantsPaymentController::class, 'showDocument'])->name('participants-payment.showDocument');
        Route::post('/{id}/approve', [ParticipantsPaymentController::class, 'approve'])->name('participants-payment.approve');
        Route::post('/{id}/reject', [ParticipantsPaymentController::class, 'reject'])->name('participants-payment.reject');
        Route::post('/{id}/checkbypic', [ParticipantsPaymentController::class, 'checkbypic'])->name('participants-payment.checkbypic');
        Route::post('/{id}/checkbymanager', [ParticipantsPaymentController::class, 'checkbymanager'])->name('participants-payment.checkbymanager');
    });
    
    Route::post('/bulk-action', [PaymentController::class, 'bulkAction'])->name('bulk.action');

    // Dropdown
    Route::get('/select-program-class', [DropdownController::class, 'index'])->name('step1.selectProgramClass');
    Route::post('/store-selection', [DropdownController::class, 'storeSelection'])->name('step1.storeSelection');
    Route::get('/get-classes/{programId}', [ParticipantsPaymentController::class, 'getClasses'])->name('get.classes');

    //eval class
    Route::get('/evaluation/upload/{class}', [ClassEvaluationController::class, 'form'])->name('evaluation.form');
    Route::post('/evaluation/store', [ClassEvaluationController::class, 'store'])->name('evaluation.store');

         // Internal Letter
        Route::get('/internal-letters/create', [InternalLetterController::class, 'create'])->name('internal-letters.create');
        Route::post('/internal-letters', [InternalLetterController::class, 'store'])->name('internal-letters.store');
        Route::get('/internal-letters/{id}/edit', [InternalLetterController::class, 'edit'])->name('internal-letters.edit');
        Route::put('/internal-letters/{id}', [InternalLetterController::class, 'update'])->name('internal-letters.update');
        Route::delete('/internal-letters/{id}', [InternalLetterController::class, 'destroy'])->name('internal-letters.destroy');


        // External Letter
        Route::get('/external-letters/create', [ExternalLetterController::class, 'create'])->name('external-letters.create');
        Route::post('/external-letters', [ExternalLetterController::class, 'store'])->name('external-letters.store');
        Route::get('/external-letters/{id}/edit', [ExternalLetterController::class, 'edit'])->name('external-letters.edit');
        Route::put('/external-letters/{id}', [ExternalLetterController::class, 'update'])->name('external-letters.update');
        Route::delete('/external-letters/{id}', [ExternalLetterController::class, 'destroy'])->name('external-letters.destroy');


        Route::get('/letters', [ExternalLetterController::class, 'index'])->name('letters.index');

        //elapse 
        Route::get('/elapse', function () {
            return view('elapse.index');
        });

       // Resource Controller: CRUD dasar untuk FormTask (PIC only)
Route::resource('tasks', TaskController::class)->names([
    'index'  => 'tasks.index',
    'create' => 'tasks.create',
    'store'  => 'tasks.store',
    'show'   => 'tasks.show',
]);

// Custom Routes untuk Task Management
Route::prefix('tasks')->name('tasks.')->group(function () {
    // Join task dengan kode akses
    Route::post('/join', [TaskController::class, 'join'])->name('join');
    Route::get('/join', [TaskController::class, 'showJoinForm'])->name('join-form');

    Route::get('/submission/{fill}/details', [TaskController::class, 'getSubmissionDetails'])->name('submission.details');

    // Isi form tugas
    Route::get('/{task}/fill', [TaskController::class, 'fill'])->name('fill');
    Route::post('/{task}/submit-fill', [TaskController::class, 'submitFill'])->name('submit-fill');

    Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('edit');
    Route::put('/{task}', [TaskController::class, 'update'])->name('update');

    // Upload ulang dokumen yang ditolak
    Route::post('/document/{document}/reupload', [TaskController::class, 'reuploadDocument'])->name('document.reupload');

    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // Review tugas dan submit hasil review
    Route::get('/{task}/review', [TaskController::class, 'review'])->name('review');
    Route::post('/{task}/submit-review', [TaskController::class, 'submitReview'])->name('submit-review');

    // Approve/Reject semua dokumen sekaligus
    Route::post('/{task}/approve-all', [TaskController::class, 'approveAll'])->name('approve-all');
    Route::post('/{task}/reject-all', [TaskController::class, 'rejectAll'])->name('reject-all');

    // Tutup dan buka kembali tugas
    Route::post('/{task}/close', [TaskController::class, 'closeTask'])->name('close');
    Route::post('/{task}/reopen', [TaskController::class, 'reopenTask'])->name('reopen');

    // Preview dan download dokumen
    Route::get('/document/{document}/preview', [TaskController::class, 'previewDocument'])->name('preview-document');
    Route::get('/document/{document}/download', [TaskController::class, 'downloadDocument'])->name('download-document');

    // Review status dokumen individual (approve/reject + comment)
    Route::post('/task-documents/{document}/review', [TaskController::class, 'reviewDocument'])->name('task.documents.review');

    // Hapus dokumen
    Route::delete('/document/{document}/remove', [TaskController::class, 'removeDocument'])->name('remove-document');
});

Route::get('/monitoring/training-operations', [TrainingOperationsMonitoringController::class, 'index'])->name('monitoring.training-operations');
Route::get('/monitoring/training-operations/export-pdf', [TrainingOperationsMonitoringController::class, 'exportPdf'])->name('monitoring.export-pdf');
Route::get('/monitoring/pic/{picId}', [TrainingOperationsMonitoringController::class, 'picDetail'])->name('monitoring.pic-detail');

    

    // Superadmin only
    Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
    
    // User role management
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/assign-role', [UserManagementController::class, 'assignRole'])->name('users.assignRole');
    Route::post('/users/{user}/remove-role', [UserManagementController::class, 'removeRole'])->name('users.removeRole');
    Route::post('/users/bulk-assign-role', [UserManagementController::class, 'bulkAssignRole'])->name('users.bulkAssignRole');
    
});
});

require __DIR__.'/auth.php';
