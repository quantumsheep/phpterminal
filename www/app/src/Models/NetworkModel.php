<?php
namespace Alph\Models;

class NetworkModel {
    public $mac;
    public $ipv4;
    public $ipv6;
    
    /**
     * Map to a new TerminalModel
     * 
     * @param array $data
     * @return self;
     */
    public static function map(array $data): self {
        $row = new self;

        $row->mac = $data["mac"] ?? null;
        $row->ipv4 = $data["ipv4"] ?? null;
        $row->ipv6 = $data["ipv6"] ?? null;

        return $row;
    }
}