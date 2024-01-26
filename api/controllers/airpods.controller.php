<?php
ini_set('error_reporting', E_ALL); ini_set('display_errors', 1);


require __DIR__.'/../services/airpods.service.php';
use services\Airpod;
$method = $_SERVER['REQUEST_METHOD'];

$airpod = new Airpod($db_connected);
$body = file_get_contents("php://input"); $url_params = json_decode($body, true);

switch ($method) {
    case 'GET':
        $results = null;
        if(isset($url_params['id'])){
            var_dump($url_params['id']);
            $results = $airpod->getAirpod((int)$url_params['id']);
        }else{
        $results = $airpod->getAirpods();
        }
        echo $results;
          break;
    case 'POST':

        $result = $airpod->addAirpod($url_params);
        echo $result;
        break;
    case 'DELETE':
            $result = $airpod->deleteAirpod($url_params);
        echo $result;
        break;
    case "PATCH":
        $result = $airpod->patchAirpod($url_params);
        echo $result;
        break;

    default:
        echo "Wrong Method";
        break;
}
