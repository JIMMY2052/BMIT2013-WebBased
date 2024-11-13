<?php
require '../../include/_base.php';
auth('admin', 'superAdmin');
$title = 'View Product';
$currentPage = basename($_SERVER['PHP_SELF']);
include '../../include/_adminHead.php';
require_once '../../lib/SimplePager.php';
$page = req('page', 1);

if (is_get()) {
    $id = req('id');


    $stm = $_db->prepare('SELECT * FROM product WHERE id = ?');
    $stm->execute([$id]);
    $product = $stm->fetch();
    if (!$product) {
        redirect('productListing.php');
        temp('info', "Not found the record");
    }

    $sql_images = "SELECT GROUP_CONCAT(url SEPARATOR ',') AS urls FROM product_image_video WHERE product_id = ? AND type = 'photo'";
    $images_stmt = $_db->prepare($sql_images);
    $images_stmt->execute([$product->id]);
    $image_result = $images_stmt->fetch(); // Fetch a single row
    $image_urls = [];
    // Check if URLs were found
    if (!empty($image_result->urls)) {
        $image_urls = explode(', ', $image_result->urls); // Separate URLs into an array

    } else {
        $image_urls[] = 'default.jpg'; 
    }

    $sql_video = "SELECT GROUP_CONCAT(url SEPARATOR ',') AS urls FROM product_image_video WHERE product_id = ? AND type = 'video'";
    $images_stmt = $_db->prepare($sql_video);
    $images_stmt->execute([$product->id]);
    $video_result = $images_stmt->fetch(); // Fetch a single row

    $video_urls = [];
    // Check if URLs were found
    if (!empty($video_result->urls)) {
        $video_urls = explode(', ', $video_result->urls); 

    } else {
        $video_urls[] = 'default.jpg'; // Set a default image if none found
    }
}





?>

