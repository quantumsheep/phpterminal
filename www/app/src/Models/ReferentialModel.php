<?php
namespace Alph\Models;

class ReferentialModel {
    public $idreferential;
    public $type;
    public $category;
    public $code;
    public $value;

    /**
     * Map to a new ReferentialModel
     * 
     * @param array $data
     * @return self;
     */
    public static function map(array $data): self {
        $row = new self;

        $row->idreferential = $data["idreferential"] ?? null;
        $row->type = $data["type"] ?? null;
        $row->category = $data["category"] ?? null;
        $row->code = $data["code"] ?? null;
        $row->value = $data["value"] ?? null;

        return $row;
    }
}