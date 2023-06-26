<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
require __DIR__ . "../../../config/config.php";

/**
 * Register Method
 */

if (isset($_POST['register']) && $_POST['register'] == true) {
  try {
    $firstName = $_POST['first_name'] ?? null;
    $lastName = $_POST['last_name'] ?? null;
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    $student_id = $_POST['student_id'] ?? null;
    $fingerprint = $_POST['fingerprint'] ?? null;
    $semester = $_POST['semester'] ?? null;
    $department = $_POST['department'] ?? null;

    if (is_null($firstName) || is_null($lastName) || is_null($email) || is_null($password) || is_null($student_id) || is_null($semester) || is_null($department)) {
      http_response_code(400);
      echo "BAD Request";
    } else {
      $sql = "INSERT INTO `user` (`first_name`, `last_name`, `email`, `password`, `student_id`, `fingerprint`, `semester`, `department`) 
          VALUES (:first_name, :last_name, :email, :pass, :student_id, :fingerprint, :semester, :department)";
      $stmt = $conn->prepare($sql);

      $stmt->bindParam(':first_name', $firstName, PDO::PARAM_STR);
      $stmt->bindParam(':last_name', $lastName, PDO::PARAM_STR);
      $stmt->bindParam(':email', $email, PDO::PARAM_STR);
      $stmt->bindParam(':pass', $password, PDO::PARAM_STR);
      $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
      $stmt->bindParam(':fingerprint', $fingerprint, PDO::PARAM_STR);
      $stmt->bindParam(':semester', $semester, PDO::PARAM_INT);
      $stmt->bindParam(':department', $department, PDO::PARAM_STR);

      $stmt->execute();

      if ($stmt->rowCount() > 0) {
        echo "Successfully registered!"; // Display success message
      } else {
        echo "Registration Failed"; // Handle insertion failure
      }
    }
  } catch (PDOException $e) {
    echo $sql . "<br>" . $e->getMessage();
  }
}
