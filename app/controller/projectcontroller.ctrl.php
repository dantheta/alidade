<?php

    class Projectcontroller extends Controller {

        var $multiSlides = array( '3.2', '4.2', '4.5' );

        public function start(){
            $Auth = new Auth($url);
            if(!$Auth->isLoggedIn()){
              // setup a disposable user that we can later just edit and amend for our needs
              header('Location: /user/disposable');
            }

            else {

                $user = $Auth->getProfile();
                $this->set('userRole', $user->role);

                if(isset($_POST['title'])){


                    $ProjectHash = md5( $_SESSION[APPNAME]['USR'] . time() . $_SESSION[APPNAME][SESSIONKEY]);

                    $data['user'] = $user->id;
                    $data['hash'] = $ProjectHash;
                    $data['title'] = $_POST['title'];

                    $idproject = $this->Project->create($data);

                    if(is_numeric($idproject)) {

                        $_SESSION['project'] = $idproject;
                        header('Location: /project/slide/1.0');

                    }
                }
                else {
                    $this->set('title', 'Start a new project');
                    $this->set('page', 'start');
                }
            }
        }

        /** urls are in the form of /project/slide/1.2 **/
        public function slide($cur){

            $Auth = new Auth($url);
            if(!$Auth->isLoggedIn()){
                //header('Location: /user/login');
                header('Location: /user/disposable');
            }

            else {

                $user = $Auth->getProfile();
                $this->set('user', $user);
                $this->set('userRole', $user->role);
                $this->set('multiSlides', $this->multiSlides);
                $this->set('inProcess', true);


                if(!isset($_SESSION['plan']) || $cur === '1.1'){
                    $_SESSION['plan'] = array();
                    $project = $_SESSION['project'];
                }

                $position = explode('.', $cur);

                $step_no    = (int)$position[0];
                $slide_no   = (int)$position[1];

                $Step = new Step;
                $Slide = new Slide;
                $Slidelist = new Slidelist;

                $step = $Step->getByPosition($step_no);
                $slidelist = $Slidelist->getList();

                $slideIndex = array();
                foreach($slidelist as $s){
                    $slideIndex[$s->step][] = $s->position;
                    $slideIndex['fullIndex'][] = $s->step . '.' . $s->position;
                }

                $projectSlides = $Slide->findProjectSlides($project);

                $this->set('step_number', $step_no);
                $this->set('slide_number', $slide_no);
                $this->set('slidelist', $slidelist);
                $this->set('slideindex', $slideIndex);

                if(!empty($_SESSION['project'])) {
                    $loaded_project = $this->Project->findOne($_SESSION['project']);
                    $this->set('projecthash', $loaded_project->hash);
                    $idProject=$loaded_project->idprojects;
                }


                $slide = $Slidelist->getSlide(            
                                            $step->idsteps,
                                            $slide_no
                                            );


                $nextSlide = $slideIndex['fullIndex'][array_search($cur, $slideIndex['fullIndex'], true) + 1];
                $prevSlide = $slideIndex['fullIndex'][array_search($cur, $slideIndex['fullIndex'], true) - 1];


                $this->set('nextSlide', $nextSlide);
                $this->set('prevSlide', $prevSlide);
                $this->set('currentSlide', $cur);

                $this->set('slide', $slide);
                $this->set('contents', $slide->description);

                //check if we have a hash for a project
                if($_GET['p']){
                    $hash = $_GET['p'];
                    $this->set('hash', $hash);
                    $project = $this->Project->find(array('hash' => $hash));

                    if(!empty($project) && is_object($project[0])) {
                        $_SESSION['project'] = $project[0]->idprojects;
                        $idProject = $project[0]->idprojects;
                    }

                    $slidecontent = $Slide->findSlide($project[0]->idprojects,
                                                      $slide->idslide_list);
                    //$slidecontent[0]->full_project = $project[0];
                    if($slidecontent){
                        $original = json_decode($slidecontent->answer, TRUE);
                        $this->set('original', $original);
                        $this->set('extra', $slidecontent->extra);
                        $this->set('slidecontent', $slidecontent);
                    }

                    if(isset($_GET['back'])){
                        $this->set('back', true);
                    }
                }
                /** access the selection from other slides as well **/

                if(isset($_POST) && !empty($_POST)){
                    $_SESSION['plan'][$_POST['current_slide']] = $_POST;

                    $slidedata = array();
                    $slidedata['project'] = $_SESSION['project'];
                    $slidedata['slide'] = $_POST['idslide_list'];
                    $slidedata['status'] = 2;
                    $slidedata['choice'] = (!empty($_POST['choice']) ? $_POST['choice'] : null);
                    $slidedata['extra'] = (!empty($_POST['extra']) ? $_POST['extra'] : null);
                    unset($_POST['extra']);
                    $slidedata['answer'] = json_encode($_POST, TRUE);

                    // creating or updating ?
                    $toUpdate = $Slide->findOne($_POST['slide_update']);
                    if ($toUpdate) {
                        $Slide->update($slidedata, $toUpdate->idslides);
                    } else {

                        $r = $Slide->create($slidedata);
                    }
                    
                }

                
                $projectSlideIndex = $this->Project->getIndex($idProject);

                // rearraange the index for our purposes
                foreach($projectSlideIndex as $p){
                    $projectIndex[$p['step']][] = $p['slideStep'];
                }
                $this->set('projectIndex', $projectIndex);

                $menu = array();
                foreach($slidelist as $slide){
                    $menu[$slide->indexer] = $slide->title;
                }
                $this->set('slideMenu', $menu);



            }
        }

    }
