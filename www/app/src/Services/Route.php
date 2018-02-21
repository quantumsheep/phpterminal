<?php
namespace Alph\Services;

class Route {
    /**
     * Try routing
     */
    public static function exec($methods, $route, $action) {
        // Check if a route is already validated
        if($GLOBALS['ROUTED']) return;

        // Check if the client route is not the same as the route method(s)
        if(!in_array($_SERVER['REQUEST_METHOD'], $methods)) return false;

        // Check if client requested URI is the same as the route string
        if($_SERVER['REQUEST_URI'] == $route) {
            // Launch the action and echo the returned string
            echo call_user_func("\\Alph\\Controllers\\" . $action, []);
            return $GLOBALS['ROUTED'] = true;
        }

        // Cut the route string to multiple parts
        $parts = explode('/', $route);

        // Cut the client requested URI to multiple parts
        $client_uri = explode('/', $_SERVER['REQUEST_URI']);

        // Count the route parts length
        $parts_length = count($parts);

        // Counth the client requested URI length
        $client_uri_length = count($client_uri);

        // Check if the route and the client requested URI have not the same length
        if($parts_length !== $client_uri_length) return false;

        $vars = [];

        for($i = 0; $i < $parts_length; $i++) {
            if($parts[$i][0] == '{') {
                $vars[preg_replace("/\{(.*?)\}/", "$1", $parts[$i])] = $client_uri[$i];
            } else if($parts[$i] !== $client_uri[$i]) {
                return false;
            }
        }
        
        echo call_user_func("\\Alph\\Controllers\\" . $action, $vars);
        return $GLOBALS['ROUTED'] = true;
    }
}