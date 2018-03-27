<?php
namespace Alph\Models\AdminModels;

use Alph\EntityModels\RowTerminal;
use Alph\EntityModels\RowAccount;

class AdminTerminalListModel {
    /**
     * @var RowTerminal[]
     */
    public $terminals;

    /**
     * @var RowAccount[]
     */
    public $accounts;
}