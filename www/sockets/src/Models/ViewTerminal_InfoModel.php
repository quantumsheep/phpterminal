<?php
namespace Alph\Models;

class ViewTerminal_InfoModel {
    public $terminalmac;
    public $networkmac;
    public $privateipv4;
    public $publicipv4;
    public $sshport;

    /**
     * Map to a new ViewTerminal_InfoModel
     * 
     * @param array $data
     * @return self;
     */
    public static function map(array $data): self {
        $row = new self;

        $row->terminalmac = $data["terminalmac"] ?? null;
        $row->networkmac = $data["networkmac"] ?? null;
        $row->privateipv4 = $data["privateipv4"] ?? null;
        $row->publicipv4 = $data["publicipv4"] ?? null;
        $row->sshport = $data["sshport"] ?? null;

        return $row;
    }
}