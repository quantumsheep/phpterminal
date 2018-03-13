<?php
namespace Alph\Controllers;

class HomeController {
    public static function index(array $params) {
        return (new View("index"))->render();
    }
}