<div class="container">
    <div class="product-view-section">
        <div class="product-header">
            <div class="image_video">
                <div class="product">
                    <?php if ($product->type == 'Accessories') : ?>
                        <div class="slideshow-container">
                            <?php if ($image_urls[0] != null):; // Use image_urls array 
                            ?>
                                <?php foreach ($image_urls as $index => $image_url): ?>
                                    <!-- Use trim() to remove any unwanted spaces -->
                                    <?php $image_url = trim($image_url); ?>
                                    <div class="photoSlides">
                                        <img src="../../img/accessories/<?= htmlspecialchars($image_url) ?>"
                                            alt="Accessories Picture"
                                            style="width: 300px; height: 300px;">
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="photoSlides">
                                    <img src="../../img/accessories/default.jpg"
                                        alt="Default Accessories Picture"
                                        style="width: 300px; height: 300px;">
                                </div>
                            <?php endif; ?>
                            <!-- Next and Previous Buttons for Photos -->
                            <a class="prev" onclick="plusPhotoSlides(-1)">&#10094;</a>
                            <a class="next" onclick="plusPhotoSlides(1)">&#10095;</a>
                        </div>
                    <?php elseif ($product->type == 'Game') : ?>
                        <div class="slideshow-container">
                            <?php if ($image_urls[0] != null) : // Use image_urls array 
                            ?>
                                <?php foreach ($image_urls as $index => $image_url): ?>
                                    <!-- Use trim() to remove any unwanted spaces -->
                                    <?php $image_url = trim($image_url); ?>
                                    <div class="photoSlides">
                                        <img src="../../img/game/<?= htmlspecialchars($image_url) ?>"
                                            alt="Game Picture"
                                            style="width: 300px; height: 300px;">
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="photoSlides">
                                    <img src="../../img/game/default.jpg"
                                        alt="Default Game Picture"
                                        style="width: 300px; height: 300px;">
                                </div>
                            <?php endif; ?>
                            <!-- Next and Previous Buttons for Photos -->
                            <a class="prev" onclick="plusPhotoSlides(-1)">&#10094;</a>
                            <a class="next" onclick="plusPhotoSlides(1)">&#10095;</a>
                        </div>
                    <?php else : ?>
                        <img src="../../img/game/default.jpg"
                            alt="Null Picture"
                            style="width: 300px; height: 300px;">
                    <?php endif; ?>
                    <!-- Add slide counter -->
                    <div class="slide-counter">
                        <span id="photo-slide-number">1</span> / <?= count($image_urls) > 0 ? count($image_urls) : 1 ?> &nbsp; Photos
                    </div>
                </div>

                <div class="product">
                    <?php if ($product->type == 'Accessories') : ?>
                        <div class="slideshow-container">
                            <?php if ($video_urls[0] != null) : // Check if video URLs are available 
                            ?>
                                <?php foreach ($video_urls as $index => $video_url): ?>
                                    <?php $video_url = trim($video_url); ?>
                                    <div class="videoSlides">
                                        <video width="300px" height="300px" controls>
                                            <source src="../../img/accessories/<?= htmlspecialchars($video_url) ?>" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="photoSlides">
                                    <img src="../../img/game/default.jpg"
                                        alt="Default Game Picture"
                                        style="width: 300px; height: 300px;">
                                </div>
                            <?php endif; ?>
                            <!-- Next and Previous Buttons for Videos -->
                            <a class="prev" onclick="plusVideoSlides(-1)">&#10094;</a>
                            <a class="next" onclick="plusVideoSlides(1)">&#10095;</a>
                        </div>
                    <?php elseif ($product->type == 'Game') : ?>
                        <div class="slideshow-container">
                            <?php if ($video_urls[0] != null) : // Check if video URLs are available 
                            ?>
                                <?php foreach ($video_urls as $index => $video_url): ?>
                                    <?php $video_url = trim($video_url); ?>
                                    <div class="videoSlides">
                                        <video width="300px" height="300px" controls>
                                            <source src="../../img/game/<?= htmlspecialchars($video_url) ?>" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="photoSlides">
                                    <img src="../../img/game/default.jpg"
                                        alt="Default Game Picture"
                                        style="width: 300px; height: 300px;">
                                </div>
                            <?php endif; ?>
                            <!-- Next and Previous Buttons for Videos -->
                            <a class="prev" onclick="plusVideoSlides(-1)">&#10094;</a>
                            <a class="next" onclick="plusVideoSlides(1)">&#10095;</a>
                        </div>
                    <?php else : ?>
                        <img src="../../img/game/default.jpg"
                            alt="Null Picture"
                            style="width: 300px; height: 300px;">
                    <?php endif; ?>
                    <!-- Add slide counter -->
                    <div class="slide-counter">
                        <span id="video-slide-number">1</span> / <?= count($video_urls) > 0 ? count($video_urls) : 1 ?> &nbsp; Videos
                    </div>
                </div>

            </div>






            <div class="product-info">
                <h1 style="color: black;"><?= $product->name ?></h1>
                <div class="product-detail">
                    <p><strong>Product ID &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp:</strong> #<?= $product->id ?></p>
                    <p><strong>Description&nbsp &nbsp &nbsp &nbsp &nbsp: </strong><?= $product->description; ?></p>
                    <p><strong>Type&nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp &nbsp: </strong><?= $product->type; ?></p>
                    <p><strong>Category Type &nbsp: </strong><?= $product->category_type ?></p>
                    <p><strong>Release Date&nbsp &nbsp &nbsp : </strong><?= date('d-m-Y', strtotime($product->release_date)); ?></p>
                    <div class="product-status">
                        <p style=""><?= ($product->is_active == 1) ? '<span class="product-status active">Active</span>' : '<span class="product-status blocked">Deactivate</span>'; ?></p>
                    </div>
                    <div style="display:flex; gap:10px;margin-right:20px">
                        <div >
                    <form action="editProduct.php?id=<?= $product->id ?>" class="product-edit-form" title="Edit">
                        <input type="hidden" name="id" value="<?= $product->id ?>">
                        <button type="submit" class="save-button" title="Edit" style="width: 150px;height:80px;">
                            <img src="/img/adminIcon/edit.svg" alt="Edit"> Edit
                        </button>
                    </form>
                        </div>
                        <div>
                    <form action="deleteProduct.php" method="POST" class="product-delete-form" onsubmit="return confirm('Are you sure you want to delete product <?= $product->id; ?>?');">
                        <input type="hidden" name="id" value="<?= $product->id ?>">
                        <button type="submit" class="discard-button" title="Delete" style="width: 150px;height:80px;">
                            <img src="/img/adminIcon/delete.svg" alt="Delete"> Deactivate
                        </button>
                    </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<div class="product-statistics">
    <div class="stat-box">
        <img src="/img/adminIcon/receipt.svg" alt="Price Icon">
        <div>
            <p>Price</p>
            <h3>RM<?= $product->price; ?></h3>
        </div>
    </div>
    <div class="stat-box">
        <img src="/img/adminIcon/package.svg" alt="Stock Icon">
        <div>
            <p>Total Stock</p>
            <h3><?= $product->stock_quantity ?></h3>
        </div>
    </div>
</div>
</div>




<?php
include '../../include/_adminFooter.php';
