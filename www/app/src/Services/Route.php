<?php
namespace Alph\Services;

class Route {
    /**
     * Try routing
     * 
     * @param array $methods
     * @param string $route
     * @param string|callable $action
     */
    public static function exec(array $methods, string $route, $action) {
        // Check if a route is already validated
        if(isset($GLOBALS['ROUTED']) && $GLOBALS['ROUTED']) return;

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
        // if($parts_length !== $client_uri_length) return false;

        // Create the array to store route variables
        $vars = [];

        // Loop over the route string parts
        for($i = 0; $i < $parts_length; $i++) {
            // If the first character of the part is '{', it must be a route variable
            if(isset($parts[$i][0]) && $parts[$i][0] == '{') {
                // If the pre-last character of the part is '*', it must be an infinite possibility route variable
                if($parts[$i][strlen($parts[$i]) - 2] === '*') {
                    // Get the route variable name
                    $varname = preg_replace("/\{(.*?)\*\}/", "$1", $parts[$i]);
                    
                    $vars[$varname] = "";
                    // Loop over the keeping client uri length
                    for($j = $i; $j < $client_uri_length; $j++) {
                        // Add the client URI parts to the array
                        $vars[$varname] .= '/' . $client_uri[$j];
                    }

                    // Break to avoid continuing the loop
                    break;
                } else {
                    $vars[preg_replace("/\{(.*?)\}/", "$1", $parts[$i])] = $client_uri[$i];
                }
            // Check if the route part match the client uri part, if not stop the routing process for this route, else do nothing and continue
            } else if($parts[$i] !== $client_uri[$i]) {
                return false;
            }
        }
        
        if(is_callable($action)) {
            // Start the action's callable relative to the route
            echo call_user_func_array($action, $vars);
        } else {
            // Start the controller's action relative to the route
            echo call_user_func("\\Alph\\Controllers\\" . $action, $vars);
        }
        
        return $GLOBALS['ROUTED'] = true;
    }
}