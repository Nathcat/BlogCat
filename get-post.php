<?php
header("Access-Control-Allow-Origin: https://blog.nathcat.net");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: text/html");

session_name("AuthCat-SSO");
session_start();

if (!array_key_exists("user", $_SESSION)) : ?>
    <div class="main align-center justify-center">
        <h1>You are not logged in!</h1>
    </div>
<?php exit(); endif;

include("parsedown/Parsedown.php");

$conn = new mysqli("localhost:3306", "blog", "", "BlogCat");
$stmt = $conn->prepare("SELECT Posts.*, SSO.Users.username AS authorName FROM Posts JOIN SSO.Users ON SSO.Users.id = Posts.author WHERE Posts.id = ?");
$stmt->bind_param("i", $_GET["page"]);
$stmt->execute();
$page = $stmt->get_result()->fetch_assoc();

$stmt->close();
$stmt = $conn->prepare("SELECT `follows` FROM followers WHERE id = ?");
$stmt->bind_param("i", $_SESSION["user"]["id"]);
$stmt->execute(); $set = $stmt->get_result();
$follows_author = false;
while ($r = $set->fetch_assoc()) {
    if ($r["follows"] === $page["author"]) {
        $follows_author = true;
        break;
    }
}

$pd = new Parsedown();

if ($page === null) : ?>
    <div class="main align-center justify-center">
        <h1>Whoops! This page doesn't exist!</h1>
        <h2><a href="/">Return home</a></h2>
    </div>
<?php else : $content = file_get_contents("posts/" . $page["filePath"]); ?>

    <div class="post-content">
        <div style="width: fit-content" class="row align-center justify-center">
            <p>
                <i>Published <?php echo $page["timePublished"]; ?> by <?php echo $page["authorName"]; ?></i>
                <?php if ($page["author"] != $_SESSION["user"]["id"]) : ?> 
                    <button onclick="fetch('/follow.php?id=<?php echo $page['author']; ?>', { method: 'GET', credentials: 'include' }).then((r) => r.json()).then((r) => r.status === 'success' ? location.reload() : alert(r.message))"><?php echo $follows_author ? "Unfollow" : "Follow"; ?></button>
                <?php endif; ?>
            </p>
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