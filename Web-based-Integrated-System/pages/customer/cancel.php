<!-- cancel.php -->
<?php
require '../../include/_base.php';

auth('customer');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Untitled' ?></title>
    <link rel="stylesheet" href="/css/app.css">
    <link rel="stylesheet" href="/css/id.css">
    <script src="/js/app.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>
    <main>
        <div class="w-full flex justify-center items-start">
            <div class="login-card flex justify-start items-center" style="margin-top: 12vw;">
                <img src="/img/icon&logo/cross-mark_.png" alt="" class="correct-icon">
                <?php echo "Payment was cancelled. Please try again."; ?>
                <form action="/" method="get">
                    <button type="submit" class="btn full-rounded">
                        <span>Back To Home</span>
                        <div class="border full-rounded" style="width: 27.2em;"></div>
                    </button>
                </form>
            </div>

        </div>
    </main>
</body>