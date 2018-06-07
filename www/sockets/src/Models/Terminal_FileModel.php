<?php
namespace Alph\Models;

class Terminal_UserModel {
    public $idfile;
    public $terminal;
    public $parent;
    public $name;
    public $data;
    public $chmod;
    public $owner;
    public $group;
    public $createddate;
    public $editedddate;
    
    /**
     * Map to a new TerminalModel
     * 
     * @param array $data
     * @return self;
     */
    public static function map(array $data) {
        $row = new self;

        $row->idfile = $data["idfile"] ?? null;
        $row->terminal = $data["terminal"] ?? null;
        $row->parent = $data["parent"] ?? null;
        $row->name = $data["name"] ?? null;
        $row->data = $data["data"] ?? null;
        $row->chmod = $data["chmod"] ?? null;
        $row->owner = $data["owner"] ?? null;
        $row->group = $data["group"] ?? null;
        $row->createddate = $data["createddate"] ?? null;
        $row->editedddate = $data["editedddate"] ?? null;

        return $row;
    }
}