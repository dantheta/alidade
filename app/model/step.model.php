<?php

    class Step extends Model {
        
        protected $table = 'steps';

        public function getNextPosition() {
            $sql = 'SELECT max(position)+1 as nextpos from `' . $this->table . '`';
            $stmt = $this->database->prepare($sql);
            
            $stmt->execute();
            
            return $stmt->fetchColumn();
        }

        public function getByPosition($pos) {
            $matches = $this->find(array('position' => $pos));
            if (count($matches) == 0) {
                return null;
            }
            return $matches[0];
        }

        public function swapPosition($step, $adj) {
            $other = $this->getByPosition($step->position +$adj);
            $opos = $other->position;
            $other->position = $step->position;
            $this->update((array)$other, $other->idsteps);

            $step->position = $step->position + $adj;
            $this->update((array)$step, $step->idsteps);
        }
        
    }
    
