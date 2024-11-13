<?php
require '../../include/_base.php';
auth('admin', 'superAdmin');
$title = 'Dashboard';
$file = $_SERVER['PHP_SELF'];
$currentPage = basename($_SERVER['PHP_SELF']);
include '../../include/_adminHead.php';


// Total Sales
$stm = $_db->prepare("SELECT SUM(total_amount) AS total_sales FROM `order` WHERE status = 'completed';");
$stm->execute();
$total_sales_result = $stm->fetch();
$total_sales = $total_sales_result->total_sales ?? 'Haven record';

// Total Users
$stm = $_db->prepare("SELECT COUNT(*) AS total_users FROM `users`;");
$stm->execute();
$total_users_result = $stm->fetch();
$total_users = $total_users_result->total_users ?? 'Haven record';

// Total Completed Orders
$stm = $_db->prepare("SELECT COUNT(*) AS total_completed_orders FROM `order` WHERE status = 'completed';");
$stm->execute();
$total_order_completed_result = $stm->fetch();
$total_order_completed = $total_order_completed_result->total_completed_orders ?? 'Haven record';

// Total Products
$stm = $_db->prepare("SELECT COUNT(*) AS total_products FROM `product`;");
$stm->execute();
$total_product_result = $stm->fetch();
$total_product = $total_product_result->total_products ?? 'Haven record';

