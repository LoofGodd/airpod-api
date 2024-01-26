<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
require __DIR__ ."/services/DB.php";

$url_container = parse_url($_SERVER['REQUEST_URI']);
$path = $url_container['path'];
$url_param_string= isset($url_container['query']) ? $url_container['query'] : "" ;
parse_str($url_param_string,$url_params);

$db = new DB();

$db_connected = $db->connect();
$routing = [
    "/api/airpod" => "controllers/airpods.controller.php",
    "/api/auth" => "controllers/auth.controller.php",
    "/api/cart" => "controllers/cart.controller.php",
];

if (array_key_exists($path, $routing)) {
    require __DIR__ . "/". $routing[$path];
} else {
    echo "You are not suppose to be here";

}
