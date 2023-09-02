<?php
require_once './config/db-connect.php';

class Admin
{
    private $id;
    private $emailid;
    private $password;
    private $pdoConnection;
    // Constructor
    public function __construct($id, $emailid, $password)
    {
        $this->id = $id;
        $this->emailid = $emailid;
        $this->password = $password;
        $this->pdoConnection = createDatabaseConnection();
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->emailid;
    }

    public function getPassword()
    {
        return $this->password;
    }

    // Static method to retrieve an admin by emailid
    public static function getByusername($emailid)
    {
        try {
            $pdoConnection = createDatabaseConnection(); // Use $this->createDatabaseConnection() instead of createDatabaseConnection()

            $query = "SELECT id, emailid, password FROM admins WHERE emailid = :emailid";
            $statement = $pdoConnection->prepare($query);
            $statement->bindParam(':emailid', $emailid);
            $statement->execute();

            $adminData = $statement->fetch(PDO::FETCH_ASSOC);

            if ($adminData) {
                return new Admin($adminData['id'], $adminData['emailid'], $adminData['password']);
            } else {
                return null; // Admin not found
            }
        } catch (PDOException $e) {
            // Handle the exception, e.g., log the error or show an error message
            // You can also throw the exception if you want to handle it elsewhere
            return null;
        }
    }
}
