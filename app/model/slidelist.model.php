<?php

    class Slidelist extends Model{
        
        protected $table = 'slide_list';
        
        
        public function getList(){
            
            $sql = 'SELECT *, CONCAT_WS(".", `step`, `position`) AS `indexer` FROM `' . $this->table . '` ORDER BY `step` ASC, `position` ASC';
            $stmt = $this->database->prepare($sql);
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_OBJ);
        }
        
        public function listed(){
            $slidelist = self::getList();
            $slideIndex = array();
            foreach($slidelist as $s){
                $slideIndex[$s->step][] = $s->position;
                $slideIndex['fullIndex'][] = $s->step . '.' . $s->position;
            }
            return $slideIndex;
            
        }
        
        public function getSlide($step, $position){
        
            $sql = 'SELECT * FROM `' . $this->table . '` AS `s`
                    WHERE
                        `s`.`step` = :step AND
                        `s`.`position` = :position 
                    ORDER BY `s`.`step` ASC, `s`.`position` ASC';
            
            
            $stmt = $this->database->prepare($sql);
            
            $stmt->bindParam(':position', $position, PDO::PARAM_INT);
            $stmt->bindParam(':step', $step, PDO::PARAM_INT);
            $q = $stmt->execute();
            
            if(!$q){
                new Error(601, 'Could not execute query. (slidelist.model.php, 42)');
                return false;
            }
            else {
                return $stmt->fetch(PDO::FETCH_OBJ);
            }        
        }
        
        public function getNextPosition($step) {
            // get next available position for this step
            $sql = 'SELECT max(position) from `' . $this->table . '` where step = :step';
            $stmt = $this->database->prepare($sql);
            
            $stmt->bindParam(':step', $step, PDO::PARAM_INT);
            
            $q = $stmt->execute();
            if(!$q){
                new Error(601, 'Could not execute query. (slidelist.model.php, 62)');
                return false;
            }
            else {
                $ret = $stmt->fetchColumn(0);
                if (is_null($ret)) {
                    return 1;
                } else {
                    return $ret + 1;
                }
            }
        }
        
        public function shiftPosition($step, $after) {
            $sql = 'update `' . $this->table . '` set position = position - 1 where step = :step and position > :position';
            $stmt = $this->database->prepare($sql);
            
            $stmt->bindParam(':step', $step, PDO::PARAM_INT);
            $stmt->bindParam(':position', $after, PDO::PARAM_INT);
            
            $q = $stmt->execute();
            if(!$q){
                new Error(601, 'Could not execute query. (slidelist.model.php, 62)');
                return false;
            }            
            
        }
        
        public function getByPosition($step, $pos) {
            $matches = $this->find(array('step' => $step, 'position' => $pos));
            if (count($matches) == 0) {
                error_log("Not found: $step, $pos");
                return null;
            }
            return $matches[0];
        }

        public function swapPosition($slide, $adj) {
            error_log("Swapping: {$slide->step} {$adj}");
            $other = $this->getByPosition($slide->step, $slide->position +$adj);
            $opos = $other->position;
            $other->position = $slide->position;
            $this->update((array)$other, $other->idslide_list);

            $slide->position = $slide->position + $adj;
            $this->update((array)$slide, $slide->idslide_list);
        }
        
        public function getIndex(){
            $sql = 'SELECT * FROM `view_slide_index`';
            $stmt = $this->database->prepare($sql);
            $q = $stmt->execute();
            
            if(!$q){
                new Error(601, 'Could not execute query. (slidelist.model.php, 59)');
                return false;
            }
            else {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }        
        }
        
    }
    
