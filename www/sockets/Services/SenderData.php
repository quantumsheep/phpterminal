<?php
namespace Alph\Services;

use Alph\Services\SenderDataCredentials;

class SenderData {
    /**
     * @var SenderDataCredentials
     */
    public $credentials;
    
    /**
     * @var array
     */
    public $data;

    public function __construct() {
        $this->credentials = new SenderDataCredentials;
    }
}