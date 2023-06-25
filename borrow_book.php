<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
require('./configuration/config.php');

if(isset($_GET['fingerprint'])){
        $fingerprint = $_GET['fingerprint'];
        $book = $_GET['book'];
        $book_barcode = $_GET['book_barcode'];
        $stmt = $conn->prepare("SELECT * FROM user WHERE fingerprint='$fingerprint'");
        $stmt->execute();
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $data = $stmt->fetchAll();
        if(count($data)>0){
           $userId = $data[0]->id;
        }
        $today = date("Y-m-d H:i:s");  
        $date = strtotime($today);
        $date = strtotime("+7 day", $date);
        $returnDate =  date('M d, Y', $date);
    try{
        $userId = $_GET['user_id'];
        $bookId = $_GET['book'];
        $bookBarcode = $_GET['book_barcode'];
        $sql = "INSERT INTO `book_borrow` (`id`, `user_id`, `book_id`, `borrow_time`, `return_time`, `book_barcode`) VALUES (NULL,'$userId','$book','$today','$returnDate','$book_barcode')";
        $conn->exec($sql);
    } catch(PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
      }
}

if(isset($_GET['value'])){
    try{
        $stmt = $conn->prepare("SELECT * FROM book_borrow WHERE returned=0");
        $stmt->execute();
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $data = $stmt->fetchAll();
        $json = json_encode($data, JSON_PRETTY_PRINT);
        echo $json;
        } catch(PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
      }
}


if(isset($_GET['returned'])){
    try{
        $stmt = $conn->prepare("SELECT * FROM book_borrow WHERE returned=1");
        $stmt->execute();
        $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $data = $stmt->fetchAll();
        $json = json_encode($data, JSON_PRETTY_PRINT);
        echo $json;
        } catch(PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
      }
}

if(isset($_GET['userDelete'])){
    try{
        $id = $_GET['userDelete'];
        $sql = "UPDATE book_borrow SET returned=1 WHERE id='$id'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        echo "Book Returned";
        } catch(PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
      }
}


