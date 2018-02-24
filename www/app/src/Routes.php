<?php
use Alph\Services\Route;

Route::exec(["GET"], "/", "HomeController::index");
Route::exec(["GET"], "/assets/{filepath*}", "AssetsController::find");

Route::exec(["GET"], "/terminal", "TerminalController::index");
Route::exec(["GET"], "/terminal/{mac}", "TerminalController::index");

Route::exec(["GET"], "/connect", "AccountController::connect");
Route::exec(["GET"], "/signup", "AccountController::signup");
Route::exec(["GET"], "/editaccount", "AccountController::editaccount");