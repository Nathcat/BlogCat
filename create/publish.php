<?php 
header("Access-Control-Allow-Origin: https://blog.nathcat.net");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");
header("Accept: application/json");

include("../start-session.php");
$content = json_decode(file_get_contents("php://input"), true);

try {
    mysqli_report(MYSQLI_REPORT_ALL);
    $conn = new mysqli("localhost:3306", "blog", "", "BlogCat");
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