// Top 6 Categories by Sales Percentage
$stm = $_db->prepare("
    SELECT p.category_type, SUM(i.subtotal) / total.total_sales * 100 AS sales_percentage
    FROM item i
    JOIN product p ON i.product_id = p.id
    JOIN (SELECT SUM(subtotal) AS total_sales FROM item) total ON 1=1
    GROUP BY p.category_type
    ORDER BY sales_percentage DESC
    LIMIT 6
");
$stm->execute();
$total_top_category_result = $stm->fetchAll();

$categories = [];
$percentages = [];

if ($total_top_category_result) {
    foreach ($total_top_category_result as $row) {
        $categories[] = $row->category_type;
        $percentages[] = number_format($row->sales_percentage, 2);
    }
} else {
    $categories[] = 'Haven record';
    $percentages[] = 0;
}

// Top 8 Products by Quantity
$stm = $_db->prepare("
    SELECT p.name AS product_name, SUM(i.quantity) AS total_quantity
    FROM item i
    JOIN product p ON i.product_id = p.id
    GROUP BY p.id, p.name
    ORDER BY total_quantity DESC
    LIMIT 8
");
$stm->execute();
$top_quantity_products = $stm->fetchAll();

$products = [];
$quantities = [];

if ($top_quantity_products) {
    foreach ($top_quantity_products as $row) {
        $products[] = $row->product_name;
        $quantities[] = $row->total_quantity;
    }
} else {
    $products[] = 'Haven record';
    $quantities[] = 0;
}

// Age Group Distribution
$ageGroupQuery = $_db->prepare("
    SELECT 
        CASE 
            WHEN FLOOR(DATEDIFF(CURDATE(), dob) / 365.25) BETWEEN 1 AND 5 THEN '1-5'
            WHEN FLOOR(DATEDIFF(CURDATE(), dob) / 365.25) BETWEEN 6 AND 10 THEN '6-10'
            WHEN FLOOR(DATEDIFF(CURDATE(), dob) / 365.25) BETWEEN 11 AND 15 THEN '11-15'
            WHEN FLOOR(DATEDIFF(CURDATE(), dob) / 365.25) BETWEEN 16 AND 20 THEN '16-20'
            WHEN FLOOR(DATEDIFF(CURDATE(), dob) / 365.25) BETWEEN 21 AND 25 THEN '21-25'
            WHEN FLOOR(DATEDIFF(CURDATE(), dob) / 365.25) BETWEEN 26 AND 30 THEN '26-30'
            WHEN FLOOR(DATEDIFF(CURDATE(), dob) / 365.25) BETWEEN 31 AND 35 THEN '31-35'
            WHEN FLOOR(DATEDIFF(CURDATE(), dob) / 365.25) BETWEEN 36 AND 40 THEN '36-40'
            WHEN FLOOR(DATEDIFF(CURDATE(), dob) / 365.25) BETWEEN 41 AND 45 THEN '41-45'
            WHEN FLOOR(DATEDIFF(CURDATE(), dob) / 365.25) BETWEEN 46 AND 50 THEN '46-50'
            WHEN FLOOR(DATEDIFF(CURDATE(), dob) / 365.25) BETWEEN 51 AND 55 THEN '51-55'
            WHEN FLOOR(DATEDIFF(CURDATE(), dob) / 365.25) BETWEEN 56 AND 60 THEN '56-60'
            ELSE '60+' 
        END AS age_group,
        COUNT(*) AS user_count
    FROM users
    WHERE dob IS NOT NULL
    GROUP BY age_group
");
$ageGroupQuery->execute();
$age_group_result = $ageGroupQuery->fetchAll();

$age_groups = [];
$user_counts = [];

if ($age_group_result) {
    foreach ($age_group_result as $row) {
        $age_groups[] = $row->age_group;
        $user_counts[] = $row->user_count;
    }
} else {
    $age_groups[] = 'Haven record';
    $user_counts[] = 0;
}

$age_groups = json_encode($age_groups);
$user_counts = json_encode($user_counts);
?>

<div class="container">
    <div class="one-row">
        <div class="data">
            <div class="card" style="background-color: #7d00b5;">
                <div class="card-inside">
                    <?php if ($total_sales === 'Haven record') { ?>
                        <div class="inside">
                            <h2 class="data-collection"><?= $total_sales ?></h2>
                            <div class="collection">
                            </div>
                        </div>
                    <?php } else { ?>

                        <div class="inside">
                            <h2 class="data-collection">RM<?= $total_sales ?></h2>
                            <div class="collection">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="inside">
                        <p class="data-collection">Total Sales</p>
                        <div class="collection"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="data">
            <div class="card" style="background-color: #265ed7;">
                <div class="card-inside">
                    <?php if ($total_users === 'Haven record') { ?>
                        <div class="inside">
                            <h2 class="data-collection"><?= $total_users ?></h2>
                            <div class="collection">
                            </div>
                        </div>
                    <?php } else { ?>

                        <div class="inside">
                            <h2 class="data-collection"><?= $total_users ?></h2>
                            <div class="collection">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="inside">
                        <p class="data-collection">Total Users</p>
                        <div class="collection"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="data">
            <div class="card " style="background-color: #ff007c;">
                <div class="card-inside">
                    <?php if ($total_order_completed === 'Haven record') { ?>
                        <div class="inside">
                            <h2 class="data-collection"><?= $total_order_completed ?></h2>
                            <div class="collection">
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="inside">
                            <h2 class="data-collection"><?= $total_order_completed ?></h2>
                            <div class="collection">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="inside">
                        <p class="data-collection">Total Order</p>
                        <div class="collection"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="data">
            <div class="card" style="background-color: #ff8b01;">
                <div class="card-inside">
                    <?php if ($total_product === 'Haven record') { ?>
                        <div class="inside">
                            <h2 class="data-collection"><?= $total_product ?></h2>
                            <div class="collection">
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="inside">
                            <h2 class="data-collection"><?= $total_product ?></h2>
                            <div class="collection">
                            </div>
                        </div>
                    <?php } ?>
                    <div class="inside">
                        <p class="data-collection">Total Product</p>
                        <div class="collection"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="one-row">
        <div class="data2">
            <div class="data2-inside">
                <div class="card-inside">
                    <div class="top-category">
                        <h5 class="sub-title">Top Categories</h5>
                    </div>
                    <?php if ($categories[0] === 'Haven record') { ?>
                        <div class="sub-title">
                            <p><?= $categories[0] ?> <span class="right"><?= $percentages[0] ?>%</span>
                            </p>
                            <div class="progress" style="height:5px;">
                                <div class="progress-bar" role="progressbar" style="width: <?= $percentages[0] ?>%"></div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <?php
                        // Limit the loop to either the length of categories or 10, whichever is smaller
                        $max = min(10, count($categories));
                        for ($i = 0; $i < $max; $i++) {
                        ?>
                            <div class="sub-title">
                                <p><?= $categories[$i] ?> <span class="right"><?= $percentages[$i] ?>%</span></p>
                                <div class="progress" style="height:5px;">
                                    <div class="progress-bar" role="progressbar" style="width: <?= $percentages[$i] ?>%"></div>
                                </div>
                            </div>
                        <?php
                        }

                        ?>
                    <?php } ?>

                </div>
            </div>
        </div>
        <div class="data3">
            <div class="card-sales">
                <div class="card-inside">
                    <div style=" margin-bottom: 1.5rem;">
                        <div>
                            <h5>Top Product</h5>
                        </div>
                        <div>
                            <h3><span>Hot Sales:</span> <?= $products[0] ?></h3>
                        </div>
                    </div>
                    <hr />
                    <?php if ($products[0] === 'Haven record') { ?>
                        <ul class="list-group">

                            <li class="list-group-item">
                                <div>
                                    <h6><?= $products[0] ?></h6>
                                </div>
                                <div class="number"><?= $quantities[0] ?></div>

                            </li>
                        </ul>
                    <?php } else { ?>
                        <div class="dashboard-top-countries">
                            <ul class="list-group">
                                <div class="dashboard-top-countries">
                                    <ul class="list-group">
                                        <?php
                                        // Loop through products and quantities, limiting the loop to a maximum of 8 items
                                        $max = min(10, count($products));
                                        for ($i = 0; $i < $max; $i++) {
                                        ?>
                                            <li class="list-group-item">
                                                <div>
                                                    <h6><?= $products[$i] ?></h6>
                                                </div>
                                                <div class="number"><?= $quantities[$i] ?></div>
                                            </li>
                                        <?php
                                        }
                                        ?>
                                    </ul>
                                </div>
                            <?php
                        }
                            ?>

                        </div>
                </div>
            </div>
        </div>
        <div class="one-row">
            <div class="data3" style="width: 100%;">
                <div class="card-sales" style="width: 94%;">
                    <h2>Line Chart about which Age Group are More use our System</h2>
                    <div class="card-inside">
                        <div class="chart-container">
                            <!-- Canvas for rendering the line chart -->
                            <canvas id="myLineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Include Chart.js from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Get the canvas element and context
        const ctx = document.getElementById('myLineChart').getContext('2d');

        // Parse PHP arrays into JavaScript arrays
        const ageGroups = <?php echo $age_groups; ?>; // This will hold the age groups
        const userCounts = <?php echo $user_counts; ?>; // This will hold the user counts

        // Create the chart
        const myLineChart = new Chart(ctx, {
            type: 'line', // Define the chart type
            data: {
                labels: ageGroups, // Use the age groups as X-axis labels
                datasets: [{
                    label: 'User Count by Age Group', // Label for the dataset
                    data: userCounts, // Use the user counts as data points for the chart
                    borderColor: 'rgba(75, 192, 192, 1)', // Line color
                    backgroundColor: 'rgba(75, 192, 192, 0.2)', // Fill color below the line
                    borderWidth: 2, // Thickness of the line
                    fill: true, // Fill below the line
                    tension: 0.4 // Curved line
                }]
            },
            options: {
                responsive: true, // Make chart responsive
                plugins: {
                    legend: {
                        display: true, // Show legend
                        position: 'top' // Position of the legend
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true // Start the x-axis from zero
                    },
                    y: {
                        beginAtZero: true // Start the y-axis from zero
                    }
                }
            }
        });
    </script>

    <?php
    include '../../include/_adminFooter.php';
