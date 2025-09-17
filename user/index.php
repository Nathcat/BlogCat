<!DOCTYPE html>
<html>

<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@40,400,0,0&icon_names=delete" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://nathcat.net/static/css/new-common.css">
    <link rel="stylesheet" href="/static/styles/home.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <title>BlogCat</title>
</head>

<body>
    <?php $__REMOVE_PROFILE_BANNER__ = 1; ?>

    <?php include("../header.php"); ?>

    <div class="main align-center">
        <a href="/">Return to home page</a>

        <div class="column justify-center align-center">
            <h1>Welcome, <?php echo $_SESSION["user"]["fullName"]; ?>.</h1>

            <div class="profile-picture">
                <img src="<?php echo "$_DATA_BASE_URL/pfps/" . $_SESSION["user"]["pfpPath"]; ?>">
            </div>

            <div id="user-data-container" class="content-card" style="width: 100%;">
                <p><b><i>Loading...</i></b></p>
            </div>
        </div>

        <div id="user-posts" class="column justify-center align-center" style="width: 100%;">
                <h1 style="width: fit-content;">Your posts</h1>
                <?php 
                $conn = new mysqli("localhost:3306", "blog", "", "BlogCat");
                if ($conn->connect_error) {
                    die("{\"status\": \"fail\", \"message\": \"Failed to connect to the database: " . $conn->connect_error . "\"}");
                }

                try {
                    mysqli_report(MYSQLI_REPORT_ERROR);
                    $stmt = $conn->prepare("SELECT Posts.*, SSO.Users.username AS 'authorName' FROM Posts JOIN SSO.Users ON author = SSO.Users.id WHERE author = ?");
                    $stmt->bind_param("i", $_SESSION["user"]["id"]);
                    $stmt->execute();
                    $results = $stmt->get_result();

                    $i = 0;
                    while ($post = $results->fetch_assoc()) {
                        echo "<div class='row justify-center align-center' style='width: 100%;'>";
                        include("../search-result.php");
                        echo "<button class='delete-button' onclick=\"ask_delete(" . $post["id"] . ", '"  . $post["title"] . "')\"><span class='material-symbols-outlined'>delete</span></button>";
                        echo "</div>";
                        $i++;
                    }

                    if ($i == 0) {
                        echo "<p><i>You have created no posts!</i></p>";
                    }
                }
                catch (Exception $e) {
                    echo "<p><i>Failed to fetch posts! $e</p>";
                }
                ?>
            </div>
    </div>

    <?php include("../footer.php"); ?>
</body>

<script>
fetch("https://data.nathcat.net/blog/get-followers.php", {
    method: "GET",
    credentials: "include"
}).then((r) => r.json()).then((r) => {
    $("#user-data-container").html(
        "<p>" + r.followers + " followers</p>"
    );
});

function ask_delete(id, name) {
    if (confirm("Are you sure you want to delete \"" + name + "\"?")) {
        fetch("../delete-post.php", {
            method: "POST",
            credentials: "include",
            headers: {"Content-Type": "applicaiton/json"},
            body: JSON.stringify({
                "id": id
            })
        }).then((r) => r.json()).then((r) => {
            if (r.status === "success") {
                alert("Post was deleted");
                location.reload();
            }
            else {
                alert("Failed to delete post! " + r.message);
            }
        });
    }
}
</script>

</html>