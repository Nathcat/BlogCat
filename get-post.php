<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: text/html");

include("parsedown/Parsedown.php");

$conn = new mysqli("localhost:3306", "blog", "", "BlogCat");
$stmt = $conn->prepare("SELECT Posts.*, SSO.Users.username AS authorName FROM Posts JOIN SSO.Users ON SSO.Users.id = Posts.author WHERE Posts.id = ?");
$stmt->bind_param("i", $_GET["page"]);
$stmt->execute();
$page = $stmt->get_result()->fetch_assoc();

$pd = new Parsedown();

if ($page === null) : ?>
    <div class="main align-center justify-center">
        <h1>Whoops! This page doesn't exist!</h1>
        <h2><a href=\"/\">Return home</a></h2>
    </div>
<?php else : $content = file_get_contents("posts/" . $page["filePath"]); ?>

    <div class="post-content">
        <div style="width: fit-content" class="row align-center justify-center">
            <p><i>Published <?php echo $page["timePublished"]; ?> by <?php echo $page["authorName"]; ?></i></p>
        </div>

        <a href="/">Return home</a>
        <?php

        echo $pd->text($content);
        ?>
    </div>
<?php

endif;

$stmt->close();
$conn->close();

?>