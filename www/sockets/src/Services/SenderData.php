<?php
namespace Alph\Services;

use Alph\Models\Terminal_UserModel;

class SenderData {
    /**
     * @var Terminal_UserModel
     */
    public $user;

    /**
     * @var bool
     */
    public $connected = false;

    /**
     * @var string
     */
    public $position;

    /**
     * @var array
     */
    public $data;

    public $controller = null;

    public $private_input = false;

    public function __construct() {
        $this->user = new Terminal_UserModel;
    }
}
