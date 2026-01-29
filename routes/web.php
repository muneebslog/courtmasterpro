<?php

use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\MatchReportController;


use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
    //redirect to login page
    // return redirect()->route('login');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::livewire('/tournaments/{tournament}', 'pages::tournaments.show')
    ->middleware(['auth', 'verified'])
    ->name('tournaments.show');

// define the event.show model route
Route::livewire('/events/{event}', 'pages::events.show')
    ->middleware(['auth', 'verified'])
    ->name('events.show');
// matches.show
Route::livewire('/matches/{match}', 'pages::matches.show')
    ->middleware(['auth', 'verified'])
    ->name('matches.show');

// matches.controlpanel
Route::livewire('/matches/{match}/controlpanel', 'pages::matches.controlpanel')
    ->middleware(['auth', 'verified'])
    ->name('matches.controlpanel');


Route::get('/pdf-test', function () {
    $pdf = Pdf::loadHTML('<h1>PDF Working</h1><p>Dompdf installed correctly.</p>');
    return $pdf->download('test.pdf');
});


Route::get('/matches/{match}/report', [MatchReportController::class, 'download'])
    ->name('matches.report.full');


Route::get('/matches/{match}/report/summary', [MatchReportController::class, 'downloadSummary'])
    ->name('matches.report.summary');

// route for 
Route::livewire('/settings/roles', 'core.roles')
    ->middleware(['auth', 'verified'])
    ->name('roles');

Route::livewire('viewer/matches', 'pages::viewer.matches')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


require __DIR__ . '/settings.php';
