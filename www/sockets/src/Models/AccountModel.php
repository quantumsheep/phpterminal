<?php
namespace Alph\Models;

class AccountModel {
    public $idaccount;
    public $status;
    public $email;
    public $username;
    public $password;
    public $code;
    public $createddate;
    public $editeddate;

    /**
     * Map to a new AccountModel
     * 
     * @param array $data
     * @return self;
     */
    public static function map(array $data): self {
        $row = new self;

        $row->idaccount = $data["idaccount"] ?? null;
        $row->status = $data["status"] ?? null;
        $row->hyperpower = $data["hyperpower"] ?? null;
        $row->email = $data["email"] ?? null;
        $row->username = $data["username"] ?? null;
        $row->password = $data["password"] ?? null;
        $row->code = $data["code"] ?? null;
        $row->createddate = $data["createddate"] ?? null;
        $row->editeddate = $data["editeddate"] ?? null;

        return $row;
    }
}