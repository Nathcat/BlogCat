<?php 
header("Access-Control-Allow-Origin: https://blog.nathcat.net");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");
header("Accept: application/json");

session_name("AuthCat-SSO");
//session_set_cookie_params(0, "/", ".nathcat.net");
session_start();

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in\"}");
}

$content = json_decode(file_get_contents("php://input"), true);

try {
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

    if ($res["can_post"] === 0) {
        die("{\"status\": \"fail\", \"message\": \"You are not allowed to do this.\"}");
    }


    $stmt = $conn->prepare("CALL create_post(?, ?)");
    $stmt->bind_param("is", $_SESSION["user"]["id"], $content["title"]);
    $stmt->execute(); $res = $stmt->get_result()->fetch_assoc();

    $file = fopen("../posts/" . $res["file_path"], "w");
    fwrite($file, $content["content"]);
    fclose($file);

    echo "{\"status\": \"success\", \"post_id\": " . $res["post_id"] . "}";
    $stmt->close();

    $stmt = $conn->prepare("SELECT id FROM followers WHERE `follows` = ?");
    $stmt->bind_param("i", $_SESSION["user"]["id"]);
    $stmt->execute(); $set = $stmt->get_result();

    $mail_subject = "BlogCat: New post from " . $_SESSION["user"]["username"];
    $mail_content = "<p>Dear, \$fullName\$</p><p>You have a new post from " . $_SESSION["user"]["username"] . " available to view on BlogCat!</p><p><a href='https://blog.nathcat.net/?page=" . $res["post_id"] . "'>Click here to view it!</a></p><p>Best wishes,<br>Nathan.</p>";

    while ($r = $set->fetch_assoc()) {
        $mail_stmt = $conn->prepare("INSERT INTO Mailer.MailToSend (recipient, subject, content) VALUES (?, ?, ?)");
        $mail_stmt->bind_param("iss", $r["id"], $mail_subject, $mail_content);
        $mail_stmt->execute(); $mail_stmt->close();
    }

    $stmt->close();
    $conn->close();
}
catch (Exception $e) {
    echo "{\"status\": \"fail\", \"message\": " . $e . "}";
}
?>