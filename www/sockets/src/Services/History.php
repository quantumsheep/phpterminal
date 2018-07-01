<?php
namespace Alph\Services;

class History
{
    public static function push(\PDO $db, int $terminal_user, string $idaccount, string $cmd) {
        $stmp = $db->prepare("INSERT INTO TERMINAL_USER_HISTORY (terminal_user, account, status, command, date) VALUES (:terminal_user, :account, 1, :command, :date)");

        $date = date('Y-m-d H:i:s');

        $stmp->bindParam(":terminal_user", $terminal_user);
        $stmp->bindParam(":account", $idaccount);
        $stmp->bindParam(":command", $cmd);
        $stmp->bindParam(":date", $date);

        return $stmp->execute();
    }
}
