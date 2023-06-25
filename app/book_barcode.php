<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
require __DIR__ . "../../config/config.php";

if (isset($_POST['book_id'])) {
  try {
    $bookId = $_POST['book_id'];
    $barcode = $_POST['barcode'];
    $encryption = $_POST['encryption'];
    $sql = "INSERT INTO `book_barcode` (`id`, `book_id`, `barcode`, `encryption`) VALUES (NULL,'$bookId','$barcode','$encryption')";
    $conn->exec($sql);
  } catch (PDOException $e) {
    echo $sql . "<br>" . $e->getMessage();
  }
}

if (isset($_GET['value'])) {
  try {
    $stmt = $conn->prepare("SELECT * FROM book_barcode");
    $stmt->execute();
    $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $data = $stmt->fetchAll();
    $json = json_encode($data, JSON_PRETTY_PRINT);
    echo $json;
  } catch (PDOException $e) {
    echo $sql . "<br>" . $e->getMessage();
  }
}
