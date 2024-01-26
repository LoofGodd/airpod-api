<?php
class Cart{
    private $conn = null;
    public function __construct($db){
        $this->conn = $db;
    }

    //for user
    public function get_products_cart_user($id)
    {
        $query = 'SELECT airpod.* FROM cart JOIN airpod ON cart.productId = airpod.id JOIN user ON cart.userId = user.id WHERE user.id = :id AND cart.status = 0';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        if($stmt->execute()){
            $arr_data = [];
            $arr_data['data'] = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $arr_data['data'][] = $row;
            }
            return json_encode($arr_data, JSON_PRETTY_PRINT);

        }
        return json_encode(['message' => 'wrong query', "status" => false]);
    }
    public function get_products_checkout_user($id){
        $query = 'SELECT airpod.* FROM cart JOIN airpod ON cart.productId = airpod.id JOIN user ON cart.userId = user.id WHERE user.id = :id AND cart.status = 1';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        if($stmt->execute()){
            $arr_data = [];
            $arr_data["data"] = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
            {
                $arr_data["data"][] = $row;
            }
            return json_encode($arr_data, JSON_PRETTY_PRINT);
        }
        return json_encode(["message"=> "wrong query","status"=> false]);
    }

    public function delete_product_cart_user($data){
        if ( (!$this->isAvailable($data["id"]) and !$this->isAvailable($data['userId'])) or count($data) > 2 ) {
            return json_encode(['message'=> 'Wrong data','status'=> false]);
        }
        $query = 'DELETE FROM cart WHERE id = :id AND userId = :userId';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $data['id']);
        $stmt->bindParam(':userId', $data['userId']);
        if ($stmt->execute()) {
            $row = $stmt->rowCount();
            if($row)
            return json_encode(['message'=> 'product '. $data['id'] . ' was deleted','status'=> true]);
        }
        return json_encode(['message'=> 'something was wrong with the query','status'=> false]);
    }
    public function patch_product_in_cart_user($data){
        $arr_product_can_edit = ["productId", "status", 'totalProducts'];
        $arr_change_data = [];

        foreach ($data as $key => $value) {
            if ($key == 'userId' or $key == 'id')
                continue;
            if(!in_array($key, $arr_product_can_edit)){
                return json_encode(['message' => 'Invalid data', 'status'=> false ]);
            }
            $arr_change_data[] = $key .' = :'. $key;
        }
        $changeData = implode(',', $arr_change_data);

        $query = 'UPDATE cart SET '. $changeData .' WHERE userId = :userId AND id = :id';

        $stmt = $this->conn->prepare($query);

        foreach ($data as $key => $value) {
            $stmt->bindValue(':'.$key, $value);
        }

        if($stmt->execute()){
            if($stmt->fetch(PDO::FETCH_ASSOC))
            return json_encode(['message'=> 'The product '. $data['id']. ' was patched', 'status'=> true]);
        }
        return json_encode(['message'=> 'something wrong with the query','status'=> false]);
    }

    public function add_product_in_cart_user($data){
        if ( !$this->isAvailable($data["userId"]) and !$this->isAvailable($data['productId']) ) {
            return json_encode(['message'=> 'No enough data','status'=> false]);
        }

        //doing duplicate products
        $products_in_user_cart = $this->get_cart_user($data['userId']);
        foreach ($products_in_user_cart['data'] as $value) {
            if($value['userId'] == $data['userId'] and $value['productId'] == $data['productId'] ){
                $product = $this->get_product_id($data);
                $data['id'] = $product['id'];
                $data['totalProducts'] = $product['totalProducts'] + 1;
                $result =  $this->patch_product_in_cart_user($data);
                return $result;
            }
        }
        //doing insert stuff
        $query = 'INSERT INTO cart (userId, productId) VALUES (:userId, :productId)';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":userId", $data['userId']);
        $stmt->bindParam(":productId", $data['productId']);

        if($stmt->execute()){
            return json_encode(['message'=> 'the product was added to cart','status'=> true]);
        }
        return json_encode(['message' => 'something was wrong with query', 'status' => false]);
    }

    private function get_cart_user($id){
        $query = 'SELECT * from cart WHERE userId = :userId';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $id);

        if ($stmt->execute()) {
            $arr_data = [];
            $arr_data['data'] = [];
            while( $row = $stmt->fetch(PDO::FETCH_ASSOC)){
                $arr_data['data'][] = $row;
            }
            return $arr_data;
        }
        return false;
    }

    private function get_product_id($data){

        $query = 'SELECT * from cart WHERE userId = :userId AND productId = :productId';
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':productId', $data['productId']);
        $stmt->bindParam(':userId', $data['userId']);

        if( $stmt->execute()){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            var_dump($row);
            return $row;
        }
        return false;
    }

    private function isAvailable($data){
        return isset($data) && !empty($data);
    }
}
