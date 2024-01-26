<?php

    namespace services;

    header('Content-Type: application/json');


    class Airpod{

        private int $limit;
        private $conn = null;
        private $array_data = ['id', 'name', 'price', 'description', 'feature', 'category_id', 'qty', 'image', 'rating'];


        public function __construct($db){
            global $url_params;
            $this->limit = isset($url_params['limit']) ? $url_params['limit'] : 20;
            $this->conn = $db;

        }


        private function correctColumnAndValue($data = []){
            $arr_columns = [];
            $arr_values_bind = [];
            foreach ($data as $key => $value) {
                $arr_columns[] = $key;
                $arr_values_bind[] = ":" . $key;
            }
            $columns = "(" . implode(", ", $arr_columns) . ")";
            return [$columns,  $arr_values_bind];
        }

        private function getAllID(){
            $query = 'SELECT id FROM airpod';
            $stmt = $this->conn->prepare($query);

            if ($stmt->execute()) {
                if ($stmt->rowCount() > 0) {
                    $ids = [];
                    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                        $ids[] = $row['id'];
                    }
                    return $ids;
                }
                return false;
            }
            return false;
        }
        public function getAirpods(){
            $query = 'SELECT * , airpod.id as id, category.id as category_id, category.name as category_name from airpod LEFT JOIN category ON airpod.category_id = category.id LIMIT :limit';

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $this->limit, \PDO::PARAM_INT);

            if($stmt->execute()){
                $rowCount = $stmt->rowCount();
                if($rowCount){
                    $airpod_data = [];
                    $airpod_data['data'] = [];
                    while($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
                        array_push($airpod_data['data'] ,$row);

                    }
                    return json_encode($airpod_data, JSON_PRETTY_PRINT);

                }
                else {
                    return json_encode(['message'=> 'No Data was founds']);
                }

            }
            echo "Error:" . $stmt->error;
            return json_encode(['message'=> 'No query']);
        }

        public function getAirpod(int $id){
            $query = 'SELECT *, airpod.id as id, category.name as category_name from airpod LEFT JOIN category ON airpod.category_id - category.id WHERE airpod.id = :id';
            global $url_params;
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
            if($stmt->execute()){
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row) {
                    return json_encode($row, JSON_PRETTY_PRINT);
                }
                return json_encode(['message' => 'No Data was found']);
            }
            return json_encode(['message'=> 'Wrong query to Developer']);
        }

        public function addAirpod($data = []){
            //for more valid
            foreach ($this->array_data as $key => $value) {
                if ($key == 'id')
                    continue;
                if(!isset($data[$value])){
                    return json_encode(['message'=> 'No enough data']);
                }
            }

            //query
            [$columns,$arr_binds] = $this->correctColumnAndValue($data);
            $binds = "(" . implode(", ", $arr_binds) . ")";
            $query = 'INSERT INTO airpod ' . $columns . ' VALUE' . $binds;

            $stmt = $this->conn->prepare($query);


            foreach ($data as $key => $value) {
                $v = is_numeric($value) ? +$value : $value;
                $pdoParam = is_numeric($value) ??  is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $stmt->bindValue(':'.$key, $v , $pdoParam);
            }
            if($stmt->execute()){
                return json_encode(['message' => $data['name'] . ' was added into the database']);
            }
            return json_encode(['message'=> 'something went wrong '. $data['name'] . 'was not added into the database']);

        }

        public function deleteAirpod($data = []){
            if(!isset($data['id'])){
                return json_encode(['message' => "PUT PRODUCT ID"]);
            }
            $ids = $this->getAllID();
            if (!in_array($data["id"], $ids)) {
                return json_encode(["message" => "NO ID WAS FOUND IN DATABASE"]);
            }
            $query = "DELETE FROM airpod where id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":id", $data['id'], \PDO::PARAM_INT);
            if ($stmt->execute()) {
                return json_encode(['message'=> "The product that have id ". $data['id'] . " was DELETED."]);
            }
            return json_encode(['message' => "something wrong the your query"]);
        }

        private function setColVal($data){
            $tempData = [];
            foreach ($data as $key => $value) {
                if($key == 'id') continue;
                $tempData[] = $key . " = " . ":" . $key;
            }
            return $tempData;
        }

        public function patchAirpod($data = []){
            if (!isset($data["id"])) {
                return json_encode(['message' => "PUT PRODUCT ID"]);
            }
            foreach ($data as $key => $value) {
                if (!in_array($key, $this->array_data))
                return json_encode(['message' => 'wrong column '. $key . '.']);
        }

        $colVal = $this->setColVal($data);
        $colVal_str = implode(', ', $colVal);

            $query = 'UPDATE airpod SET ' . $colVal_str . ' WHERE id = :id';
            $stmt = $this->conn->prepare($query);

            foreach ($data as $key => $value) {
                $pdoParam = is_numeric($value) ??  is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $stmt->bindValue(':'.$key, $value, $pdoParam);
            }
            if($stmt->execute()){
                return json_encode(['message' => "Product have id " . $data['id'] . " have updated"]);
            }
            return json_encode(['message' => "Wrong query"]);
        }

    }
