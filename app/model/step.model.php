<?php

    class Step extends Model {
        
        protected $table = 'steps';

        public function getNextPosition() {
            $sql = 'SELECT max(position)+1 as nextpos from `' . $this->table . '`';
            $stmt = $this->database->prepare($sql);
            
            $stmt->execute();
            
            return $stmt->fetchColumn();
        }
        
    }
    
