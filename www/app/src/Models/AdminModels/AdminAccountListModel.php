<?php
namespace Alph\Models\AdminModels;

use Alph\EntityModels\RowAccount;

class AdminAccountListModel {
    /**
     * @var RowAccount[]
     */
    public $accounts;

    /**
     * @var int[]
     */
    public $terminalsCount;
}