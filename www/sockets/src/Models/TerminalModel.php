<?php
namespace Alph\Models;

class TerminalModel {
    public $mac;
    public $account;
    public $localnetwork;

    /**
     * Map to a new TerminalModel
     * 
     * @param array $data
     * @return self;
     */
    public static function map(array $data) {
        $row = new self;

        $row->mac = $data["mac"] ?? null;
        $row->account = $data["account"] ?? null;
        $row->localnetwork = $data["localnetwork"] ?? null;

        return $row;
    }
}