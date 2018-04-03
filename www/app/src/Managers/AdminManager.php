<?php
namespace Alph\Managers;

class AdminManager {
    public static function getAccountCreatedByDate(\PDO $db, int $limit = 31) {
        $stmp = $db->prepare("SELECT COUNT(*) as created, DATE_FORMAT(createddate, '%M %e') as date FROM ACCOUNT GROUP BY CONVERT(createddate, DATE) LIMIT :limit;");

        $stmp->bindParam(":limit", $limit, \PDO::PARAM_INT);

        $stmp->execute();

        $data = [];

        while($row = $stmp->fetch(\PDO::FETCH_ASSOC)) {
            $data[$row["date"]] = $row["created"];
        }

        return $data;
    }
}