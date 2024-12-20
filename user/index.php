<!DOCTYPE html>
<html>

<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://nathcat.net/static/css/new-common.css">
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
</script>

</html>