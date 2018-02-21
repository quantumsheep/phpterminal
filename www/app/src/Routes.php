<?php
use Alph\Controllers\TerminalController;
use Alph\Services\Route;

Route::exec(["GET"], "/assets/{file}", "AssetsController::find");

Route::exec(["GET"], "/terminal", "TerminalController::index");
Route::exec(["GET"], "/terminal/{mac}", "TerminalController::index");