<?php
require '../../include/_base.php';
ob_start();
auth('admin', 'superAdmin');
$title = 'Add Product';
$currentPage = basename($_SERVER['PHP_SELF']);
include '../../include/_adminHead.php';

$stm = $_db->query("SELECT id FROM product ORDER BY id DESC LIMIT 1");
$lastId = $stm->fetchColumn();

if ($lastId) {
    // Extract the numeric part from the ID
    $numericPart = (int)substr($lastId, 1);

    // Increment the numeric part by 1
    $newNumericPart = $numericPart + 1;

    // Format the new ID by padding with leading zeros if necessary
    $newId = 'P' . str_pad($newNumericPart, 3, '0', STR_PAD_LEFT); 
}

$today = date('Y-m-d'); // Get today's date

$dor = isset($dor) ? $dor : null; 
$uploadedFiles = [];

if ($dor != null) {

    $year = $parts[0];
    $month = $parts[1];
    $day = $parts[2];
} else {
    $year = null;
    $month = null;
    $day = null;
}




$errorMessagesImage = []; 
$errorMessagesVideo = [];
if (is_post()) {
    $name = strtoupper(req('name'));
    $description = req('description');
    $type = ucfirst(strtolower(trim(req('type'))));
    $category_type = req('category_type');
    $price = req('price');

    // Check if product type is "Game" and set default stock_quantity to 1
    if ($type === 'Game') {
        $stock_quantity = 1;
    } else {
        $stock_quantity = req('stock_quantity');
    }

    $selected_day = req('day');
    $selected_month = req('month');
    $selected_year = req('year');
    $photos = $_FILES['product_images'];
    $videos = $_FILES['product_videos']; 
    $uploadedFiles = [];
    $uploadedVideos = []; // Array to hold uploaded video info
    $errorMessagesImage = []; 
    $errorMessagesVideo = [];


    if ($photos['size'][0] != 0) {
        for ($i = 0; $i < count($photos['name']); $i++) {
            $fileName = $photos['name'][$i];
            $fileTmpName = $photos['tmp_name'][$i];
            $fileType = $photos['type'][$i];
            $fileError = $photos['error'][$i];
            $fileSize = $photos['size'][$i];


            if ($fileSize > 1 * 1024 * 1024) { // Check if file size exceeds 1MB    
                $_err['product_images'][$i] = 'File ' . $fileName . ' exceeds the maximum size of 1MB';
                $errorMessagesImage[] = $_err['product_images'][$i]; // Store error in the array

            } else {

                // If validation passes, add to uploadedFiles array
                $uploadedFiles[] = [
                    'name' => $fileName,
                    'tmp_name' => $fileTmpName,
                    'type' => $fileType,
                    'size' => $fileSize
                ];
            }
        }
    }


    // Handle video uploads
    if ($videos['size'][0] != 0) {
        for ($i = 0; $i < count($videos['name']); $i++) {
            $videoName = $videos['name'][$i];
            $videoTmpName = $videos['tmp_name'][$i];
            $videoType = $videos['type'][$i];
            $videoError = $videos['error'][$i];
            $videoSize = $videos['size'][$i];

            if ($videoSize > 15 * 1024 * 1024) {     // Check if file size exceeds 1MB  
                $_err['product_videos'][$i] = 'File ' . $videoName . ' exceeds the maximum size of 15MB';
                $errorMessagesVideo[] = $_err['product_videos'][$i]; // Store error in the array
            } else {
                // Add to uploadedVideos array
                $uploadedVideos[] = [
                    'name' => $videoName,
                    'tmp_name' => $videoTmpName,
                    'type' => $videoType,
                    'size' => $videoSize
                ];
            }
        }
    }




    if ($name == '') {
        $_err['name'] = 'Product Name is required';
    } else if (strlen($name) > 35) { 
        $_err['name'] = 'Product Name cannot exceed 35 characters';
    } else if (duplicated_data($name, 'name', 'product')) {
        $_err['name'] = 'Duplicated Username';
    }


    // Validate: description
    if ($description == '') {
        $_err['description'] = 'Description  is Required';
    }

    if ($type == '') {
        $_err['type'] = 'Type is required (Game or Accessories)';
    } else if ($type != 'Game' && $type != 'Accessories') {
        $_err['type'] = 'Must Type Enter Game or Accessories';
    }

    if ($category_type == '') {
        $_err['category_type'] = 'Category Type  is Required';
    }

    if ($price == '') {
        $_err['price'] = 'Price is required';
    } else if ($price < 0) {
        $_err['price'] = 'Price is not allowed to be a negative number';
    } else if (!is_numeric($price)) {
        $_err['price'] = 'Price is not allowed to contain letters or symbols';
    }

    if ($stock_quantity == '') {
        $_err['stock_quantity'] = 'Stock Quantity is required';
    } else if ($stock_quantity < 0) {
        $_err['stock_quantity'] = 'Stock Quantity is not allowed to be a negative number';
    } else if (!is_numeric($stock_quantity)) {
        $_err['stock_quantity'] = 'Stock Quantity is not allowed to contain letters or symbols';
    }

    if (empty($selected_day) || empty($selected_month) || empty($selected_year)) {
        $_err['dor'] = 'Release Date is required';
    } else {
        $dor = "$selected_year-$selected_month-$selected_day";
        // Check if the date is valid
        if (!checkdate((int)$selected_month, (int)$selected_day, (int)$selected_year)) {
            $_err['dor'] = 'Invalid Release Date. Please enter a valid date.';
        } else {
            $dorDate = new DateTime($dor);
            $today = new DateTime();
            // Ensure the date is not in the future
            if ($dorDate > $today) {
                $_err['dor'] = 'Release Date cannot be in the future';
            }
        }
    }

    
    $imagesUploaded = false; // Flag for image upload status
    $videosUploaded = false; // Flag for video upload status

    if (!$_err && empty($errorMessagesVideo) && empty($errorMessagesImage)) {

        // Insert the product data into the product table
        $stm = $_db->prepare('INSERT INTO `product` (`id`, `name`, `description`, `type`, `category_type`, `price`, `stock_quantity`, `release_date`, `is_active`, `last_date_operate`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP);');
        $stm->execute([$newId, $name, $description, $type, $category_type, $price, $stock_quantity, $dor, 1]);


        // Check if there are uploaded image files
        if ($uploadedFiles[0]['size'] != 0) {
            $fileNames = [];
            foreach ($uploadedFiles as $file) {
                if (!empty($file['tmp_name']) && file_exists($file['tmp_name'])) {
                    $newFileName = uniqid() . '.jpg';

                    // Determine the correct path based on the type
                    if ($type === 'Game') {
                        $filePath = "../../img/game/$newFileName"; 
                    } else if ($type === 'Accessories') {
                        $filePath = "../../img/accessories/$newFileName";
                    } else {
                        $_err['type'] = 'Invalid product type'; 
                        continue; // Skip this file if the type is invalid
                    }


                    require_once '../../lib/SimpleImage.php';

                    // Initialize the SimpleImage class
                    $img = new SimpleImage();
                    $img->fromFile($file['tmp_name'])  // Access the 'tmp_name' key from the $file array
                        ->thumbnail(200, 200)
                        ->toFile($filePath, 'image/jpeg');  // Save the thumbnail image to the determined path

                    // Store the file name
                    $fileNames[] = $newFileName;
                }
            }

            // Combine the file names into a single string separated by commas
            $combinedFileNames = implode(', ', $fileNames);

            // Insert the combined file names into the product_image_video table
            $stmImg = $_db->prepare('INSERT INTO `product_image_video` (`product_id`, `url`, `type`, `last_date_operate`) VALUES (?, ?, ?, CURRENT_TIMESTAMP);');
            $stmImg->execute([$newId, $combinedFileNames, 'photo']); // Insert the combined string of file names

            $imagesUploaded = true; // Set flag to true if images were uploaded
        }

        // Check for uploaded videos
        if ($uploadedVideos[0]['size'] != 0) {
            $fileNamesVideo = [];
            foreach ($uploadedVideos as $video) {
                if (!empty($video['tmp_name']) && file_exists($video['tmp_name'])) {

                    $newVideoName = uniqid() . '.' . pathinfo($video['name'], PATHINFO_EXTENSION); 

                    // Determine the correct path based on the type
                    if ($type === 'Game') {
                        $videoPath = "../../img/game/$newVideoName";  // Save to the game folder
                    } else if ($type === 'Accessories') {
                        $videoPath = "../../img/accessories/$newVideoName";  // Save to the accessories folder
                    } else {
                        $_err['type'] = 'Invalid product type'; // Just in case another type is provided
                        continue; // Skip this file if the type is invalid
                    }
                    move_uploaded_file($video['tmp_name'], $videoPath);

                    // Store the file name
                    $fileNamesVideo[] = $newVideoName;
                }
            }
            // Combine the file names into a single string separated by commas
            $combinedFileNamesVideo = implode(', ', $fileNamesVideo);
            // Insert video path into database
            $stmVideo = $_db->prepare('INSERT INTO `product_image_video` (`product_id`, `url`, `type`, `last_date_operate`) VALUES (?, ?, ?, CURRENT_TIMESTAMP);');
            $stmVideo->execute([$newId, $combinedFileNamesVideo, 'video']); 
            $videosUploaded = true; // Set flag to true if videos were uploaded


        }

        // Set success message based on upload status
        if ($imagesUploaded && $videosUploaded) {
            temp('info', 'Successfully Added Product with Images and Videos');
        } else if ($imagesUploaded) {
            temp('info', 'Successfully Added Product with Image');
        } else if ($videosUploaded) {
            temp('info', 'Successfully Added Product with Video');
        } else {
            temp('info', 'Successfully Added Product without Images and Videos');
        }

    }
                // Redirect after insertion
                redirect('/pages/admin/addProduct.php');
}
?>
<div class="w-full product-section">
    <div class="profile-section">
        <div class="section-header">
            <h2 style="margin-left:275px;">Add Product</h2>
        </div>

        <form method="post" enctype="multipart/form-data" id="product-form">
            <div class="form-container flex">
                <div style="float: left;">
                    <div style="width:600px;float:left;height:300px">
                        <div class="form-group" style="height: 20px;width:200px">
                            <!-- Label with 'for' attribute linked to the input with the same id -->
                            <label>Upload Images</label>
                            <input type="file" name="product_images[]" id="product_images" accept="image/*" class="file-input" multiple onchange="handleImageUpload(event)"><br>

                        </div>
                        <!-- Image Upload Section -->
                        <div class="image-preview" id="image-preview-container">
                            <!-- Placeholder for image previews -->

                            <div class="image-upload-placeholder" onclick="triggerFileInput()">
                                <span style="width:60px">Upload Image</span>
                            </div>
                        </div>
                        <div style="margin-top: 10px;">
                            <?php err1($errorMessagesImage) ?>

                        </div>
                    </div>
                    <!-- Video Upload Section -->
                    <div style="width:600px;float:left;height:300px;">
                        <div class="form-group" style="height: 20px;width:200px">
                            <!-- Label for video upload -->
                            <label>Upload Videos</label>
                            <input type="file" name="product_videos[]" id="product_videos" accept="video/*" class="file-input" multiple onchange="handleVideoUpload(event)"><br>
                        </div>

                        <div class="video-preview" id="video-preview-container">
                            <!-- Placeholder for video previews -->
                            <div class="video-upload-placeholder" onclick="triggerVideoFileInput()">
                                <span style="width:60px">Upload Video</span>
                            </div>
                        </div>

                        <div style="margin-top: 10px;">
                            <?php err1($errorMessagesVideo) ?>
                        </div>
                    </div>

                </div>

                <!-- Product Details Section -->
                <div class="product-details" style="width:400px">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <?= html_text('name', ''); ?><br>
                        <?= err('name') ?>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <?= html_text('description', ''); ?><br>
                        <?= err('description') ?>
                    </div>

                    <div class="form-group">
                        <label for="type">Product Type</label>
                        <input type="text" name="type" id="product_type" oninput="toggleStockQuantity()"><br> <!-- Added id and oninput -->
                        <?= err('type') ?>
                    </div>

                    <div class="form-group">
                        <label for="category_type">Category Type</label>
                        <?= html_text('category_type', ''); ?><br>
                        <?= err('category_type') ?>
                    </div>

                    <div class="form-group">
                        <label for="price">Price</label>
                        <?= html_text('price', ''); ?><br>
                        <?= err('price') ?>
                    </div>

                    <div class="form-group" id="stock_quantity_div">
                        <label for="stock_quantity">Stock Quantity</label>
                        <input type="text" name="stock_quantity" id="stock_quantity_input" value=""><br> 
                        <?= err('stock_quantity') ?>
                    </div>

                    <div class="form-group">
                        <label>Release Date</label>
                    </div>
                    <div class="date-group">
                        <?= html_select1('day', $days, '- Select Day -', 'class="date-select" id="day"', $day); ?>
                        <?= html_select1('month', $months, '- Select Month -', 'class="date-select" id="month"', $month); ?>
                        <?= html_select1('year', $years, '- Select Year -', 'class="date-select" id="year"', $year); ?>

                    </div>
                    <?= err('dor') ?>

                    <div class="action-buttons66">
                        <input type="submit" value="Add" class="save-button">
                        <button type="reset" class="discard-button">Discard</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    // Image upload handling
    let imagesToUpload = []; // Array to hold all images to be uploaded
    let imageCount = 0; // Total image count
    const maxImages = 6;


    function triggerFileInput() {
        if (imageCount < maxImages) {
            document.getElementById('product_images').click();
        } else {
            alert('You can only upload a maximum of 6 images.');
        }
    }

    function handleImageUpload(event) {
        const files = event.target.files;
        const previewContainer = document.getElementById('image-preview-container');

        Array.from(files).forEach((file) => {
            if (imageCount >= maxImages) return; // Limit to max images

            // Add file to the array for upload
            imagesToUpload.push(file);

            const imagePreview = document.createElement('div');
            imagePreview.classList.add('image-preview-item');

            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = 'Image Preview';
            img.classList.add('image-thumb');

            const removeButton = document.createElement('button');
            removeButton.innerText = 'Remove';
            removeButton.classList.add('remove-image-button');
            removeButton.onclick = function() {
                previewContainer.removeChild(imagePreview);
                imagesToUpload.splice(imagesToUpload.indexOf(file), 1); // Remove file from the array
                imageCount--; // Decrease image count
            };

            imagePreview.appendChild(img);
            imagePreview.appendChild(removeButton);
            previewContainer.insertBefore(imagePreview, previewContainer.lastChild);

            imageCount++;
        });

        // Update the hidden input to send all selected files to the server
        document.getElementById('product_images').files = createFileList(imagesToUpload);
    }

    // Helper function to create a FileList from an array of File objects
    function createFileList(files) {
        const dataTransfer = new DataTransfer();
        files.forEach(file => dataTransfer.items.add(file));
        return dataTransfer.files; // Return the FileList object
    }
    // Video upload handling
    let videosToUpload = []; // Array to hold all videos to be uploaded
    let videoCount = 0;
    const maxVideos = 3;

    function triggerVideoFileInput() {
        if (videoCount < maxVideos) {
            document.getElementById('product_videos').click();
        } else {
            alert('You can only upload a maximum of 3 videos.');
        }
    }

    function handleVideoUpload(event) {
        const files = event.target.files;
        const previewContainer = document.getElementById('video-preview-container');

        Array.from(files).forEach((file) => {
            if (videoCount >= maxVideos) return; // Limit to max videos

            // Add file to the array for upload
            videosToUpload.push(file);

            const videoPreview = document.createElement('div');
            videoPreview.classList.add('video-preview-item'); // Unique class for video previews

            const video = document.createElement('video');
            video.src = URL.createObjectURL(file);
            video.alt = 'Video Preview';
            video.classList.add('video-thumb'); // Separate class for video styling
            video.controls = true;

            const removeButton = document.createElement('button');
            removeButton.innerText = 'Remove';
            removeButton.classList.add('remove-video-button'); // Unique class for video removal
            removeButton.onclick = function() {
                previewContainer.removeChild(videoPreview);
                videosToUpload.splice(videosToUpload.indexOf(file), 1); // Remove file from the array
                videoCount--; // Decrease video count
                // Update the hidden input to send the remaining selected files to the server
                document.getElementById('product_videos').files = createVideoList(videosToUpload);
            };

            videoPreview.appendChild(video);
            videoPreview.appendChild(removeButton);
            previewContainer.insertBefore(videoPreview, previewContainer.lastChild);

            videoCount++;
        });

        // Update the hidden input to send all selected files to the server
        document.getElementById('product_videos').files = createVideoList(videosToUpload);
    }

    // Helper function to create a FileList from an array of File objects
    function createVideoList(files) {
        const dataTransfer = new DataTransfer();
        files.forEach(file => dataTransfer.items.add(file));
        return dataTransfer.files; // Return the FileList object
    }

    // Function to handle the visibility of stock quantity field
    function toggleStockQuantity() {
        const productTypeInput = document.getElementById('product_type'); // Assuming the ID is 'product_type'
        const stockQuantityDiv = document.getElementById('stock_quantity_div');
        const stockQuantityInput = document.getElementById('stock_quantity_input');

        if (productTypeInput.value.toLowerCase() === 'game') {
            // Hide the stock quantity field
            stockQuantityDiv.style.display = 'none';
            // Set stock quantity to 1
            stockQuantityInput.value = 1;
        } else {
            // Show the stock quantity field
            stockQuantityDiv.style.display = 'block';
        }
    }

    // Attach the event listener to the Product Type field
    document.getElementById('product_type').addEventListener('input', toggleStockQuantity);
</script>

<?php
include '../../include/_adminFooter.php';
