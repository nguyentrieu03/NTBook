<?php

use App\Http\Controllers\Web\AttributeController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('/admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('dashboard.index');
    })->middleware(['verified'])->name('dashboard');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', function () {
            return view('products.index');
        })->name('index');

        Route::get('/create', function () {
            return view('products.form');
        })->name('create');
    });

    Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
    Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);
    Route::resource('attributes', AttributeController::class)->only(['store', 'update', 'destroy']);
});
