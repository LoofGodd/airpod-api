<?php
class Auth{
    private $conn = null;
    public function __construct($db){
        $this->conn = $db;
    }

    public function login(){
        global $url_params;
        $query = 'SELECT * FROM user WHERE email = :email';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $url_params['email'], PDO::PARAM_STR);
        if($stmt->execute()){
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if($result and password_verify($url_params['password'], $result['password'])){
                return json_encode(['message' => 'Login successfully','userId' => $result['id'], 'status' => true]);
            }
            return json_encode(['message' => 'Invalid Email or password', 'status' => false]);
        }
    }

    public function updateUsername($username){
        if (!isset($username) or empty($username)) {
            return json_encode(['message'=> 'no username','status'=> false]);
        }
        $query = "UPDATE user SET username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        if ($stmt->execute()) {
            return json_encode(["message"=> "Username is set","status"=> true]);
        }
        return json_encode(["message"=> "wrong username","status"=> false]);
    }
    public function logout(){
        return json_encode(['message'=> 'logout successfully', 'status'=> true]);
    }

    public function register($email, $pass){
        if (!isset($email) or empty($email) or empty($pass) and !isset($pass)) {
            return json_encode(['message'=> 'we do not take that','status'=> false]);
        }
        $query = "INSERT user (email, password, username) VALUES (:email, :password, :username)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":username", $email, PDO::PARAM_STR);
        $stmt->bindParam(":password", $pass, PDO::PARAM_STR);
        $pass = password_hash($pass, PASSWORD_DEFAULT);

        if ($stmt->execute()) {
            return json_encode(["message"=> "Register successfully","status"=> true]);
        }
        return json_encode(["message"=> "Email already exist","status"=> false]);
    }
}
