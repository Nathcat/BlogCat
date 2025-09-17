<?php
include("start-session.php");

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");

if (!array_key_exists("user", $_SESSION)) {
    die("{\"status\": \"fail\", \"message\": \"Not logged in.\"}");
}

$request = json_decode(file_get_contents("php://input"), true);

$conn = new mysqli("localhost:3306", "blog", "", "BlogCat");
if ($conn->connect_error) {
    die(json_encode(["status" => "fail", "message" => "Failed to connect to database"]));
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR);

    $stmt = $conn->prepare("SELECT * FROM Posts WHERE id = ? AND author = ?");
    $stmt->bind_param("ii", $request["id"], $_SESSION["user"]["id"]);
    $stmt->execute(); $res = $stmt->get_result();

    if ($p = $res->fetch_assoc()) {
        $path = $p["filePath"];
        unlink("posts/$path");
        $stmt = $conn->prepare("DELETE FROM Posts WHERE id = ? AND author = ?");
        $stmt->bind_param("ii", $request["id"], $_SESSION["user"]["id"]);
        $stmt->execute();
    }
}
catch (Exception $e) {
    $conn->close();
    die(json_encode(["status" => "fail", "message" => "$e"]));
}

$stmt->close();
$conn->close();
echo json_encode([
    "status" => "success"
]);
?>
