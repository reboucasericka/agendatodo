<?php

use App\Http\Controllers\MeetingController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Página pública (Landing)
|--------------------------------------------------------------------------
*/
// LANDING
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home'); //  IMPORTANTE
/*
|--------------------------------------------------------------------------
| APP PRINCIPAL (depois do login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/app', [TaskController::class, 'index'])->name('app');

    Route::redirect('/breve/reunioes', '/reunioes');

    Route::get('/reunioes', [MeetingController::class, 'index'])->name('meetings.index');
    Route::post('/reunioes', [MeetingController::class, 'store'])->name('meetings.store');
    Route::put('/reunioes/{meeting}', [MeetingController::class, 'update'])->name('meetings.update');
    Route::delete('/reunioes/{meeting}', [MeetingController::class, 'destroy'])->name('meetings.destroy');

    Route::get('/breve/{feature}', function () {
        return Inertia::render('ComingSoon', [
            'title' => 'Em breve',
            'body' => 'Esta secção não faz parte do produto atual. Usa as tarefas, a lista semanal ou as reuniões no menu.',
            'links' => [
                [
                    'label' => 'Ir para tarefas',
                    'href' => route('app'),
                ],
                [
                    'label' => 'Lista semanal',
                    'href' => route('app', ['view' => 'week', 'status' => 'pending']),
                ],
                [
                    'label' => 'Reuniões',
                    'href' => route('meetings.index'),
                ],
            ],
        ]);
    })->name('feature.placeholder');

    // TASKS
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::post('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
});
/*
|--------------------------------------------------------------------------
| Redirecionamento padrão após login
|--------------------------------------------------------------------------
*/
// DASHBOARD (compatibilidade com Breeze)
Route::get('/dashboard', function () {
    return redirect()->route('app');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';
