<?php
require '../../include/_base.php';
ob_start();
auth('admin', 'superAdmin');
$title = 'Edit Product';
$currentPage = basename($_SERVER['PHP_SELF']);
include '../../include/_adminHead.php';
$page = req('page', 1);
$id = req('id');

$stm = $_db->prepare('SELECT * FROM product WHERE id = ?');
$stm->execute([$id]);
$product = $stm->fetch();
$sql_images = "SELECT GROUP_CONCAT(url SEPARATOR ',') AS urls FROM product_image_video WHERE product_id = ? AND type = 'photo'";
$images_stmt = $_db->prepare($sql_images);
$images_stmt->execute([$product->id]);
$image_result = $images_stmt->fetch(); // Fetch a single row
$image_urls = [];
// Check if URLs were found
if (!empty($image_result->urls)) {
    $image_urls = explode(', ', $image_result->urls); // Separate URLs into an array

}
$sql_video = "SELECT GROUP_CONCAT(url SEPARATOR ',') AS urls FROM product_image_video WHERE product_id = ? AND type = 'video'";
$Video_stmt = $_db->prepare($sql_video);
$Video_stmt->execute([$product->id]);
$video_result = $Video_stmt->fetch(); // Fetch a single row

$video_urls = [];
// Check if URLs were found
if (!empty($video_result->urls)) {
    $video_urls = explode(', ', $video_result->urls); // Separate URLs into an array

}



// Populate global variables with product data
$GLOBALS['name'] = $product->name;
$GLOBALS['description'] = $product->description;
$GLOBALS['type'] = $product->type;
$GLOBALS['category_type'] = $product->category_type;
$GLOBALS['price'] = $product->price;
$GLOBALS['stock_quantity'] = $product->stock_quantity;
$GLOBALS['is_active'] = $product->is_active;





if (!$product) {
    redirect('productListing.php');
    temp('info', "Can't edit the record");
}




$is_active_options = ['1' => 'Yes', '0' => 'No'];
$errorMessagesImage = [];
$errorMessagesVideo = [];

