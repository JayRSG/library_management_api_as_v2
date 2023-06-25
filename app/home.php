<?php

echo "WELCOME TO LIBRARY MANAGEMENT API v2.0";

echo "<BR/><BR/>";

echo "<pre>";
echo json_encode([
  "data" => [
    "Author" => "Arafat and Sahad",
    "Project Name" => "Library Management System API",
    "Authored Date" => "June 2023",
    "Institute" => "Chittagong Independent University",
  ]
], JSON_PRETTY_PRINT);

echo "</pre>";
