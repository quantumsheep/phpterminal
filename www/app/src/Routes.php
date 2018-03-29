<?php
use Alph\Services\Route;

Route::checkAccess("ErrorController::e403");

Route::exec(["GET"], "/assets/{filepath*}", "AssetsController::find");
Route::exec(["GET"], "/", "HomeController::index");

Route::exec(["GET"], "/signup", "AccountController::signup");
Route::exec(["POST"], "/signup", "AccountController::signupaction");

Route::exec(["GET"], "/signin", "AccountController::signin");
Route::exec(["POST"], "/signin", "AccountController::signinaction");

Route::exec(["GET"], "/logout", "AccountController::logout");
Route::exec(["GET"], "/validate/{code}", "AccountController::validate");

Route::exec(["GET"], "/terminal", "TerminalController::terminal_list");
Route::exec(["GET"], "/terminal/{mac}", "TerminalController::terminal");

Route::exec(["GET"], "/admin", "AdminController::index");
Route::exec(["GET"], "/admin/terminal", "AdminController::terminal");
Route::exec(["GET"], "/admin/terminal/{mac}", "AdminController::terminal");
Route::exec(["GET"], "/admin/network", "AdminController::network");
Route::exec(["GET"], "/admin/network/{mac}", "AdminController::network");
Route::exec(["GET"], "/admin/account", "AdminController::account");
Route::exec(["GET"], "/admin/account/{idaccount}", "AdminController::account");

Route::exec(["GET"], "/about/tos", "AboutController::tos");

Route::exec(["GET"], "/sitemap.xml", function() {
    echo \Alph\Controllers\AssetsController::find(["filepath" => "sitemap.xml"]);
});

Route::checkRouted("ErrorController::e404");