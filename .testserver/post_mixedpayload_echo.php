<?php

header("content-type: application/json");

echo json_encode([
    "fizz" => $_GET["fizz"] ?? "",
    "foo" => $_POST["foo"] ?? ""
]);
