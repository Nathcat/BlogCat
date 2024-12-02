<?php 
header("Access-Control-Allow-Origin: https://blog.nathcat.net");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");
header("Accept: application/json");

include("../start-session.php");
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
    $conn->close();
}
catch (Exception $e) {
    echo "{\"status\": \"fail\", \"message\": " . $e . "}";
}
?>