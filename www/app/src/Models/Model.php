<?php
namespace Alph\Models;

class Model {
    public function __get($property){
        return $this->{$property};
    }
}