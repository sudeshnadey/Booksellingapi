<?php
require_once './config/db-connect.php';
require './require/url.php';

class Category
{
    public $id;
    public $name;
    public $image;
    public $description;
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
            $query = "UPDATE categories SET name = :name,description = :description";
            $params = [
                'name' => $this->name,
                'description' => $this->description,
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
            $query = "INSERT INTO categories (name, image, description) VALUES (:name, :image, :description)";
            $statement = $this->pdo->prepare($query);
            $statement->bindParam(':name', $this->name);
            $statement->bindParam(':image', $this->image);
            $statement->bindParam(':description', $this->description);
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

    public static function getById($bannerId, $pdo)
    {
        $query = "SELECT * FROM categories WHERE id = :id";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':id', $bannerId);
        $statement->execute();
        $bannerData = $statement->fetch(PDO::FETCH_ASSOC);

        return $bannerData ? new Category($bannerData['name'], $bannerData['image'], $bannerData['description'], $pdo) : null;
    }

    public static function getAll($pdo)
    {
        $query = "SELECT * FROM categories";
        $statement = $pdo->query($query);
        $bannerData = $statement->fetchAll(PDO::FETCH_ASSOC);

        $categories = array();
        foreach ($bannerData as $data) {
            $banner = new Category($data['name'], imageUrl().$data['image'], $data['description'], $pdo);
            $banner->id = $data['id'];
            $categories[] = $banner;
        }

        return $categories;
    }
}