if (is_post()) {

    // Retrieve images marked for deletion
    $imagesToDelete = req('delete_images', []);
    $videoToDelete = req('delete_videos', []);
    $name = strtoupper(req('name'));
    $description = req('description');
    $type = req('type');
    $category_type  = req('category_type');
    $price = req('price');
    // Check if product type is "Game" and set default stock_quantity to 1
    if ($type === 'Game') {
        $stock_quantity = 1;
    } else {
        $stock_quantity = req('stock_quantity');
    }
    $is_active = req('is_active');
    $photos = $_FILES['product_images'];
    $videos = $_FILES['product_videos']; // Add this line to handle video uploads
    $uploadedFiles = [];
    $uploadedVideos = []; // Array to hold uploaded video info
    $fileNames = []; // Initialize the variable as an empty array
    $fileNamesVideo = [];
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

            if ($videoSize > 15 * 1024 * 1024) {
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
    } else if (strlen($name) > 35) {  // Check if the username exceeds 35 characters
        $_err['name'] = 'Product Name cannot exceed 35 characters';
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

    if ($is_active == '') {
        $_err['is_active'] = 'Product active or unactive is required';
    }




    if (!empty($imagesToDelete)) {
        foreach ($imagesToDelete as $imageUrl) {
            // Delete the image from the database
            $stm = $_db->prepare('DELETE FROM product_image_video WHERE product_id = ? AND `url`= ? AND `type` = "photo"');
            $stm->execute([$product->id, $imageUrl]);

            // Optionally, delete the image file from the server
            if ($product->type === 'Game') {
                $filePath = "../../img/game/$imageUrl";
            } elseif ($product->type === 'Accessories') {
                $filePath = "../../img/accessories/$imageUrl";
            }

            if (file_exists($filePath)) {
                unlink($filePath);  // Remove the image file from the server
            }
            // Find the index of the image URL in the $image_urls array and remove it
            $index = array_search($imageUrl, $image_urls);
            if ($index !== false) {
                unset($image_urls[$index]); // Remove the specific URL from the array
                // Reindex the array to avoid gaps
                $image_urls = array_values($image_urls); // Reindex the array

            }
        }
    }
    if (!empty($videoToDelete)) {
        foreach ($videoToDelete as $videoUrl) {
            // Delete the video from the database
            $stm = $_db->prepare('DELETE FROM product_image_video WHERE product_id = ? AND `url`= ? AND `type` = "video"');
            $stm->execute([$product->id, $videoUrl]);

            // Optionally, delete the video file from the server
            if ($product->type === 'Game') {
                $filePath = "../../img/game/$videoUrl";
            } elseif ($product->type === 'Accessories') {
                $filePath = "../../img/accessories/$videoUrl";
            }

            if (file_exists($filePath)) {
                unlink($filePath);  // Remove the video file from the server
            }
            // Remove the video URL from the array
            $index = array_search($videoUrl, $video_urls);
            unset($video_urls[$index]);
            $video_urls = array_values($video_urls);  // Reindex the array


        }
    }

    if (!$_err && empty($errorMessagesVideo) && empty($errorMessagesImage)) {
        

        $stm = $_db->prepare('UPDATE `product` 
        SET `name` = ?, 
            `description` = ?, 
            `type` = ?, 
            `category_type` = ?, 
            `price` = ?, 
            `stock_quantity` = ?, 
            `release_date` = ?, 
            `is_active` = ?, 
            `last_date_operate` = CURRENT_TIMESTAMP 
        WHERE `id` = ?');

        $stm->execute([$name, $description, $type, $category_type, $price, $stock_quantity, $product->release_date, $is_active, $product->id]);

        // Check if there are uploaded files
        if ($uploadedFiles[0]['size'] != 0 || $image_urls[0] != null) {

            foreach ($uploadedFiles as $file) {
                if (!empty($file['tmp_name']) && file_exists($file['tmp_name'])) {
                    $newFileName = uniqid() . '.jpg'; 

                    // Determine the correct path based on the type
                    if ($type === 'Game') {
                        $filePath = "../../img/game/$newFileName"; 
                    } else if ($type === 'Accessories') {
                        $filePath = "../../img/accessories/$newFileName";  
                    } else {
                        $_err['type'] = 'Invalid product type'; // Just in case another type is provided
                        continue; 
                    }

                    // Load SimpleImage library and save the image
                    require_once '../../lib/SimpleImage.php';

                    // Initialize the SimpleImage class
                    $img = new SimpleImage();
                    $img->fromFile($file['tmp_name'])  
                        ->thumbnail(200, 200)
                        ->toFile($filePath, 'image/jpeg');  

                    // Store the file name
                    $fileNames[] = $newFileName;
                }
            }



            // Merge the existing image URLs with the new filenames
            $fileAllNames = array_merge($image_urls, $fileNames);  // Merge arrays
            $fileAllNames = array_filter($fileAllNames); // Remove any empty values
            $fileAllNames = array_values($fileAllNames); // Reindex the array
            // Combine the file names into a single string separated by commas
            $combinedFileNames = implode(', ', $fileAllNames);

            $stmImg = $_db->prepare('UPDATE `product_image_video` SET `url` = ? WHERE `product_id` = ? AND `type` = "photo";');
            $stmImg->execute([$combinedFileNames, $product->id]); // Update the image file related to the product

        }

        // Check for uploaded videos
        if ($uploadedVideos[0]['size'] != 0 || $video_urls[0] != NULL) {
            $fileNamesVideo = [];
            foreach ($uploadedVideos as $video) {
                if (!empty($video['tmp_name']) && file_exists($video['tmp_name'])) {

                    $newVideoName = uniqid() . '.' . pathinfo($video['name'], PATHINFO_EXTENSION);

                    // Determine the correct path based on the type
                    if ($type === 'Game') {
                        $videoPath = "../../img/game/$newVideoName";  
                    } else if ($type === 'Accessories') {
                        $videoPath = "../../img/accessories/$newVideoName"; 
                    } else {
                        $_err['type'] = 'Invalid product type'; // Just in case another type is provided
                        continue; 
                    }
                    move_uploaded_file($video['tmp_name'], $videoPath);

                    // Store the file name
                    $fileNamesVideo[] = $newVideoName;
                }
            }


            // Merge the existing image URLs with the new filenames
            $fileAllNamesVideo = array_merge($video_urls, $fileNamesVideo);  // Merge arrays
            $fileAllNamesVideo = array_filter($fileAllNamesVideo); // Remove any empty values
            $fileAllNamesVideo = array_values($fileAllNamesVideo); // Reindex the array

            // Combine the file names into a single string separated by commas
            $combinedFileNamesVideo = implode(', ', $fileAllNamesVideo);

            // Insert video path into database
            $stmVideo = $_db->prepare('UPDATE `product_image_video` SET `url` = ? WHERE `product_id` = ? AND `type` = "video";');
            $stmVideo->execute([$combinedFileNamesVideo, $product->id]); // Update the image file related to the product


        }
        temp('info', 'Successfully Edited ');
                    // Redirect after insertion
                    redirect('/pages/admin/productListing.php');
    }
    


}
?>
<div class="w-full product-section">
    <div class="profile-section">
        <div class="section-header">
            <h2 style="margin-left:275px;">Edit Product</h2>
        </div>

        <form method="post" enctype="multipart/form-data" id="product-form">
            <div class="form-container flex">
                <div style="float: left;">
                    <div style="width:600px;float:left;height:300px;">
                        <div class="form-group" style="height: 20px;width:200px">
                            <!-- Label with 'for' attribute linked to the input with the same id -->
                            <label>Upload Images</label>
                            <input type="file" name="product_images[]" id="product_images" accept="image/*" class="file-input" multiple onchange="handleImageUpload(event)">

                        </div>
                        <!-- Image Upload Section -->
                        <div class="image-preview" id="image-preview-container">
                            <!-- Placeholder for image previews -->
                            <div class="image-upload-placeholder" onclick="triggerFileInput()">
                                <span style="width:60px">Upload Image</span>
                            </div>

                            <?php foreach ($image_urls as $index => $image_url): ?>
                                <?php $image_url = trim($image_url); ?>
                                <?php $safeImageId = htmlspecialchars($image_url); ?>
                                <div class="image-preview-item" id="image-preview-<?= $safeImageId ?>">
                                    <?php if ($product->type === 'Game'): ?>
                                        <img src="../../img/game/<?= $image_url ?>" alt="Product Image" class="image-thumb">
                                    <?php elseif ($product->type === 'Accessories'): ?>
                                        <img src="../../img/accessories/<?= $image_url ?>" alt="Product Image" class="image-thumb">
                                    <?php endif; ?>

                                    <!-- Add a hidden input with the image URL when user clicks remove -->
                                    <button type="button" class="remove-image-button"
                                        onclick="removeExistingImage('<?= $safeImageId ?>', '<?= htmlspecialchars($image_url) ?>')">Remove</button>

                                    <!-- Hidden input for deletion is dynamically created in the JS function -->
                                </div>
                            <?php endforeach; ?>


                        </div>
                        <div>
                            <?php err1($errorMessagesImage) ?>

                        </div>
                    </div>
                    <div style="width:600px;float:left;height:300px;">
                        <div class="form-group" style="height: 20px;width:200px">
                            <!-- Label with 'for' attribute linked to the input with the same id -->
                            <label>Upload Video</label>
                            <input type="file" name="product_videos[]" id="product_videos" accept="video/*" class="file-input" multiple onchange="handleVideoUpload(event)">

                        </div>
                        <!-- Image Upload Section -->
                        <div class="image-preview" id="video-preview-container">
                            <!-- Placeholder for image previews -->
                            <div class="image-upload-placeholder" onclick="triggerVideoFileInput()">
                                <span style="width:60px">Upload Video</span>
                            </div>

                            <?php foreach ($video_urls as $index => $video_url): ?>
                                <?php $video_url = trim($video_url); ?>
                                <?php $safeVideoId = htmlspecialchars($video_url); ?>
                                <div class="video-preview-item" id="video-preview-<?= $safeVideoId ?>">
                                    <?php if ($product->type === 'Game'): ?>
                                        <video width="100%" height="100%" style="object-fit: cover;" controls>
                                            <source src="../../img/game/<?= htmlspecialchars($video_url) ?>" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    <?php elseif ($product->type === 'Accessories'): ?>
                                        <video width="100%" height="100%" style="object-fit: cover;" controls>
                                            <source src="../../img/accessories/<?= htmlspecialchars($video_url) ?>" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    <?php endif; ?>

                                    <!-- Add a hidden input with the video URL when user clicks remove -->
                                    <button type="button" class="remove-video-button"
                                        onclick="removeExistingVideo('<?= $safeVideoId ?>', '<?= htmlspecialchars($video_url) ?>')">Remove</button>

                                    <!-- Hidden input for deletion is dynamically created in the JS function -->
                                </div>
                            <?php endforeach; ?>



                        </div>
                        <div>
                            <?php err1($errorMessagesVideo) ?>

                        </div>
                    </div>
                </div>

                <!-- Product Details Section -->
                <div class="product-details" style="width:400px">
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <?= html_text1('name', 'oninput="this.value = this.value.toUpperCase()"'); ?><br>
                        <?= err('name') ?>
                    </div>


                    <div class="form-group">
                        <label for="description">Description</label>
                        <?= html_text('description', ''); ?><br>
                        <?= err('description') ?>
                    </div>
                    <div class="form-group">
                        <label for="type">Product Type</label>
                        <?= html_text('type', ''); ?><br>
                        <?= err('type') ?>
                    </div>

                    <div class=" form-group">
                        <label for="category_type">Category Type</label>
                        <?= html_text('category_type', ''); ?><br>
                        <?= err('category_type') ?>
                    </div>

                    <div class="form-group">
                        <label for="price">Price</label>
                        <?= html_text('price', ''); ?><br>
                        <?= err('price') ?>
                    </div>

                    <div class="form-group">
    <label for="stock_quantity">Stock Quantity</label>
    <input 
        type="text" 
        name="stock_quantity" 
        id="stock_quantity" 
        value="<?= htmlspecialchars($GLOBALS['stock_quantity']) ?>" 
        <?= ($GLOBALS['type'] === 'Game') ? 'disabled' : '' ?> 
    /><br>
    <?= err('stock_quantity') ?>
</div>

                    <div class="form-group">
                        <label for="is_active">Active</label>
                        <div class="date-group" style="height:40px;">
                            <?= html_select1('is_active', $is_active_options, '', 'class="date-select" id="is_active"'); ?>
                        </div>
                        <?= err('is_active') ?>
                    </div>


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
    let imageCount = <?= count($image_urls) ?>;
    const maxImages = 6; // Maximum number of images allowed
    let videoCount = <?= count($video_urls) ?>;
    const maxVideos = 3; // Maximum number of videos allowed
    let imagesToUpload = []; // Array to hold all images to be uploaded
    // Video upload handling
    let videosToUpload = []; // Array to hold all videos to be uploaded

    function triggerFileInput() {
        if (imageCount < maxImages) {
            document.getElementById('product_images').click();
        } else {
            alert('You can only upload a maximum of 6 images.');
        }
    }

    // Trigger file input when user clicks on the video upload placeholder
    function triggerVideoFileInput() {
        if (videoCount < maxVideos) {
            document.getElementById('product_videos').click();
        } else {
            alert('You can only upload a maximum of 3 videos.');
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
        const productTypeInput = document.getElementById('type'); // Assuming the ID is 'type'
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
    document.getElementById('type').addEventListener('input', toggleStockQuantity);

    // Remove the existing image preview by its identifier
    function removeExistingImage(imageIdentifier, imageUrl) {
        const previewContainer = document.getElementById('image-preview-container');
        const imagePreview = document.getElementById(`image-preview-${imageIdentifier}`);

        if (imagePreview) {
            previewContainer.removeChild(imagePreview);
            imageCount--;
        }

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_images[]';
        input.value = imageUrl;
        document.getElementById('product-form').appendChild(input);
    }

    // Remove the existing video preview by its identifier
    function removeExistingVideo(videoIdentifier, videoUrl) {
        const previewContainer = document.getElementById('video-preview-container');
        const videoPreview = document.getElementById(`video-preview-${videoIdentifier}`);

        if (videoPreview) {
            previewContainer.removeChild(videoPreview);
            videoCount--;
        }

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'delete_videos[]';
        input.value = videoUrl;
        document.getElementById('product-form').appendChild(input);
    }
</script>

<?php
include '../../include/_adminFooter.php';
