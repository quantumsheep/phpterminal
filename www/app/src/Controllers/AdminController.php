<?php
namespace Alph\Controllers;

use Alph\Controllers\View;
use Alph\Services\Database;

class AdminController
{
    public static function index(array $params)
    {
        return (new View("admin/index"))->render();        
    }
}