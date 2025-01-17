<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group([], function (){
    
    Route::get('/', [ProfileController::class, 'index'])->name('index');

    Route::get('/teste', [ProfileController::class, 'teste']);

    Route::get('/infoanime/{id}', [ProfileController::class, 'infoanime']);
    Route::get('/listadeanimes', [ProfileController::class, 'listadeanimes']);

    Route::get('/list-ranking', [ProfileController::class, 'list_ranking']);

    Route::get('/plusanimec/{id}', [ProfileController::class, 'plusanimec']);
    Route::get('/decreanimec/{id}', [ProfileController::class, 'decreanimec']);
    
    
});


Route::get('/dashboard', [ProfileController::class, 'home'])->middleware(['auth', 'verified'])->name('dashboard');

/*Route::middleware('auth')->group(function () {*/
Route::group(['middleware' => ['auth']], function () {
    
    Route::get('/home', [ProfileController::class, 'home'])->name('home');
    Route::get('/apache2', [ProfileController::class, 'apache2'])->name('apache2');
    Route::get('/linuxComandos', [ProfileController::class, 'linuxComandos'])->name('linuxComandos');
    Route::get('/createProject', [ProfileController::class, 'createProject'])->name('createProject');
    Route::get('/laravel-migrations', [ProfileController::class, 'laravelMigrations'])->name('laravelMigrations');
    Route::get('/laravel-eloquent', [ProfileController::class, 'laravelEloquent'])->name('laravelEloquent');
    Route::get('/laravel-auth', [ProfileController::class, 'laravelAuth'])->name('laravelAuth');
    
    Route::get('/formassistindo', [ProfileController::class, 'formassistindo'])->name('formassistindo');
    Route::get('/formassistindo/edit/{id}', [ProfileController::class, 'edit_assistindo']);
    Route::get('/formassistindo/plusanime/{id_anime}/{id_assist}', [ProfileController::class, 'plusanime'])->name('plusanime');
    Route::get('/formassistindo/decreanime/{id_anime}/{id_assist}', [ProfileController::class, 'decreanime'])->name('decreanime');
    Route::get('/formassistindo/addranking/{id}', [ProfileController::class, 'addranking'])->name('addranking');
    Route::get('/formassistindo/addcontinua/{id}', [ProfileController::class, 'addcontinua']);
    Route::get('/create_parados/{id}', [ProfileController::class, 'addparados']);
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/lista-animes-parados', [ProfileController::class, 'listAnimesParados'])->name('listAnimesParados');
    
    Route::get('/plusNota/{id}', [ProfileController::class, 'plusNota'])->name('plusNota');
    Route::get('/decreNota/{id}', [ProfileController::class, 'decreNota'])->name('decreNota');
    
    Route::post('/formassistindo', [ProfileController::class, 'assistindoAdd'])->name('assistindoAdd');
    
    Route::put('/formassistindo/update/{id}', [ProfileController::class, 'update_assistindo']);
    
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::delete('/destroy_assistindo/{id}', [ProfileController::class, 'destroy_assistindo']);

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
});

Route::group(['middleware' => ['auth', 'admin']], function (){
        
    Route::get('/admin/formanime', [ProfileController::class, 'formAnime'])->name('formAnime');
        Route::post('/admin/formanime', [ProfileController::class, 'animeAdd'])->name('animeAdd');
        Route::post('/animeAdd2', [ProfileController::class, 'animeAdd2'])->name('animeAdd2');
});


require __DIR__.'/auth.php';
