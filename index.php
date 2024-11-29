<!DOCTYPE html>
<html>
    <head>
        <title>BlogCat</title>

        <link rel="stylesheet" href="https://nathcat.net/static/css/new-common.css">
        <link rel="stylesheet" href="/static/styles/home.css">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    </head>

    <?php
    if (array_key_exists("search",  $_GET)) {
        $conn = new mysqli("localhost:3306", "blog", "", "BlogCat");
        $stmt = $conn->prepare("SELECT Posts.*, SSO.Users.username AS authorName FROM Posts JOIN SSO.Users ON SSO.Users.id = Posts.author WHERE title like ?");
        $search_term = "%" . $_GET["search"] . "%";
        $stmt->bind_param("s", $search_term);
        $stmt->execute(); $results = $stmt->get_result();
    }
    else if (array_key_exists("page", $_GET)) {
        $conn = new mysqli("localhost:3306", "blog", "", "BlogCat");
        $stmt = $conn->prepare("SELECT Posts.*, SSO.Users.username AS authorName FROM Posts JOIN SSO.Users ON SSO.Users.id = Posts.author WHERE Posts.id = ?");
        $stmt->bind_param("i", $_GET["page"]);
        $stmt->execute(); $page = $stmt->get_result()->fetch_assoc();
    }
    else {
        $conn = new mysqli("localhost:3306", "blog", "", "BlogCat");
        $stmt = $conn->prepare("SELECT Posts.*, SSO.Users.username AS authorName FROM Posts JOIN SSO.Users ON SSO.Users.id = Posts.author ORDER BY timePublished DESC LIMIT 1");
        $stmt->execute(); $post = $stmt->get_result()->fetch_assoc();
    }
    ?>

    <body>
        <div class="content">
            <?php include("parsedown/Parsedown.php"); include("header.php"); ?>

            <?php if (array_key_exists("search", $_GET)) : ?>
                <div class="column align-center" style="justify-content: start">
                    <?php
                    $i = 0;
                    while ($post = $results->fetch_assoc()) {
                        include("search-result.php");
                        $i++;
                    }
                    $stmt->close();
                    $conn->close();

                    if ($i === 0) {
                        echo "<h1>No results!</h1><h2><a href='/'>Return home</a></h2>";
                    }
                    ?>
                </div>
                
            <?php elseif (array_key_exists("page", $_GET)) : ?>
                <?php 
                if ($page === null) {
                    echo "<div class=\"main align-center justify-center\"><h1>Whoops! This page doesn't exist!</h1><h2><a href=\"/\">Return home</a></h2></div>";
                }
                ?>

                <?php if ($page !== null) : ?>
                <div class="post-content">
                    <div style="width: fit-content" class="row align-center justify-center">
                        <p><i>Published <?php echo $page["timePublished"]; ?> by <?php echo $page["authorName"]; ?></i></p>
                    </div>

                    <a href="/">Return home</a>

                    <?php 
                    $Parsedown = new Parsedown();

                    echo $Parsedown->text(file_get_contents("posts/" . $page["filePath"]));
                    $stmt->close();
                    $conn->close();
                    ?>
                </div>
                <?php endif ; ?>

            <?php else : ?>
            <div class="main align-center justify-center">
                <h1>Welcome to BlogCat!</h1>

                <form class="search-form column align-center justify-center" action="/" method="GET">
                    <input type="text" name="search" placeholder="Search for a post">
                </form>

                <h2>Or, view the most recent post</h2>
                <?php include("search-result.php"); ?>
            </div>

            <?php endif ; ?>

            <?php include("footer.php"); ?>
        </div>
    </body>
</html>