<?php
namespace Alph\Managers;

class AccountManager
{
    public static function checkUserRegister(\PDO $db, string $username, string $email, string $password)
    {
        $errors = [];

        if (empty($username) || empty($email) || empty($password)) {
            $errors[] = "Please complete the form.";
            return $errors;
        }

        if (strlen($password) < 8) {
            $errors[] = "The password must contains 8 characters minimum.";
        }

        if (strlen($username) < 3) {
            $errors[] = "The username must contains 3 characters minimum.";
        }

        if (!preg_match("/[a-zA-Z0-9.!#$%&'*+\/=?^_``{|}~-]+@[a-zA-Z0-9^_\-\.%+]+\.[a-zA-Z0-9]{2,8}$/", $email)) {
            $errors[] = "Please provide a valid email adress.";
        }

        if (empty($errors)) {
            $stmp = $db->prepare("SELECT idaccount FROM ACCOUNT WHERE username = :username OR email = :email");

            $stmp->bindParam(':username', $username);
            $stmp->bindParam(':email', $email);

            $stmp->execute();

            if ($stmp->rowCount() > 0) {
                while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                    if ($row["email"] == $email) {
                        $errors[] = "This email adress is already used.";
                    } else if ($row["username"] == $username) {
                        $errors[] = "This username is already used.";
                    }
                }
            }
        }

        return $errors;
    }

    public static function createUser(\PDO $db, string $username, string $email, string $password)
    {
        $stmp = $db->prepare("INSERT INTO ACCOUNT (status, email, username, password, createddate, editeddate) VALUES(0, :email, :username, :password, NOW(),  NOW())");

        $stmp->bindParam(":email", $email);
        $stmp->bindParam(":username", $username);

        $password = \password_hash($password, PASSWORD_BCRYPT);
        $stmp->bindParam(":password", $password);

        var_dump($stmp->errorInfo());
        return $stmp->execute();
    }

    public static function createActivationCode(\PDO $db, string $email)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $characters_length = strlen($characters);
        $rand_str = '';

        for ($i = 0; $i < 100; $i++) {
            $rand_str .= $characters[rand(0, $characters_length - 1)];
        }

        $stmp = $db->prepare("INSERT INTO ACCOUNT_VALIDATION (idaccount, code) VALUES((SELECT idaccount FROM ACCOUNT WHERE email = :email), :code);");

        $stmp->bindParam(":email", $email);
        $stmp->bindParam(":code", $rand_str);

        if ($stmp->execute()) {
            return $rand_str;
        }

        return false;
    }

    public static function validateUser(\PDO $db, int $idaccount)
    {
        $stmp = $db->prepare("UPDATE ACCOUNT SET status=1 WHERE idaccount = :idaccount");

        $stmp->bindParam(":idaccount", $idaccount);

        return $stmp->execute();
    }

    public static function getUserIdFromCode(\PDO $db, string $code)
    {
        $stmp = $db->prepare("SELECT idaccount FROM ACCOUNT_VALIDATION WHERE code = :code;");

        $stmp->bindParam(":code", $code);

        if ($stmp->execute()) {
            if ($stmp->rowCount() == 1) {
                return $stmp->fetch()["idaccount"];
            }
        }
        

        return false;
    }

    public static function removeValidationCode(\PDO $db, string $code)
    {
        $stmp = $db->prepare("DELETE FROM ACCOUNT_VALIDATION WHERE code = :code;");

        $stmp->bindParam(":code", $code);

        var_dump($stmp->errorInfo());
        
        return $stmp->execute();
    }

    public static function checkUserLogin(\PDO $db, string $email, string $password)
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = "Incorrect passord.";
        }

        if (!preg_match("/[a-zA-Z0-9.!#$%&'*+\/=?^_``{|}~-]+@[a-zA-Z0-9^_\-\.%+]+\.[a-zA-Z0-9]{2,8}$/", $email)) {
            $errors[] = "Please provide a valid email adress.";
        }

        return $errors;
    }

    public static function identificateUser(\PDO $db, string $email, string $password)
    {
        $stmp = $db->prepare("SELECT idaccount, email, username, password FROM account WHERE email = :email AND status=1;");

        $stmp->bindParam(":email", $email);

        if ($stmp->execute()) {
            if ($stmp->rowCount() == 1) {
                $user = $stmp->fetch();

                if (\password_verify($password, $user["idaccount"])) {
                    return false;
                }
                
                $_SESSION["account"]["idaccount"] = $user["idaccount"];
                $_SESSION["account"]["email"] = $user["email"];
                $_SESSION["account"]["username"] = $user["username"];

                return true;
            }
        }

        return false;
    }

    public static function logout() {
        unset($_SESSION["account"]);
    }
}
