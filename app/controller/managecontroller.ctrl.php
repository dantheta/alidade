<?php

    class ManageController extends Controller {
        
        public function __construct($model, $controller, $action){
            parent::__construct($model, $controller, $action);
            $Auth = new Auth($url);
            if(!$Auth->isLoggedIn()){
                header('Location: /user/login');
            }
            else {                
                $user = $Auth->getProfile();
                $this->set('userRole', $user->role);
                if($user->role != 'root'){
                    header('Location: /user/forbidden');
                }   
            }
            
        }
        
        public function index() {
            $Pages = new Page;
            $SlideList = new Slidelist;
            $Step = new Step;
            
            $this->set('title', 'Manage contents of the TSA');
            $this->set('pages', $Pages->findAll());
            $this->set('slides', $SlideList->getList());
            $this->set('steps', $Step->findAll("position"));
        }
        
        
        /** manage "static" pages **/
        public function page($page){
            /** load component css and js -> see /app/view/head.php & /app/view/foot.php **/
            $this->set('mdEditor', true);
            $css = array('/components/summernote/dist/summernote.css');
            $js = array('/components/summernote/dist/summernote.js'); // hacked version
            $this->set('js', $js);
            $this->set('css', $css);
            
            $Pages = new Page;
            
            if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
                /** save data **/
                $data = array();
                $data['title'] = $_POST['title'];
                $data['url'] = $_POST['url'];
                $data['contents'] = $_POST['contents'];
                $id = $_POST['page'];
                $update = $Pages->update($data, $id);
                if($update){
                    $response['success'] = 'Page contents updated';
                }
                else{
                    $response['danger'] = 'Could not update the page.';
                }
                $this->set('response', $response);
            }
            
            $this->set('page', $Pages->findOne($page));
            
            
        }
        
        /* Manage steps */
        public function step($step) {
            $this->set('id', $step);
            $css = array('/components/summernote/dist/summernote.css');
           $js = array('/components/summernote/dist/summernote.js'); // hacked version
            $this->set('js', $js);
            $this->set('css', $css);
            
            $Step = new Step;
            
            if (isset($_POST) && !empty($_POST)) {
                if ($step == "new") {
                    unset($_POST['files']);
                    $_POST['position'] = $Step->getNextPosition();
                    $update = $Step->create($_POST);
                    header("Location: /manage/step/" . $update);
                } else {
                    $stepobj = $Step->findOne($step);
                    $update = $Step->update($_POST, $stepobj->idstep);
                }
            }
            
            if ($step == 'new') {
                $stepobj = new stdClass();
                $stepobj->idstep = 'new';
                $stepobj->title = '';
                $stepobj->description = '';
            } else {
                $stepobj = $Step->findOne($step);
            }
            $this->set('step', $stepobj);
            
        }
        
        
        /** edit slide contents **/
        public function slide($step, $position){
            $Step = new Step();
            $this->set('mdEditor', true);
            $css = array('/components/summernote/dist/summernote.css');
            $js = array('/components/summernote/dist/summernote.js'); // hacked version
            $this->set('js', $js);
            $this->set('css', $css);
            $this->set('steps', $Step->findAll('idsteps'));
            
            if(isset($_POST) && !empty($_POST)) {
                $slide = $SlideList->getSlide($step, $position);
                
                //TODO: load by id, change position if needed
                
                $update = $SlideList->update($_POST, $slide->idslide_list);    
            }
            
            $SlideList = new Slidelist;
            $slide = $SlideList->getSlide($step, $position);
            
            $this->set('slide', $slide);
        }

        public function stepup($step) {
            $Step = new Step;
            $stepobj = $Step->findOne($step);
            $Step->swapPosition($stepobj, -1);
            header("Location: /manage/index");
        }
        public function stepdown($step) {
            $Step = new Step;
            $stepobj = $Step->findOne($step);
            $Step->swapPosition($stepobj, 1);
            header("Location: /manage/index");
        }
        
        
        /** manage user profiles **/
        public function user($user){
            
        }
        
        /* import export feature */
        
        public function import() {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                if ($_FILES['file0']['error'] == 0) {
                    $data = yaml_parse_file($_FILES['file0']['tmp_name']);
                    
                    $SlideList = new SlideList;
                    $SlideList->deleteAll();
                    $Step = new Step;
                    $Step->deleteAll();
                    $Page = new Page;
                    $Page->deleteAll();
                    
                    $result = array();
                    
                    foreach($data['steps'] as $step) {
                        $slides = $step['_slides'];
                        unset($step['_slides']);
                        $Step->create($step);
                        $result[] = "Created step {$step[title]}";
                        foreach($slides as $slide) {
                            $SlideList->create($slide);
                            $result[] = "Created slide {$slide[title]}";
                        }
                    }
                    foreach($data['pages'] as $page) {
                        $Page->create($page);
                        $result[] = "Created page {$page[title]}";
                    }
                    $this->set('result', $result);
                } else {
                    $this->set('result', 'Upload failed');
                }
            }
        }
        
        public function export() {
            $SlideList = new SlideList;
            $Step = new Step;
            $Page = new Page;
            $out = array();
            $steps = array();
            foreach($Step->findAll('position') as $step) {
                $step->_slides = array();
                foreach($SlideList->find(array(step => $step->idsteps)) as $slide) {
                    $step->_slides[] = (array)$slide;
                }
                $steps[] = (array)$step;
            }
            $out['steps'] = $steps;
            $pages = array();
            foreach($Page->findAll('idpages') as $page) {
                $pages[] = (array)$page;
            }
            $out['pages'] = $pages;
            $out['created'] = date('Y-m-d H:m:s');
            
            $this->set('yaml', yaml_emit($out));
        }
    }
    
