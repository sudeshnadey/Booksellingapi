<?php


require_once 'require/header.php';
include 'require/auth-admin.php';
require_once './config/db-connect.php';
require_once './require/image-upload.php';
require_once './require/url.php';

try {
    $pdo = createDatabaseConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Handle database connection error
    $response = array(
        'error' => 'Database connection failed'
    );
    http_response_code(500);
    echo json_encode($response);
    exit;
}

// Set the appropriate header for JSON response
header('Content-Type: application/json');

// Retrieve all courses or courses based on type
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $type = $_GET['type'] ?? '';
    $id = $_GET['id'] ?? '';
    $lang = $_GET['lang'] ?? 'in';

    if(empty($type) || empty($id)){
        http_response_code(400);
        echo json_encode(['success'=>false,'message'=>'type and id required']);
        exit;
    }


    try {
        if($id){
            $stmt = $pdo->prepare('SELECT * FROM courses WHERE id = ? AND lang=?');
            $stmt->execute([$id,$lang]);
            $course = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($course) {
                $q2 = "SELECT * FROM images WHERE type=? AND item_id=?";
                $st = $pdo->prepare($q2);
                $st->execute(['course', $id]);
                $images = $st->fetchAll(PDO::FETCH_ASSOC);
                $images = array_map(function ($image) {
                    return imageUrl() . $image['name'];
                }, $images);


                $q2 = "SELECT day_no, id, title, description, course_id, link FROM videos WHERE course_id=? ORDER BY day_no";
                $st = $pdo->prepare($q2);
    
                $st->execute([$id]);
                $videos = array();
                while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                    $dayNo = $row['day_no'];
                    unset($row['day_no']);
                    if (!isset($videos[$dayNo])) {
                        $videos[$dayNo] = array();
                    }
                    $videos[$dayNo][] = $row;
                }
                
                $groupedVideos = array();
                foreach ($videos as $dayNo => $videoArray) {
                    $groupedVideos['day' . $dayNo] = isset($groupedVideos['day' . $dayNo]) ? array_merge($groupedVideos['day' . $dayNo], $videoArray) : $videoArray;
                }

                $q3 = "SELECT day_no, id, title, description, course_id, link FROM tests WHERE course_id=? ORDER BY day_no";
                $st = $pdo->prepare($q3);
    
                $st->execute([$id]);
                $tests = array();
                while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                    $dayNo = $row['day_no'];
                    unset($row['day_no']);
                    if (!isset($tests[$dayNo])) {
                        $tests[$dayNo] = array();
                    }
                    $row['link']=imageUrl().$row['link'];

                    $tests[$dayNo][] = $row;
                }
                
                $groupedTests = array();
                foreach ($tests as $dayNo => $videoArray) {
                    $groupedTests['day' . $dayNo] = isset($groupedTests['day' . $dayNo]) ? array_merge($groupedTests['day' . $dayNo], $videoArray) : $videoArray;
                }


                $q4 = "SELECT day_no, id, title, description, course_id, link FROM pdfs WHERE course_id=? ORDER BY day_no";
                $st = $pdo->prepare($q4);
    
                $st->execute([$id]);
                $pdfs = array();
                while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
                    $dayNo = $row['day_no'];
                    unset($row['day_no']);
                    if (!isset($pdfs[$dayNo])) {
                        $pdfs[$dayNo] = array();
                    }
                    $row['link']=imageUrl().$row['link'];
                    $pdfs[$dayNo][] = $row;
                }
                
                $groupedPdfs = array();
                foreach ($pdfs as $dayNo => $videoArray) {
                    $groupedPdfs['day' . $dayNo] = isset($groupedPdfs['day' . $dayNo]) ? array_merge($groupedPdfs['day' . $dayNo], $videoArray) : $videoArray;
                }
                $course['images']=$images;
                $course['videos'] = $groupedVideos;
                $course['tests'] = $groupedTests;
                $course['pdfs'] = $groupedPdfs;

                echo json_encode($course);
            } else {
                $response = array(
                    'error' => 'Course not found'
                );
                http_response_code(404);
                echo json_encode($response);
            }
            return;
        }
         else if ($type) {
            
            $stmt = $pdo->prepare('SELECT * FROM courses WHERE type = ? AND lang=?');
            $stmt->execute([$type,$lang]);
        } else {
            $stmt = $pdo->query('SELECT * FROM courses WHERE lang=?');
            $stmt->execute([$lang]);

        }

        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

       
      
        $courses2 = array_map(function ($course) use($pdo) {
            $q2 = "SELECT * FROM images WHERE type=? AND item_id=?";
            $st = $pdo->prepare($q2);
            $st->execute(['course', $course['id']]);
            $image = $st->fetch(PDO::FETCH_ASSOC);
            $course['image']= $image && $image['name']? imageUrl() . $image['name']:null;
            return $course;
        }, $courses);


        echo json_encode($courses2);
    } catch (PDOException $e) {
        // Handle database query error
        $response = array(
            'error' => 'Failed to retrieve courses'.$e
        );
        http_response_code(500);
        echo json_encode($response);
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize the input data
    $input = $_POST;
    $name = $input['name'] ?? '';
    $description = $input['description'] ?? '';
    $mrp = $input['mrp'] ?? 0;
    $category_id = $input['category_id'] ?? 0;
    $type = $input['type'] ?? '';
    $lang = $input['lang'] ?? '';
    $is_free = isset($input['is_free']) ? (bool)$input['is_free'] : false;
    $discount = $input['discount'] ?? 0;

    // Validate the input data
    if (empty($name) || empty($description) || empty($mrp) || empty($category_id) || empty($type) || empty($lang)) {
        $response = array(
            'error' => 'Missing required fields'
        );
        http_response_code(400);
        echo json_encode($response);
        exit;
    }

    // Insert the new course into the database
    try {
        $stmt = $pdo->prepare('INSERT INTO courses (name, description, mrp, category_id, type, lang, is_free, discount) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$name, $description, $mrp, $category_id, $type, $lang, $is_free, $discount]);

        // Get the newly inserted course ID
        $courseId = $pdo->lastInsertId();

        uploadImages('course',$courseId);

        // Prepare the response
        $response = array(
            'id' => $courseId,
            'name' => $name,
            'description' => $description,
            'mrp' => $mrp,
            'category_id' => $category_id,
            'type' => $type,
            'lang' => $lang,
            'is_free' => $is_free,
            'discount' => $discount
        );

        echo json_encode($response);
    } catch (PDOException $e) {
        // Handle database insert error
        $response = array(
            'error' => 'Failed to create a new course'.$e
        );
        http_response_code(500);
        echo json_encode($response);
    }
}

// ...

// Update an existing course
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['id'])) {
    $courseId = $_GET['id'];

    // Validate and sanitize the input data
    $input = json_decode(file_get_contents('php://input'), true);
    $name = $input['name'] ?? '';
    $description = $input['description'] ?? '';
    $mrp = $input['mrp'] ?? 0;
    $category_id = $input['category_id'] ?? 0;
    $type = $input['type'] ?? '';
    $lang = $input['lang'] ?? '';
    $is_free = isset($input['is_free']) ? (bool)$input['is_free'] : false;
    $discount = $input['discount'] ?? '';

    // Validate the input data
    if (empty($name) || empty($description) || empty($mrp) || empty($category_id) || empty($type) || empty($lang)) {
        $response = array(
            'error' => 'Missing required fields'
        );
        http_response_code(400);
        echo json_encode($response);
        exit;
    }

    try {
        $stmt = $pdo->prepare('UPDATE courses SET name=?, description=?, mrp=?, category_id=?, type=?, lang=?, is_free=?, discount=? WHERE id=?');
        $stmt->execute([$name, $description, $mrp, $category_id, $type, $lang, $is_free, $discount, $courseId]);

        $response = array(
            'message' => 'Course updated successfully'
        );
        echo json_encode($response);
    } catch (PDOException $e) {
        // Handle database update error
        $response = array(
            'error' => 'Failed to update the course'
        );
        http_response_code(500);
        echo json_encode($response);
    }
}

// Delete a course
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $courseId = $_GET['id'];

    try {
        $stmt = $pdo->prepare('DELETE FROM courses WHERE id = ?');
        $stmt->execute([$courseId]);

        $response = array(
            'message' => 'Course deleted successfully'
        );
        echo json_encode($response);
    } catch (PDOException $e) {
        // Handle database delete error
        $response = array(
            'error' => 'Failed to delete the course'
        );
        http_response_code(500);
        echo json_encode($response);
    }
}