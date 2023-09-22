<?php
require_once './config/db-connect.php';
require './require/url.php';

class EBook
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
    public $price;
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
    public static function getDetail($pdo)
    {
        $id = $_GET['id'] ?? '';
        if ($id) {
            $query = "SELECT * FROM books WHERE id = :id LIMIT 1";
            $statement = $pdo->prepare($query);
            $statement->bindParam(':id', $id);
            $statement->execute();
            $product = $statement->fetch(PDO::FETCH_ASSOC);


            $q2 = "SELECT * FROM images WHERE type=? AND item_id=?";
            $st = $pdo->prepare($q2);
            $st->execute(['book', $product["id"]]);
            $images = $st->fetchAll(PDO::FETCH_ASSOC);

            $images = array_map(function ($image) {
                return imageUrl() . $image['name'];
            }, $images);


            $product['images'] = !empty($images) ? $images : [];
            $product['sample'] = !empty($product['sample']) ? imageUrl() . $product['sample'] : null;
            $product['barcode'] = !empty($product['barcode']) ? imageUrl() . $product['barcode'] : null;
            $product['related'] = Book::getRelatedBook($pdo, $product['categoryId'], $product['lang']);
            $product['reviews'] = Book::getReviews($pdo, $product['id'], 'book');
            $product['rate'] = Book::calculateRate($pdo, $product['id'], 'book');
            return $product;
        } else {
            return null;
        }
    }

    public static function addRecentlyViewed($pdo, $bookId, $type)
    {
        $userId = 1; // Replace with the actual user ID
        $productId = 1; // Replace with the actual product ID

        // Insert the view into the viewing history
        $query = "INSERT INTO viewing_history (user_id, item_id,type timestamp)
          VALUES (:userId, :itemId,:type, NOW())";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':userId', $userId);
        $statement->bindParam(':itemId', $bookId);
        $statement->execute();
    }
    public static function fetchRecentlyViewd($pdo, $userId, $type)
    {
        $userId = 1; // Replace with the actual user ID
        $limit = 5; // Number of recently viewed products to fetch

        // Fetch recently viewed products with product names
        $query = "SELECT vh.item_id, p.name
                  FROM viewing_history vh
                  JOIN products p ON vh.item_id = p.id
                  WHERE vh.user_id = :userId
                  AND  vh.type = :type
                  ORDER BY vh.timestamp DESC
                  LIMIT :limit";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':userId', $userId);
        $statement->bindParam(':type', $type);
        $statement->bindParam(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return  $recentlyViewed = $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function calculateRate($pdo, $bookId, $type)
    {
        // Calculate average rating
        $query = "SELECT AVG(rating) AS average_rating
                  FROM reviews
                  WHERE item_id = :productId
                  AND type=:type";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':productId', $bookId);
        $statement->bindParam(':type', $type);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $averageRating =doubleval($result['average_rating']) ?? 0.0;
    }
    public static function getReviews($pdo, $bookId, $type)
    {

        $query = "SELECT r.*,u.name as user_name FROM reviews as r INNER JOIN users as u on u.id = r.user_id WHERE r.item_id = :bookId AND type = :book ";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':bookId', $bookId);
        $statement->bindParam(':book', $type);
        $statement->execute();
        return $reviews = $statement->fetchAll(PDO::FETCH_ASSOC);
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
            $data['rate'] = Book::calculateRate($pdo, $data['id'], 'book');

            return $data;
        }, $books);

        return $fbooks;
    }

    public static function getRelatedBook($pdo, $cate_id, $lang)
    {

        if ($cate_id) {


            $query = "SELECT id,name,mrp,discount,rate FROM books WHERE categoryId = :categoryId AND lang=:lang ORDER BY created_at DESC ";

            $statement = $pdo->prepare($query);
            $statement->bindParam(':categoryId', $cate_id);
            $statement->bindParam(':lang', $lang);
            $statement->execute();
            $books = $statement->fetchAll(PDO::FETCH_ASSOC);

            $fbooks = array_map(function ($data) use ($pdo) {
                $q2 = "SELECT * FROM images WHERE type=? AND item_id=?";
                $st = $pdo->prepare($q2);
                $st->execute(['book', $data["id"]]);
                $images = $st->fetchAll(PDO::FETCH_ASSOC);

                $dat = [];
                $dat['images'] = array_map(function ($image) {
                    return imageUrl() . $image['name'];
                }, $images);

                $data['image'] = !empty($dat['images']) ? $dat['images'][0] : null;
                $data['rate'] = Book::calculateRate($pdo, $data['id'], 'book');

                return $data;
            }, $books);

            return $fbooks;
        } else {
            return [];
        }
    }
    public static function getAllBooksByCategory($pdo)
    {
        $cate_id = $_GET['category'];
        $lang = $_GET['lang'] ?? 'in';
        if ($cate_id) {


            $query = "SELECT id,name,mrp,discount,rate,quantity FROM books WHERE categoryId = :categoryId AND lang=:lang AND quantity > 0 ORDER BY created_at DESC ";

            $statement = $pdo->prepare($query);
            $statement->bindParam(':categoryId', $cate_id);
            $statement->bindParam(':lang', $lang);
            $statement->execute();
            $books = $statement->fetchAll(PDO::FETCH_ASSOC);

            $fbooks = array_map(function ($data) use ($pdo) {
                $q2 = "SELECT * FROM images WHERE type=? AND item_id=?";
                $st = $pdo->prepare($q2);
                $st->execute(['book', $data["id"]]);
                $images = $st->fetchAll(PDO::FETCH_ASSOC);

                $dat = [];
                $dat['images'] = array_map(function ($image) {
                    return imageUrl() . $image['name'];
                }, $images);

                $data['image'] = !empty($dat['images']) ? $dat['images'][0] : null;
                $data['rate'] = Book::calculateRate($pdo, $data['id'], 'book');

                return $data;
            }, $books);

            return $fbooks;
        } else {
            return [];
        }
    }

    public static function getCategoriesWithBooks($pdo)
    {
        $query = "SELECT c.id as category_id,c.name as category_name,c.image as category_image, b.id as
         book_id,b.name as book_name,b.mrp FROM categories c LEFT JOIN books b 
         ON c.id = b.categoryId WHERE b.lang=:lang ORDER BY b.created_at DESC";

        $lang = $_GET['lang'] ?? 'in';
        $statement = $pdo->prepare($query);
        $statement->bindParam(':lang', $lang);
        $statement->execute();
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        $categories = [];

        foreach ($results as $row) {
            $categoryId = $row['category_id'];

            if (!isset($categories[$categoryId])) {
                $categories[$categoryId] = [
                    'category_id' => $row['category_id'],
                    'category_name' => $row['category_name'],
                    'image' =>  $row['category_image'] ? imageUrl() . $row['category_image'] : null,
                    'books' => []
                ];
            }

            $dd = [];
            $q2 = "SELECT * FROM images WHERE type=? AND item_id=?";
            $st = $pdo->prepare($q2);
            $st->execute(['book', $row["book_id"]]);
            $images = $st->fetchAll(PDO::FETCH_ASSOC);

            $dd['images'] = array_map(function ($image) {
                return imageUrl() . $image['name'];
            }, $images);


            if (isset($row['book_id'])  && count($categories[$categoryId]['books']) < 5) {
                $categories[$categoryId]['books'][] = [
                    'book_id' => $row['book_id'],
                    'book_name' => $row['book_name'],
                    'image' => $dd['images'][0] ?? null,
                    'mrp' => $row['mrp'],
                    'rate' => Book::calculateRate($pdo, $row['book_id'], 'book')

                    // Add any other book properties you want to include
                ];
            }
        }

        return array_values($categories);
    }

    public static function getNewReleases($pdo)
    {

        $query = "SELECT c.id as category_id,c.name as category_name,c.image as category_image, b.id as
         book_id,b.name as book_name,b.mrp FROM categories c  JOIN books b 
         ON c.id = b.categoryId WHERE b.lang=:lang ORDER BY b.created_at DESC";

        $filt = $_GET['filter'] ?? '';

        if (!($filt == 'all')) {
            $query .= ' LIMIT 2';
        }

        $lang = $_GET['lang'] ?? 'in';
        $statement = $pdo->prepare($query);
        $statement->bindParam(':lang', $lang);
        $statement->execute();
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        $fbooks = array_map(function ($data) use ($pdo) {
            $q2 = "SELECT * FROM images WHERE type=? AND item_id=?";
            $st = $pdo->prepare($q2);
            $st->execute(['book', $data["book_id"]]);
            $images = $st->fetchAll(PDO::FETCH_ASSOC);

            $dat = [];


            $dat['images'] = array_map(function ($image) {
                return imageUrl() . $image['name'];
            }, $images);

            $data['image'] = !empty($dat['images']) ? $dat['images'][0] : null;
            $data['rate'] = Book::calculateRate($pdo, $data['book_id'], 'book');

            // $data['sample'] = !empty($data['sample']) ? imageUrl() . $data['sample'] : null;
            // $data['barcode'] = !empty($data['barcode']) ? imageUrl() . $data['barcode'] : null;
            return $data;
        }, $results);
        return $fbooks;
    }
}
