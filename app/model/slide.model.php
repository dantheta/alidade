<?php

    class Slide extends Model {
        protected $table = 'slides';
        protected $dates = true;

        public function findProjectSlides($p){

            $sql = 'SELECT * FROM slides_with_step AS `s`
                    WHERE `s`.`project` = :p
                    ORDER BY `s`.`step` asc, `s`.`slide` ASC';


            $stmt = $this->database->prepare($sql);

            $stmt->bindParam(':p', $p, PDO::PARAM_INT);
            $q = $stmt->execute();

            if(!$q){
                new Error(601, 'Could not execute query. (slide.model.php, 20)');
                return false;
            }
            else {
                return $stmt->fetchAll(PDO::FETCH_OBJ);
            }
        }

        public function findSlide($p, $idslide_list){

            $sql = 'SELECT * FROM `' . $this->table . '` AS `s`
                    WHERE `s`.`project` = :p
                    AND `s`.`slide` = :slide
                    ORDER BY  `s`.`slide` ASC';


            $stmt = $this->database->prepare($sql);

            $stmt->bindParam(':p', $p, PDO::PARAM_INT);
            $stmt->bindParam(':slide', $idslide_list, PDO::PARAM_INT);
            $q = $stmt->execute();

            if(!$q){
                new Error(601, 'Could not execute query. (slide.model.php, 20)');
                return false;
            }
            else {
                return $stmt->fetch(PDO::FETCH_OBJ);
            }
        }

        public function projectSlideIndex($project){
            $sql = 'SELECT DISTINCT(CONCAT_WS(".", step, slide)) as indexer, step FROM slides_with_step WHERE project = :id ORDER BY step ASC, slide ASC';
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':id', $project, PDO::PARAM_INT);
            $q = $stmt->execute();

            if(!$q){
                new Error(601, 'Could not execute query. (slide.model.php, 20)');
                return false;
            }
            else {
                return $stmt->fetchAll(PDO::FETCH_OBJ);
            }
        }

        public function findPreviousAnswer($project, $step, $slide){
            $sql.='
            SELECT
                `slides`.*,
                `slide_list`.`title`,
                `slide_list`.`description`,
                `projects`.`hash`,
                `slide_list`.`description` as slide_description
            FROM `slides`
            INNER JOIN `slide_list` ON (`slide_list`.`idslide_list` = `slides`.`slide`)
            INNER JOIN `steps` on (slide_list.step = steps.idsteps)
            INNER JOIN `projects` ON `projects`.`idprojects` = `slides`.`project`
            WHERE
                `projects`.`idprojects` = :project AND 
                `steps`.`position` = :step AND
                `slide_list`.`position` = :slide
            ORDER BY `slides`.`modified_at` DESC
            LIMIT 1';
            $stmt = $this->database->prepare($sql);

            $stmt->bindParam(':project', $project, PDO::PARAM_STR);
            $stmt->bindParam(':step', $step, PDO::PARAM_INT);
            $stmt->bindParam(':slide', $slide, PDO::PARAM_INT);

            $q = $stmt->execute();

            if(!$q){
                new ErrorMsg(601, 'Could not execute query. (slide.model.php, 20)');
                return false;
            }
            else {
                return $stmt->fetch(PDO::FETCH_OBJ);
            }

        }
      public function clean($project){
        $sql = 'DELETE FROM `'.$this->table.'` WHERE `project` = :id';
        $stmt = $this->database->prepare($sql);

        $stmt->bindParam(':id', $project, PDO::PARAM_INT);

        $q = $stmt->execute();

        if(!$q){
            new Error(601, 'Could not execute query. (slide.model.php, 108)');
            return false;
        }
        else {
            return true;
        }
      }
    }
