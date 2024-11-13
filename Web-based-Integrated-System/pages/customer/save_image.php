<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../img/userPic/'; 
        $imageFile = uniqid() . '.png'; // Generate unique file name
        $imagePath = $uploadDir . $imageFile;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            // Return a success response with the file name, not the full path
            echo json_encode(['success' => true, 'file' => $imageFile]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to move the uploaded file.']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'No file uploaded or upload error occurred.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
