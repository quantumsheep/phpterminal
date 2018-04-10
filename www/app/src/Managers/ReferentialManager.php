<?php
namespace Alph\Managers;

use Alph\Models\ReferentialModel;

class ReferentialManager
{
    public static function getReferential(\PDO $db, int $idreferential) {
        // Prepare the SQL row selection
        $stmp = $db->prepare("SELECT idreferential, type, category, code, value FROM REFERENTIAL WHERE idreferential = :idreferential;");

        $stmp->bindParam(":idreferential", $idreferential, \PDO::PARAM_INT);

        // Execute the query and check if successful
        if ($stmp->execute()) {
            // Check if there is one row selected
            if($row = $stmp->fetch()) {
                // Fetch and map the row
                return ReferentialModel::map($row);
            }
        }

        return new ReferentialModel();
    }

    public static function getReferentialCategoryCode(\PDO $db, int $idcategory = null) {
        // Prepare the SQL row selection
        $stmp = $db->prepare("SELECT code FROM REFERENTIAL WHERE idreferential = :idreferential;");

        $stmp->bindParam(":idreferential", $idcategory, \PDO::PARAM_INT);

        // Execute the query and check if successful
        if ($stmp->execute()) {
            // Check if there is one row selected
            if($row = $stmp->fetch()) {
                // Fetch and map the row
                return $row["code"];
            }
        }

        return null;
    }

    public static function getReferentials(\PDO $db, int $category = null)
    {
        // Prepare the SQL row selection
        $stmp = $db->prepare("SELECT idreferential, type, category, code, value FROM REFERENTIAL WHERE category" . (!empty($category) ? "=" . $category : " IS NULL") . " ORDER BY type DESC;");

        $result = [];

        // Execute the query and check if successful
        if ($stmp->execute()) {
            // Check if there is one row selected
            while($row = $stmp->fetch()) {
                // Fetch and map the row
                $result[] = ReferentialModel::map($row);
            }
        }

        return $result;
    }

    public static function createReferential(\PDO $db, int $type, string $code, int $category = null, string $value = null) {
        $stmp = $db->prepare("INSERT INTO REFERENTIAL (type, category, code, value) VALUES(:type, :category, :code, :value);");

        $stmp->bindParam(":type", $type, \PDO::PARAM_INT);
        $stmp->bindParam(":category", $category);
        $stmp->bindParam(":code", $code);
        $stmp->bindParam(":value", $value);

        return $stmp->execute();
    }

    public static function updateValue(\PDO $db, int $idreferential, string $value) {
        $stmp = $db->prepare("UPDATE REFERENTIAL SET value = :value WHERE idreferential = :idreferential;");

        $stmp->bindParam(":value", $value);
        $stmp->bindParam(":idreferential", $idreferential);

        return $stmp->execute();
    }
}
