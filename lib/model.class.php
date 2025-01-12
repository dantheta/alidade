<?php

    /** Main Model Class
     * exposes the $database var (PDO Object)
     * to model classes that extend it
     * */

    class Model implements ModelInterface {
        
        protected $database;
        protected $table;
        protected $dates = false;
        
        public function __construct() {
            if(!$this->database){
                
                $dns = DBTYPE . ':dbname=' . DBNAME . ';host=' . DBHOST . ';charset=utf8';
                
                $this->database = new PDO($dns, DBUSER, DBPASS);
                if (is_object($this->database)) {
                    $this->database->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                    return $this->database;
                }
                else {
                    return false;
                }
            }
        }
        
        public function find( $params ){
            $sql = 'SELECT * FROM ' . $this->table . ' WHERE ';
            $clauses = array();
            if(!empty($params)){ 
                foreach($params as $field => $value) {
                    $clauses[] = $field . ' = :' . $field;
                }
                $sql .= implode(' AND ', $clauses);
            }
            
            $stmt = $this->database->prepare($sql);
            
            if(!empty($params)){ 
                foreach($params as $field => &$value){
                    $b = $stmt->bindParam(':'.$field, $value);
                    if(!$b && SYSTEM_STATUS == 'development'){
                        dbga($stmt->errorInfo());
                    }
                }
            }
            $q = $stmt->execute();
            
            if(!$q){
                new ErrorMsg(601, 'Could not execute query. (model.class.php, 46)');
                return false;
            }
            else {
                return $stmt->fetchAll(PDO::FETCH_OBJ);
            }
        }
        
        public function findOne($id){
            
            $sql = 'SELECT * FROM `' . $this->table . '` WHERE `id' . $this->table . '` = :id';
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $q = $stmt->execute();
            
            if(!$q){
                new ErrorMsg(601, 'Could not execute query. (model.class.php, 61)');
                return false;
            }
            else {
                return $stmt->fetch(PDO::FETCH_OBJ);
            }
            
        }
        
        public function findAll($_order=null){
            $sql = 'SELECT * FROM ' . $this->table;
            if ($_order) {
                $sql .= " order by `$_order`";
            }
            $stmt = $this->database->prepare($sql);
            $q = $stmt->execute();
            
            if(!$q){
                new ErrorMsg(601, 'Could not execute query. (model.class.php, 76)');
                return false;
            }
            else {
                return $stmt->fetchAll(PDO::FETCH_OBJ);
            }
            
        }
        
        public function create($data){
            
            array_filter($data); // remove empty entries. 
            if($this->dates == true){
                $data['created_at'] = date('Y-m-d H:i:s', time() );
            }
            $fields = array_keys($data);
            $holders = array();
            foreach($fields as $i => $field){
                $fields[$i] = '`' . $field . '`';
                $holders[]  = ':' . $field;
            }
            
            $sql = 'INSERT INTO `' . $this->table . '`(' . implode(', ', $fields) .  ') VALUES (' . implode(', ', $holders) . ')';
            
            $stmt = $this->database->prepare($sql);
            
            if(!$stmt && SYSTEM_STATUS == 'development'){
                dbga($this->database->errorInfo());   
            }
            
            foreach($data as $field => &$value){
                $stmt->bindParam(':'.$field, $value, PDO::PARAM_STR);    
            }
            
            $q = $stmt->execute();
            
            if(!$q && SYSTEM_STATUS == 'development'){
                dbga($stmt->errorInfo());
                $response = false;
            }
            else {
                $response = $this->database->lastInsertId();
            }
            
            return $response;
            
        }
        public function update($data, $id){
            if(!filter_var($id, FILTER_VALIDATE_INT)){
                new ErrorMsg(666, 'Invalid Update Parameter. (model.class.php, 130)');
                return false;
            }
            $fields = array_keys($data);
            $holders = array();
            foreach($fields as $i => $field){
                $fields[$i] = '`' . $field . '` = :' . $field;
            }
            
            $sql = 'UPDATE `' . $this->table . '` SET ' . implode(', ', $fields) . ' WHERE `id' . $this->table . '` = :id';
            
            $stmt = $this->database->prepare($sql);
            if(!$stmt && SYSTEM_STATUS == 'development'){
                dsql($sql . ' (model.class.php, 142)');
                dbga($this->database->errorInfo());   
            }
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            foreach($data as $field => &$value){
                if(empty($value) || is_null($value)) { $value == 'NULL'; }
                $g = $stmt->bindParam(':' . $field, $value);
            }
            
            $q = $stmt->execute();
            
            if(!$q && SYSTEM_STATUS == 'development'){
                dbga($stmt->errorInfo());
                $response = false;
            }
            
            else {
                $response = true;
            }
            
            return $response;
        }
        
        public function delete($id){
            if(!is_numeric($id)){
                new ErrorMsg(620, 'Invalid parameter. (model.class.php, 168)');
                return false;
                
            }
            else {
                $sql = 'DELETE FROM `' . $this->table . '` WHERE `id' . $this->table . '` = :id';
                $stmt = $this->database->prepare($sql);
                if(!$stmt && SYSTEM_STATUS == 'development'){
                    dbga($this->database->errorInfo());   
                }
                else {
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    
                    $q = $stmt->execute();
                    if(!$q && SYSTEM_STATUS == 'development'){
                        $err = $stmt->errorInfo();
                        
                        new ErrorMsg(601, 'Could not execute query. ' . $err[2] . ' (model.class.php, 183)');
                        return false;
                    }
                    else {
                        return true;
                    }
                }
            }
        }

        public function deleteMany( $params ){
            $sql = 'DELETE FROM ' . $this->table . ' WHERE ';
            $clauses = array();
            if(!empty($params)){ 
                foreach($params as $field => $value) {
                    $clauses[] = $field . ' = :' . $field;
                }
                $sql .= implode(' AND ', $clauses);
            }
            
            $stmt = $this->database->prepare($sql);
            
            if(!empty($params)){ 
                foreach($params as $field => &$value){
                    $b = $stmt->bindParam(':'.$field, $value);
                    if(!$b && SYSTEM_STATUS == 'development'){
                        dbga($stmt->errorInfo());
                    }
                }
            }
            $q = $stmt->execute();
            
            if(!$q){
                new ErrorMsg(601, 'Could not execute query. (model.class.php, 46)');
                return false;
            }
            else {
                return true;
            }
        }
        
        public function deleteAll() {
            $sql = 'DELETE FROM  `' . $this->table .'`';
            $stmt = $this->database->prepare($sql);
            $stmt->execute();
        }
        
        public function howMany($params = null){
            if(empty($params)){
                return count(self::findAll());
            }
            else {
                return count(self::find($params));
            }
        }
        
       
        
    }
    
    
