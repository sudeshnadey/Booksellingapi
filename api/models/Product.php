<?php
require_once '../config/db-connect.php';

class Product
{
    public $id;
    public $name;
    public $image;
    public $categoryId;
    public $subCategoryId;
    public $description;
    public $mrp;
    public $discount;
    public $quantity;
    private $pdo;

    public function __construct($name, $image, $description,$categoryId,$subCategoryId,$mrp,$discount,$quantity)
    {
        $this->name = $name;
        $this->image = $image;
        $this->description = $description;
        $this->categoryId = $categoryId;
        $this->mrp = $mrp;
        $this->discount = $discount;
        $this->quantity = $quantity;
        $this->subCategoryId = $subCategoryId;
        $this->pdo = createDatabaseConnection();
    }

    public function save()
    {
        if ($this->id) {
            // Update existing banner
            $query = "UPDATE products SET name = :name, image = :image, description = :description ,categoryId=:categoryId,subCategoryId=:subCategoryId,mrp=:mrp,discount=:discount,quantity=:quantity WHERE id = :id";
            $statement = $this->pdo->prepare($query);
            $statement->bindParam(':id', $this->id);
        } else {
            // Insert new banner
            $query = "INSERT INTO products (name, image, description,categoryId,subCategoryId,mrp,discount,quantity) VALUES (:name, :image, :description,:categoryId,:subCategoryId,:mrp,:discount,:quantity)";
            $statement = $this->pdo->prepare($query);
        }

        $statement->bindParam(':name', $this->name);
        $statement->bindParam(':image', $this->image);
        $statement->bindParam(':description', $this->description);
        $statement->bindParam(':categoryId', $this->categoryId);
        $statement->bindParam(':subCategoryId', $this->subCategoryId);
        $statement->bindParam(':mrp', $this->mrp);
        $statement->bindParam(':quantity', $this->quantity);
        $statement->bindParam(':discount', $this->discount);

        $statement->execute();

        if (!$this->id) {
            $this->id = $this->pdo->lastInsertId();
        }
    }

    public function delete($id)
    {
        $query = "DELETE FROM products WHERE id = :id";
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
        $query = "SELECT * FROM products WHERE id = :id";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':id', $bannerId);
        $statement->execute();
        $product = $statement->fetch(PDO::FETCH_ASSOC);

        return $product ? new Product($product['name'], $product['image'], $product['description'],
        $product['categoryId'],$product['subCategoryId'] ,$product['mrp'] ,$product['quantity'],$product['discount'] ,$pdo) : null;
    }

    public static function getAll($pdo)
    {
        $query = "SELECT * FROM products";
        $statement = $pdo->query($query);
        $fproducts = $statement->fetchAll(PDO::FETCH_ASSOC);

        $products = array();
        foreach ($fproducts as $data) {
            $product = new Product($data['name'], $data['image'], $data['description'], $data['categoryId'],
            $data['subCategoryId'],$data['mrp'] ,$data['quantity'],$data['discount'] ,$pdo);
            $product->id = $data['id'];
            $products[] = $product;
        }

        return $products;
    }
}