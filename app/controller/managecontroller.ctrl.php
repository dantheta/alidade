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
            $this->set('steps', $Step->findAll());
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
            $this->set('mdEditor', true);
            $css = array('/components/summernote/dist/summernote.css');
           $js = array('/components/summernote/dist/summernote.js'); // hacked version
            $this->set('js', $js);
            $this->set('css', $css);
            
            if(isset($_POST) && !empty($_POST)) {
                $slide = $SlideList->getSlide($step, $position);
                
                $update = $SlideList->update($_POST, $slide->idslide_list);    
            }
            
            $SlideList = new Slidelist;
            $slide = $SlideList->getSlide($step, $position);
            
            $this->set('slide', $slide);
        }
        
        
        /** manage user profiles **/
        public function user($user){
            
        }
    }
    
