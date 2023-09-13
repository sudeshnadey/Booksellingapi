<?php
require_once './config/db-connect.php';
require './require/url.php';

class Book
{
    public $id;
    public $name;
    public $image;
    public $categoryId;
    public $subCategoryId;
    public $lang;
    public $description;
    public $mrp;
    public $discount;
    public $quantity;
    public $barcode;
    public $sold = 0;
    public $sample;
    public $delivery_price;
    private $pdo;

    public function __construct($name, $description, $categoryId,  $lang, $mrp, $discount, $quantity)
    {
        $this->name = $name;
        $this->description = $description;
        $this->categoryId = $categoryId;
        $this->mrp = $mrp;
        $this->discount = $discount;
        $this->quantity = $quantity;
        $this->lang = $lang;
        $this->pdo = createDatabaseConnection();
    }

    public function save()
    {
        if ($this->id) {
            // Update existing product
            $query = "UPDATE books SET name = :name, description = :description,
             categoryId = :categoryId,lang = :lang, mrp = :mrp,
              quantity = :quantity, discount = :discount,
                 delivery_price= :delivery_price";
        
            if ($this->sample !== null) {
                $query .= ", sample = :sample";
            }
            if ($this->barcode !== null) {
                $query .= ", barcode = :barcode";
            }
            $query .= " WHERE id = :id";
            $statement = $this->pdo->prepare($query);
            $statement->bindParam(':id', $this->id);

         
            if ($this->sample !== null) {
                $statement->bindParam(':sample', $this->sample);
            }
            if ($this->barcode !== null) {
                $statement->bindParam(':barcode', $this->barcode);
            }
            $statement->bindParam(':name', $this->name);
            $statement->bindParam(':description', $this->description);
            $statement->bindParam(':categoryId', $this->categoryId);
            $statement->bindParam(':lang', $this->lang);
            $statement->bindParam(':mrp', $this->mrp);
            $statement->bindParam(':quantity', $this->quantity);
            $statement->bindParam(':discount', $this->discount);

            $statement->bindParam(':delivery_price', $this->delivery_price);

            $statement->execute();
        } else {
            // Insert new product
            $query = "INSERT INTO books (name,
              description,
               categoryId, 
             mrp,
             quantity,
              discount,  
                lang,
                  barcode,
                  sample,
                  delivery_price
                  ) VALUES (
                :name,
                 :description, 
                 :categoryId, 
                  :mrp,
                   :quantity,
                  :discount,
                  :lang,
                  :barcode,
                  :sample,
                  :delivery_price
                  )";
            $statement = $this->pdo->prepare($query);

            $statement->bindParam(':name', $this->name);
            $statement->bindParam(':description', $this->description);
            $statement->bindParam(':categoryId', $this->categoryId);
            $statement->bindParam(':lang', $this->lang);
            $statement->bindParam(':mrp', $this->mrp);
            $statement->bindParam(':quantity', $this->quantity);
            $statement->bindParam(':discount', $this->discount);
            $statement->bindParam(':barcode', $this->barcode);
            $statement->bindParam(':sample', $this->sample);
            $statement->bindParam(':delivery_price', $this->delivery_price);
            // $statement->bindParam(':sold', $this->sold);

            $statement->execute();
        }



        if (!$this->id) {
            $this->id = $this->pdo->lastInsertId();
        }
    }
    public function delete($id)
    {
        $query = "DELETE FROM books WHERE id = :id";
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
        $query = "SELECT * FROM books WHERE id = :id";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':id', $bannerId);
        $statement->execute();
        $product = $statement->fetch(PDO::FETCH_ASSOC);

        return $product ? new Book(
            $product['name'],
            $product['description'],
            $product['categoryId'],
            $product['lang'],
            $product['mrp'],
            $product['quantity'],
            $product['discount'],
        ) : null;
    }

    public static function getAll($pdo)
    {
        $query = "SELECT * FROM books";
        $statement = $pdo->query($query);
        $books = $statement->fetchAll(PDO::FETCH_ASSOC);
    
        $fbooks = array_map(function ($data) use ($pdo) {
            $q2 = "SELECT * FROM images WHERE type=? AND item_id=?";
            $st = $pdo->prepare($q2);
            $st->execute(['book', $data["id"]]);
            $images = $st->fetchAll(PDO::FETCH_ASSOC);
    
            $data['images'] = array_map(function ($image) {
                return imageUrl() . $image['name'];
            }, $images);
    
            $data['image'] = !empty($data['images']) ? $data['images'][0] : null;
            $data['sample'] = !empty($data['sample']) ? imageUrl() . $data['sample'] : null;
            $data['barcode'] = !empty($data['barcode']) ? imageUrl() . $data['barcode'] : null;
            return $data;
        }, $books);
    
        return $fbooks;
    }
}
