<?php
namespace Alph\Controllers;

class HomeController {
    public static function index($params) {
        return (new View("index"))->render();
    }
}