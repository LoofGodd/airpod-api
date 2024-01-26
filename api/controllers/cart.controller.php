<?php
require __DIR__ . "/../services/cart.service.php";
$method = $_SERVER['REQUEST_METHOD'];
$cart = new Cart($db_connected);

$body_str = file_get_contents("php://input");
$body = json_decode($body_str, true);
switch ($method) {
    case 'GET':
        //userId, cart, checkout
        if(count($url_params) > 2){
            echo json_encode(['message' => 'Too much query param', 'status' => false]);
            break;
        }
        if(!isset($url_params['userId'])){
            echo json_encode(['message' => 'No userID']);
            break;
        }

        if(isset($url_params['cart']) and $url_params['cart'] == 'true'){
            $result = $cart->get_products_cart_user($url_params['userId']);
            echo $result;
            break;
        }
        if(isset($url_params['checkout']) and $url_params['checkout'] = 'true'){
            $result = $cart->get_products_checkout_user($url_params['userId']);
            echo $result;
            break;
        }
        echo json_encode(['message'=> 'Wrong query param']);
        break;
    case 'POST':
        $result = $cart->add_product_in_cart_user($body);
        echo $result;
        break;
    case 'PATCH':
        $result = $cart->patch_product_in_cart_user($body);
        echo $result;
        break;
    case 'DELETE':
        $result = $cart->delete_product_cart_user($body);
        echo $result;
        break;
    default:
        echo json_encode(['message' => 'wrong method']);
        break;
}
