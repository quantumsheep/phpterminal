<?php
namespace Alph\Controllers;

use Alph\Controllers\View;

class AboutController
{
    public static function tos(array $params)
    {
        return (new View("about/tos"))->render();
    }
}
