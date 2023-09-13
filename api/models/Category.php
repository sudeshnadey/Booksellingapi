<?php
require_once './config/db-connect.php';
require './require/url.php';

class Category
{
    public $id;
    public $name;
    public $image;
    public $description;
    public $type;
    private $pdo;

    public function __construct($name, $image, $description)
    {
        $this->name = $name;
        $this->image = $image;
        $this->description = $description;
        $this->pdo = createDatabaseConnection();
    }

    public function save()
    {
        if ($this->id) {
            // Update existing banner
            $query = "UPDATE categories SET name = :name,description = :description,type=:type";
            $params = [
                'name' => $this->name,
                'description' => $this->description,
                'type' => $this->type,
            ];
    
            if ($this->image !== null) {
                $query .= ", image = :image";
                $params['image'] = $this->image;
            }
    
            $query .= " WHERE id = :id";
            $params['id'] = $this->id;
    
            $statement = $this->pdo->prepare($query);
            $statement->execute($params);
        } else {
            // Insert new banner
            $query = "INSERT INTO categories (name, image, description,type) VALUES (:name, :image, :description,:type)";
            $statement = $this->pdo->prepare($query);
            $statement->bindParam(':name', $this->name);
            $statement->bindParam(':image', $this->image);
            $statement->bindParam(':description', $this->description);
            $statement->bindParam(':type', $this->type);
            $statement->execute();

        }

        if (!$this->id) {
            $this->id = $this->pdo->lastInsertId();
        }
    }

    public function delete($id)
    {
        $query = "DELETE FROM categories WHERE id = :id";
        $statement = $this->pdo->prepare($query);
        $statement->bindParam(':id', $id);
        
        if ($statement->execute()) {
            // Deletion was successful
            return true;
        } else {
            // Deletion failed
            return false;
        }
    }

    public static function getById($id, $pdo)
    {
        $query = "SELECT * FROM categories WHERE id = :id";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':id', $id);
        $statement->execute();
        $categoryData = $statement->fetch(PDO::FETCH_ASSOC);

        return $categoryData ? new Category($categoryData['name'], $categoryData['image'], $categoryData['description']) : null;
    }

    public static function getAll($pdo)
    {
        $query = "SELECT * FROM categories";
        $statement = $pdo->query($query);
        $categories = $statement->fetchAll(PDO::FETCH_ASSOC);

     
        return array_map(function ($data) {
            $data['image'] = !empty($data['image']) ? imageUrl() . $data['image'] : null;
            return $data;
        }, $categories);


    }
}