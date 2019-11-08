<?php

    class PageController extends Controller {

        public function __construct($model, $controller, $action){

            parent::__construct($model, $controller, $action);
            $Auth = new Auth($url);

            if($Auth->isLoggedIn()){
                $user = $Auth->getProfile();
                $this->set('userRole', $user->role);
            }
            
        }

        private function setAction($action) {
            /* Used to update the action when the _default controller method us used */
            $this->action = $action;
            $this->_template->setAction($action);  // set the action on the template object too 

        }

        public function index($url = null){
            $url = (is_null($url) ? 'homepage' : $url);
            $url = filter_var($url, FILTER_SANITIZE_URL);

            $page = $this->Page->find(array('url' => $url));

            $this->set('page', $page[0]->contents);
        }

        public function _default($url=null) {
            $this->setAction("index");
            return $this->index($url);
        }

        public function research(){

        }

        public function six_rules(){ }

        public function home(){
            $url = 'homepage';
            $page = $this->Page->find(array('url' => $url));
            $js = array(
                'https://cdnjs.cloudflare.com/ajax/libs/fullPage.js/2.9.5/jquery.fullpage.min.js'
            );
            $css = array(
                '//cdnjs.cloudflare.com/ajax/libs/fullPage.js/2.9.5/jquery.fullpage.css'
            );
            $this->set('js', $js);
            $this->set('css', $css);
            $this->set('page', $page[0]->contents);
            $this->set('bodyClass', 'homepage');
        }

    }
