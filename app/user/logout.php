<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

if (isset($_POST['logout']) && $_POST['logout'] == true) {
  // logging out
  session_destroy();
  http_response_code(204);
  echo "Logged out";
}
