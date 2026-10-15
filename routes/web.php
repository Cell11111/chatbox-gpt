<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\RequirementController;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function (RequirementController $controller) {
    $data = $controller->dashboard();
    return view('welcome', $data);
})->name('welcome');

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);


Route::middleware(['auth'])->group(function () {
    // Profile routes
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    
    Route::get('/submit-requirements', [RequirementController::class, 'showProject']);

    Route::get('/review-defects', [RequirementController::class, 'showDefects']);

    Route::get('/change-log', [RequirementController::class, 'showChangeLog']);

    Route::get('/view-conflict', [RequirementController::class, 'showConflicts']);

    Route::get('/edit-requirement', function () {
        return view('edit-requirement');
    });

    Route::get('/add-project', function () {
        $engineers = \App\Models\User::where('role', 'software engineer')->get();
        return view('add-project', compact('engineers'));
    })->middleware(['auth', 'noSoftwareEngineer']);

    Route::get('/view-project', [RequirementController::class, 'viewProjects'])->name('view.projects');

    Route::get('/add-feature', [RequirementController::class, 'showAddFeature']);

    // Review changes route handled by controller (manager view)


    Route::post('/generate-requirements', [ChatbotController::class, 'generateRequirements']);
    Route::post('/clarify-requirements', [ChatbotController::class, 'clarifyRequirements']);
    Route::post('/save-requirements', [RequirementController::class, 'saveRequirements'])->middleware('auth');
    ;
    Route::post('/analyze-requirements', [ChatBotController::class, 'analyzeRequirements']);
    Route::post('/analyze', [ChatBotController::class, 'analyzeRequirements']);
    Route::post('/update-status', [App\Http\Controllers\RequirementController::class, 'updateStatus']);
    Route::get('/projects/{project}/categories', [RequirementController::class, 'getCategories']);
    Route::post('/auto-fix-requirement', [\App\Http\Controllers\ChatBotController::class, 'autoFix']);
    Route::post('/ignore-defect', [RequirementController::class, 'ignoreDefect']);
    Route::post('/analyze-impact', [ChatBotController::class, 'analyzeImpact'])->name('analyze.impact');
    Route::post('/analyze-all-requirements', [ChatBotController::class, 'analyzeRequirementsForConflicts']);
    Route::post('/suggest-combined', [ChatbotController::class, 'suggestCombined'])->name('suggest.combined');
    Route::get('/view-conflict', [RequirementController::class, 'showConflicts']);
    Route::delete('/requirements/{id}', [RequirementController::class, 'destroy'])->name('requirements.destroy');
    Route::resource('requirements', RequirementController::class);
    Route::post('/project-store', [RequirementController::class, 'projectStore']);
    Route::put('/projects/{id}', [RequirementController::class, 'projectUpdate'])->name('project.update');
    Route::get('/engineers', [RequirementController::class, 'listEngineers'])->name('engineers.list');
    Route::post('/suggest-features', [ChatBotController::class, 'suggestFeatures'])->name('project.suggest-features');
    Route::get('/view-project', [RequirementController::class, 'viewProjects'])->name('view.projects');
    Route::post('/feature-store', [RequirementController::class, 'featureStore']);
    Route::get('/projects/{id}', [RequirementController::class, 'getProject'])->name('project.details');
    Route::get('/review-changes', [RequirementController::class, 'showPendingUpdates']);
    Route::get('/my-changes', [RequirementController::class, 'showMyPendingUpdates'])->name('my.changes');
    Route::post('/pending-updates/{id}/review', [RequirementController::class, 'reviewChange'])
        ->name('pending-updates.review');
    Route::post('/apply-resolution', [ChatbotController::class, 'applyResolution'])->name('apply.resolution');
    Route::get('/projects', [RequirementController::class, 'list']);
    Route::get('/export-report', [RequirementController::class, 'exportReport'])->name('export-report');
    Route::post('/projects/{project}/feedback', [RequirementController::class, 'storeFeedback'])->name('submitFeedback');
    Route::delete('/feedback/{feedback}', [RequirementController::class, 'destroyFeedback'])->name('deleteFeedback');
    Route::delete('/features/{id}', [RequirementController::class, 'destroyFeature'])->name('deleteFeature');
    Route::post('/dismiss-conflict', [RequirementController::class, 'dismissConflict'])->name('conflict.dismiss');
    Route::delete('/projects/{id}', [RequirementController::class, 'destroyProject'])->name('project.destroy');
    Route::post('/pending-resolutions/{id}/review', [RequirementController::class, 'reviewResolution'])
        ->name('pending-resolutions.review');
    Route::post('/requirement-rollback', [RequirementController::class, 'rollbackVersion'])
        ->name('requirement.rollback')->middleware('noSoftwareEngineer');
    Route::post('/project-rollback', [RequirementController::class, 'rollbackProject'])
        ->name('project.rollback')->middleware('noSoftwareEngineer');
    Route::get('/project-version-snapshot', [RequirementController::class, 'projectVersionSnapshot'])
        ->name('project.version.snapshot');
    
    // Project Snapshot Routes
    Route::post('/project-snapshot/save', [RequirementController::class, 'saveProjectSnapshot'])
        ->name('project.snapshot.save');
    Route::get('/project/{projectId}/snapshots', [RequirementController::class, 'getProjectSnapshots'])
        ->name('project.snapshots.list');
    Route::post('/project-snapshot/rollback', [RequirementController::class, 'rollbackToSnapshot'])
        ->name('project.snapshot.rollback');
    Route::delete('/project-snapshot/{snapshotId}', [RequirementController::class, 'deleteSnapshot'])
        ->name('project.snapshot.delete')->middleware('noSoftwareEngineer');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


