<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view('welcome');
    //redirect to login page
    return redirect()->route('login');
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


require __DIR__.'/settings.php';
