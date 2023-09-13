<?php
require_once './config/db-connect.php';
 function uploadImages($type,$item_id){

// Check if files were uploaded successfully
if (isset($_FILES['image']) && is_array($_FILES['image']['name'])) {
  $uploadedFiles = $_FILES['image'];

  // Iterate through each uploaded file in the array
  for ($i = 0; $i < count($uploadedFiles['name']); $i++) {
    $uploadedFile = [
      'name' => $uploadedFiles['name'][$i],
      'type' => $uploadedFiles['type'][$i],
      'tmp_name' => $uploadedFiles['tmp_name'][$i],
      'error' => $uploadedFiles['error'][$i],
      'size' => $uploadedFiles['size'][$i]
    ];

    // Check if the current file was uploaded successfully
    if ($uploadedFile['error'] === UPLOAD_ERR_OK) {
      // Generate a unique file name
      $fileName = uniqid() . '_' . $uploadedFile['name'];

      // Specify the destination folder to store the uploaded file
      $destination = 'images/' . $fileName;

      // Move the uploaded file to the destination folder
      move_uploaded_file($uploadedFile['tmp_name'], $destination);

      // Save the file name to the database
      // Perform your database insertion query here, using appropriate database connection and prepared statements
      // Example:
      $db = createDatabaseConnection();
      $query = $db->prepare('INSERT INTO images (item_id, type, name) VALUES (?, ?, ?)');
      $query->execute([$item_id, $type, $fileName]);
    }
  }

} elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
  // A single image file was uploaded
  $uploadedFile = $_FILES['image'];

  // Generate a unique file name
  $fileName = uniqid() . '_' . $uploadedFile['name'];

  // Specify the destination folder to store the uploaded file
  $destination = 'images/' . $fileName;

  // Move the uploaded file to the destination folder
  move_uploaded_file($uploadedFile['tmp_name'], $destination);

  $db = createDatabaseConnection();
  $query = $db->prepare('INSERT INTO images (item_id, type, name) VALUES (?, ?, ?)');
  $query->execute([$item_id, $type, $fileName]);
} else {
  // Handle errors, such as no file uploaded or other upload errors
  // Redirect or display an error message to the user
  http_response_code(400);
  echo json_encode(["error"=> "Error uploading the image(s)."]);
}
}



?>
