<?php
use Alph\Services\Route;

Route::exec(["GET"], "/", "HomeController::index");
Route::exec(["GET"], "/assets/{filepath*}", "AssetsController::find");

Route::exec(["GET"], "/terminal", "TerminalController::index");
Route::exec(["GET"], "/terminal/{mac}", "TerminalController::index");

Route::exec(["GET"], "/signup", "AccountController::signup");
Route::exec(["POST"], "/signup", "AccountController::signupaction");

Route::exec(["GET"], "/about/tos", "AboutController::tos");