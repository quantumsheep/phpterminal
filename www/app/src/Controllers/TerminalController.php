<?php
namespace Alph\Controllers;

class TerminalController {
    public static function index($params) {
        return (new View("terminal"))->render();
    }
}