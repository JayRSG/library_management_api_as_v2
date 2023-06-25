<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
require('../config/config.php');

if(isset($_GET['emailPost'])){
    try{
        $firstName = $_GET['first_name'];
        $lastName = $_GET['last_name'];
        $email = $_GET['emailPost'];
        $userId = $_GET['student_id'];
        $fingerprint = $_GET['fingerprint'];
        $semester = $_GET['semester'];
        $department = $_GET['department'];
        $sql = "INSERT INTO `user` (`id`, `first_name`, `last_name`, `email`, `student_id`, `fingerprint`, `semester`, `department`) VALUES (NULL,'$firstName','$lastName','$email','$userId','$fingerprint','$semester','$department')";
        $conn->exec($sql);
    } catch(PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
      }
 
}

if(isset($_GET['value'])){
    try{
        $stmt = $conn->prepare("SELECT * FROM user");
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
        $sql = "DELETE FROM user WHERE id='$id'";
        $conn->exec($sql);
        echo "User deleted";
        } catch(PDOException $e) {
        echo $sql . "<br>" . $e->getMessage();
      }
}


