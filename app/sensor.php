<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

if (isset($_GET['current'])) {
  try {
    $stmt = $conn->prepare("SELECT * FROM sensor");
    $stmt->execute();
    $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $data = $stmt->fetchAll();
    $json = json_encode($data, JSON_PRETTY_PRINT);
    echo $json;
  } catch (PDOException $e) {
    echo $sql . "<br>" . $e->getMessage();
  }
}

if (isset($_GET['update'])) {
  try {
    $id = $_GET['students_id'];
    $fingerprint = $_GET['fingerprint'];
    $sql = "UPDATE sensor SET students_id='$id' , fingerprint='$fingerprint'  WHERE id=1";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    echo "Id Updated";
  } catch (PDOException $e) {
    echo $sql . "<br>" . $e->getMessage();
  }
}
