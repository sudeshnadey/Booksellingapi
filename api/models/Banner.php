// Banner.php
<?php
require_once './config/db-connect.php';

class Banner
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
            $query = "UPDATE banners SET name = :name, image = :image, description = :description WHERE id = :id";
            $statement = $this->pdo->prepare($query);
            $statement->bindParam(':id', $this->id);
        } else {
            // Insert new banner
            $query = "INSERT INTO banners (name, image, description) VALUES (:name, :image, :description)";
            $statement = $this->pdo->prepare($query);
        }

        $statement->bindParam(':name', $this->name);
        $statement->bindParam(':image', $this->image);
        $statement->bindParam(':description', $this->description);

        $statement->execute();

        if (!$this->id) {
            $this->id = $this->pdo->lastInsertId();
        }
    }

    public function delete()
    {
        $query = "DELETE FROM banners WHERE id = :id";
        $statement = $this->pdo->prepare($query);
        $statement->bindParam(':id', $this->id);
        $statement->execute();
    }

    public static function getById($bannerId, $pdo)
    {
        $query = "SELECT * FROM banners WHERE id = :id";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':id', $bannerId);
        $statement->execute();
        $bannerData = $statement->fetch(PDO::FETCH_ASSOC);

        return $bannerData ? new Banner($bannerData['name'], $bannerData['image'], $bannerData['description'], $pdo) : null;
    }

    public static function getAll($pdo)
    {
        $query = "SELECT * FROM banners";
        $statement = $pdo->query($query);
        $bannerData = $statement->fetchAll(PDO::FETCH_ASSOC);

        $banners = array();
        foreach ($bannerData as $data) {
            $banner = new Banner($data['name'], $data['image'], $data['description'], $pdo);
            $banner->id = $data['id'];
            $banners[] = $banner;
        }

        return $banners;
    }
}