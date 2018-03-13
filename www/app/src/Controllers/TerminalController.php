<?php
namespace Alph\Controllers;

class TerminalController {
    public static function index(array $params) {
        return (new View("terminal"))->render();
    }
}