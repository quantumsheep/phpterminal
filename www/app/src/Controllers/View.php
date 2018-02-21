<?php
namespace Alph\Controllers;

class View {
    public $view;

    /**
     * Generate a new view
     * @param string $view
     * @param array|object $model
     */
    public function __construct($view, $model = []) {
        // Generate the Blade view
        $this->view = (new \Philo\Blade\Blade(DIR_VIEWS, DIR_BLADE_CACHE))->view()->make($view)->withModel($model);
    }
    
    public function render() {
        // Render the Blade view
        return $this->view->render();
    }
}