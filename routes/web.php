<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Nota: las transiciones de carga y alertas se implementan en el
// frontend React (condominio) según los requisitos UX. El backend
// Laravel no modifica esas animaciones.
