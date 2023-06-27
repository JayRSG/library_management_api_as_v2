<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

require __DIR__ . "../../../config/config.php";

if (!auth()) {
  http_response_code(400);
  echo "Not logged in";
  return;
} else {
  if (!checkPostMethod()) {
    return;
  }
}

if (isset($_POST['logout']) && $_POST['logout'] == true) {
  // logging out
  session_destroy();
  http_response_code(204);
  echo "Logged out";
}
