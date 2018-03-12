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
                    if($row["email"] == $email) {
                        $errors[] = "This email adress is already used.";
                    } else if($row["username"] == $username) {
                        $errors[] = "This username is already used.";                        
                    }
                }
            }
        }

        return $errors;
    }

    public static function createUser(\PDO $db, string $username, string $email, string $password) {
        $stmp = $db->prepare("INSERT INTO account (email, username, password, createddate, editeddate) VALUES(:email, :username, :password, NOW(),  NOW());");
    
        $stmp->bindParam(":email", $email);
        $stmp->bindParam(":username", $username);
        $stmp->bindParam(":password", \password_hash($password, PASSWORD_BCRYPT));

        return $stmp->execute();
    }
}
