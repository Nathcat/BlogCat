<!DOCTYPE html>
<html>

<head>
    <title>BlogCat - Create Post</title>

    <link rel="stylesheet" href="https://nathcat.net/static/css/new-common.css">
    <link rel="stylesheet" href="/static/styles/home.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="  https://cdn.jsdelivr.net/npm/showdown@2.1.0/dist/showdown.min.js"></script>
</head>

<body>
    <div class="content">
        <?php include("../header.php"); ?>

        <?php
        $conn = new mysqli("localhost:3306", "blog", "", "BlogCat");
        $stmt = $conn->prepare("INSERT IGNORE INTO UserData (id) VALUES (?)");
        $stmt->bind_param("i", $_SESSION["user"]["id"]);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("SELECT * FROM UserData WHERE id = ?");
        $stmt->bind_param("i", $_SESSION["user"]["id"]);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($res["can_post"] === 0) :
        ?>
            <div class="main align-center justify-center">
                <h1>Whoops!</h1>
                <p>You don't have access to this!</p>
                <a href="/">Return</a>
            </div>
        <?php else : ?>
            <div class="main column align-center" style="margin-bottom: 50px">

                <div class="row" style="width: 100%; align-items: start; margin-bottom: 25px;">
                    <input style="font-size: 3vw;" id="title-entry" type="text" placeholder="Post title...">
                </div>

                <div class="row align-center" style="width: 100%; height: 100%; margin-bottom: 50px;">
                    <textarea id="post-edit-content"></textarea>
                    <div id="post-preview" class="post-content" style="margin-left: 10px"></div>
                </div>

                <div class="row align-center justify-center" style="width: 100%">
                    <button onclick="publish();" style="font-size: 2vw; padding: 25px;">Publish</button>
                    <span class="half-spacer"></span>
                    <button onclick="location = '/';" style="font-size: 2vw; padding: 25px;">Delete</button>
                </div>
            </div>

            <script>
                var converter = new showdown.Converter();

                $("#post-edit-content").on("input", function(e) {
                        document.getElementById("post-preview").innerHTML = converter.makeHtml($(this).val());

                    $(".post-content a").each(function() {
                        $(this).attr("target", "_blank");
                    });
                });

                var ask_before_unload = function() {
                    if ($("#post-edit-content").val() !== "") {
                        return "This post will be lost if you close this tab, are you sure you want to continue?";
                    }
                };

                window.onbeforeunload = ask_before_unload;

                function publish() {
                    if ($("#title-entry").val() === "" || $("post-edit-content") === "") {
                        alert("Please make sure you have entered values for all fields!");
                        return;
                    }

                    window.onbeforeunload = function(e) {};

                    fetch("publish.php", {
                        method: "POST",
                        credentials: "include",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            "title": $("#title-entry").val(),
                            "content": $("#post-edit-content").val()
                        })
                    }).then((r) => r.json()).then((r) => {
                        if (r.status === "success") location = "/?page=" + r.post_id;
                        else alert("An error occurred!" + r.message);
                    });
                }
            </script>

        <?php endif; ?>

        <?php include("../footer.php"); ?>
    </div>
</body>

</html>