<?php
use Alph\Services\Route;

Route::exec(["GET"], "/", "HomeController::index");
Route::exec(["GET"], "/assets/{filepath*}", "AssetsController::find");

Route::exec(["GET"], "/terminal", "TerminalController::index");
Route::exec(["GET"], "/terminal/{mac}", "TerminalController::index");

Route::exec(["GET"], "/signup", "AccountController::signup");
Route::exec(["POST"], "/signup", "AccountController::signupaction");

Route::exec(["GET"], "/signin", "AccountController::signin");
Route::exec(["POST"], "/signin", "AccountController::signinaction");

Route::exec(["POST"], "/signin", "AccountController::signinaction");

Route::exec(["GET"], "/validate/{code}", "AccountController::validate");

Route::exec(["GET"], "/about/tos", "AboutController::tos");