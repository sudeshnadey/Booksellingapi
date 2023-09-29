<?php
require_once 'require/header.php';
include 'require/auth-user.php';
require './require/url.php';

require_once './config/db-connect.php';



function getCoursesById($pdo)
{
    $id = $_GET['id'] ?? '';

    if (empty($id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => ' id required']);
        exit;
    }
    $lang = $_GET['lang'] ?? 'in';
    if ($id) {

        $stmt = $pdo->prepare('SELECT * FROM courses WHERE id = ? AND lang=?');
        $stmt->execute([$id, $lang]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($course) {
            $q2 = "SELECT * FROM images WHERE type=? AND item_id=?";
            $st = $pdo->prepare($q2);
            $st->execute(['course', $id]);
            $images = $st->fetchAll(PDO::FETCH_ASSOC);

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
            $images = array_map(function ($image) {
                return imageUrl() . $image['name'];
            }, $images);

            $course['images'] = $images;
            $course['videos'] = $groupedVideos;
            $course['tests'] = $groupedTests;
            $course['pdfs'] = $groupedPdfs;
            return ($course);
        } else {
            return [];
        }
    } else {
        return [];
    }
}

try {
    $pdo = createDatabaseConnection();
    $news = getCoursesById($pdo);
    $jsonData = json_encode($news);

    http_response_code(200);
    header('Content-Type: application/json');

    // Output the JSON data
    echo $jsonData;
} catch (PDOException $e) {
    // Example: Logging the error
    error_log('Error fetching categories: ' . $e->getMessage());

    // Return an error response
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'An error occurred while fetching categories.' . $e->getMessage()]);
}
