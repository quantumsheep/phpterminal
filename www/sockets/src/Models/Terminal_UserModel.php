<?php
namespace Alph\Models;

class Terminal_UserModel {
    public $idterminal_user;
    public $terminal;
    public $uuid;
    public $gid;
    public $status;
    public $username;
    public $password;
    
    /**
     * Map to a new TerminalModel
     * 
     * @param array $data
     * @return self;
     */
    public static function map(array $data): self {
        $row = new self;

        $row->idterminal_user = $data["idterminal_user"] ?? null;
        $row->terminal = $data["terminal"] ?? null;
        $row->uuid = $data["uuid"] ?? null;
        $row->gid = $data["gid"] ?? null;
        $row->status = $data["status"] ?? null;
        $row->username = $data["username"] ?? null;
        $row->password = $data["password"] ?? null;

        return $row;
    }
}