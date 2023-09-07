<?php
require_once './config/db-connect.php';
require './require/url.php';

class SubSubCategory
{
    public $id;
    public $name;
    public $image;
    public $subCategoryId;
    public $description;
    private $pdo;

    public function __construct($name, $image, $description,$subCategoryId)
    {
        $this->name = $name;
        $this->image = $image;
        $this->description = $description;
        $this->subCategoryId = $subCategoryId;
        $this->pdo = createDatabaseConnection();
    }

    public function save()
    {
        if ($this->id) {
            // Update existing banner
            $query = "UPDATE sub_sub_categories SET name = :name, description = :description ,subCategoryId=:subCategoryId";
            $statement = $this->pdo->prepare($query);

            if ($this->image !== null) {
                $query .= ", image = :image";
            }
            $query .= " WHERE id = :id";
            $statement = $this->pdo->prepare($query);
            $statement->bindParam(':id', $this->id);
            if ($this->image !== null) {
                $statement->bindParam(':image', $this->image);
            }

        } else {
            // Insert new banner
            $query = "INSERT INTO sub_sub_categories (name, image, description,subCategoryId) VALUES (:name, :image, :description,:subCategoryId)";
            $statement = $this->pdo->prepare($query);
            $statement->bindParam(':image', $this->image);

        }

        $statement->bindParam(':name', $this->name);
        $statement->bindParam(':description', $this->description);
        $statement->bindParam(':subCategoryId', $this->subCategoryId);

        $statement->execute();

        if (!$this->id) {
            $this->id = $this->pdo->lastInsertId();
        }
    }

    public function delete($id)
    {
        $query = "DELETE FROM sub_sub_categories WHERE id = :id";
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
        $query = "SELECT * FROM sub_sub_categories WHERE id = :id";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':id', $bannerId);
        $statement->execute();
        $subCate = $statement->fetch(PDO::FETCH_ASSOC);

        return $subCate ? new SubSubCategory($subCate['name'], $subCate['image'], $subCate['description'],$subCate['subCategoryId'], $pdo) : null;
    }

    public static function getAll($pdo)
    {
        $query = "SELECT * FROM sub_sub_categories";
        $statement = $pdo->query($query);
        $subCate = $statement->fetchAll(PDO::FETCH_ASSOC);

        $sub_categories = array();
        foreach ($subCate as $data) {
            $sub_category = new SubSubCategory($data['name'], imageUrl().$data['image'], $data['description'], $data['subCategoryId'], $pdo);
            $sub_category->id = $data['id'];
            $sub_categories[] = $sub_category;
        }

        return $sub_categories;
    }
}