<?php 
header("Access-Control-Allow-Origin: https://blog.nathcat.net");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

session_name("AuthCat-SSO");
//session_set_cookie_params(0, "/", ".nathcat.net");
session_start();

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in\"}");
}


if (array_key_exists("id", $_GET)) {
    $conn = new mysqli("localhost:3306", "blog", "", "BlogCat");
    $stmt = $conn->prepare("INSERT INTO followers (id, `follows`) VALUES (?, ?);");
    $stmt->bind_param("ii", $_SESSION["user"]["id"], $_GET["id"]);
     
    if (!$stmt->execute()) {
        $unfollow = $conn->prepare("DELETE FROM followers WHERE id = ? AND `follows` = ?");
        $unfollow->bind_param("ii", $_SESSION["user"]["id"], $_GET["id"]);
        $unfollow->execute(); $unfollow->close();
    }

    $stmt->close(); $conn->close();
    

    echo "{\"status\": \"success\"}";

}
else {
    die("{\"status\": \"fail\", \"message\": \"You must specify who you wish to follow.\"}");
}
?>