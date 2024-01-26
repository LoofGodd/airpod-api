<?php
require __DIR__ . "/../services/auth.service.php";

$http_method = $_SERVER['REQUEST_METHOD'];
$auth = new Auth($db_connected);

$body = file_get_contents("php://input");
$url_params = json_decode($body, true);
switch ($http_method) {
    case 'POST':
        $array_action = ['login', 'logout', 'setusername', 'register'];
        if (!isset($url_params['action'])) {
            echo json_encode(['message' => 'no action, but want to get thing.', 'status'=> false]);
            break;
        }
        if(!in_array(strtolower($url_params['action']), $array_action)){
            echo json_encode(['message' => 'wrong method but want to get things', 'status' => false]);
            break;
        }


        if (strtolower($url_params['action']) == 'login') {
            $result = $auth->login();
        } else if (strtolower($url_params['action']) == 'setusername') {
            $result = $auth->updateUsername($url_params['username']);
        } else if (strtolower($url_params['action']) == 'register') {
            $result = $auth->register($url_params['email'], $url_params['password']);
        }
        else{
             $result = $auth->logout();
        }
        echo $result;
        break;

    default:
        echo json_encode(['message'=> 'wrong method','status'=> false]);
        break;
}
