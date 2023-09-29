<?php
require_once './config/db-connect.php';

class User
{
    public $id;
    public $emailid;
    public $password;
    public $phone;
    public $name;
    // public $pdoConnection;
    // Constructor
    public function __construct($name, $password,$phone)
    {
        // $this->id = $id;
        $this->name = $name;
        $this->password = $password;
        $this->phone = $phone;
        // $this->pdoConnection = createDatabaseConnection();
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
    public function getPhone()
    {
        return $this->phone;
    }

    public function getPassword()
    {
        return $this->password;
    }

    // Static method to retrieve an admin by emailid
    public static function getByusername($phone)
    {
        try {
            $pdoConnection = createDatabaseConnection(); // Use $this->createDatabaseConnection() instead of createDatabaseConnection()

            $query = "SELECT id,name, email,phone, password FROM users WHERE phone = :phone";
            $statement = $pdoConnection->prepare($query);
            $statement->bindParam(':phone', $phone);
            $statement->execute();

            $adminData = $statement->fetch(PDO::FETCH_ASSOC);

            if ($adminData) {

                $user= new User($adminData['name'], $adminData['password'], $adminData['phone']);
                $user->id=$adminData['id'];
                return $user;
            } else {
                return null; // Admin not found
            }
        } catch (PDOException $e) {
            // Handle the exception, e.g., log the error or show an error message
            // You can also throw the exception if you want to handle it elsewhere
            return null;
        }
    }

    public static function registerUser($name, $password, $phone)
    {
        try {
            $pdoConnection = createDatabaseConnection();

            $query = "INSERT INTO users (name, password, phone) VALUES (:name, :password, :phone)";
            $statement = $pdoConnection->prepare($query);
            // $statement->bindParam(':email', $emailid);
            $statement->bindParam(':password', $password);
            $statement->bindParam(':phone', $phone);
            $statement->bindParam(':name', $name);
            $statement->execute();

            $userId = $pdoConnection->lastInsertId();

            return new User($name, $password, $phone);
        } catch (PDOException $e) {
            return null;
        }
    }

    public static function updateUser($name, $email, $phone,$id, $photo = null)
    {
        try {
            $pdoConnection = createDatabaseConnection();
    
            $query = "UPDATE users SET name=:name, email=:email, phone=:phone";
            $parameters = [':name' => $name, ':email' => $email, ':phone' => $phone];
    
            if ($photo !== null) {
                $query .= ", photo=:photo";
                $parameters[':photo'] = $photo;
            }
    
            $query .= " WHERE id=:id";
            $parameters[':id'] = $id;
    
            $statement = $pdoConnection->prepare($query);
            $statement->execute($parameters);
    
            return true;
        } catch (PDOException $e) {
            return $e;
        }
    }
    public static function getAll($pdo)
    {
        $query = "SELECT * FROM users";
        $statement = $pdo->query($query);
        $fproducts = $statement->fetchAll(PDO::FETCH_ASSOC);

        $products = array();
        foreach ($fproducts as $data) {
            $product = new User($data['name'], $data['password'], $data['phone']);
            $product->id = $data['id'];
            $products[] = $product;
        }

        return $products;
    }
}
