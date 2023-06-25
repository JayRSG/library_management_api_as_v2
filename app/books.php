<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
require __DIR__ . "../../config/config.php";

if (isset($_GET['name'])) {
  try {
    $name = $_GET['name'];
    $author = $_GET['author'];
    $quantity = $_GET['quantity'];
    $price = $_GET['price'];
    $rfid = $_GET['rfid'];
    $description = $_GET['description'];
    $sql = "INSERT INTO `book` (`id`, `name`, `author`, `quantity`, `price`, `description`, `rfid`) VALUES (NULL,'$name','$author','$quantity','$price','$description','$rfid')";
    $conn->exec($sql);
  } catch (PDOException $e) {
    echo $sql . "<br>" . $e->getMessage();
  }
}

if (isset($_GET['value'])) {
  try {
    $stmt = $conn->prepare("SELECT * FROM book");
    $stmt->execute();
    $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $data = $stmt->fetchAll();
    $json = json_encode($data, JSON_PRETTY_PRINT);
    echo $json;
  } catch (PDOException $e) {
    echo $sql . "<br>" . $e->getMessage();
  }
}



if (isset($_GET['userDelete'])) {
  try {
    $id = $_GET['userDelete'];
    $sql = "DELETE FROM book WHERE id='$id'";
    $conn->exec($sql);
    echo "Book deleted";
  } catch (PDOException $e) {
    echo $sql . "<br>" . $e->getMessage();
  }
}
