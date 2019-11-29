<?php
session_start();
error_reporting(E_ALL);
#ini_set('display_errors', 'On');

$noLocal = false;
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/config.php');

if(!(include(ROOT . DS . 'config' . DS . 'local.php'))) {  $noLocal = true; }

require_once(ROOT . DS . 'config' . DS . 'definitions.php');
require_once(ROOT . DS . 'lib' . DS . 'functions.php');
    
if(( isset($_GET['config-check']) && $_GET['config-check'] == 1) || isset($_POST) && !empty($_POST) ){
    
   
    
    /** check PHP version **/
    if(phpversion() < '5.4'){
        $response[] = '<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> The TSA has not been tested on versions of PHP lower than 5.4</div>';
    }
    else {
        $response[] = '<div class="alert alert-success"><i class="fa fa-check"></i> PHP Version is ' . phpversion() . '</div>';
    }
    /** check db connection **/
    $dns = DBTYPE . ':dbname=' . DBNAME . ';host=' . DBHOST . ';charset=utf8';
    $database = new PDO($dns, DBUSER, DBPASS);
    if(is_object($database)){
        $version = $database->getAttribute(PDO::ATTR_SERVER_VERSION);
        $response[] = '<div class="alert alert-success"><i class="fa fa-check"></i> Successfully connected to Database (MySQL version ' . $version . ')</div>';
    }
    else {
        $response[] = '<div class="alert alert-danger"><i class="fa fa-times"></i> Could not establish a connection to the Database! </div>';
    }
    /** check strength of App Key based on length and variations (basically almost useless, but hey, better than IHEARTMUM **/
    $app_key_error = false;
    if(strlen(APPKEY) < 16) {
        $app_key_error = true;
        $response[] = '<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Your App Key should be at least 16 characters long.</div>';
    }
    if(!preg_match('/[a-zA-Z]/',    APPKEY)){
        $app_key_error = true;
        $response[] = '<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Your App Key should contain at least a capital letter.</div>';
    }
    if(!preg_match('/\d/',          APPKEY)){
        $app_key_error = true;
        $response[] = '<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Your App Key should contain at least a number.</div>';
    }

    if(!preg_match('/[^a-zA-Z\d]/', APPKEY)){
        $app_key_error = true;
        $response[] = '<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Your App Key should contain at least a symbol.</div>';
    }
    if(!$app_key_error){
        $response[] = '<div class="alert alert-success"><i class="fa fa-check"></i> Your App Key seems strong enough.</div>';
    }
    
    
    
    if(isset($_POST) && !empty($_POST)){
    
        /** get classes **/
        include(ROOT . DS . 'lib' . DS . 'modelinterface.class.php');
        include(ROOT . DS . 'lib' . DS . 'model.class.php');
        include(ROOT . DS . 'app' . DS . 'model' . DS . 'user.model.php');
        include(ROOT . DS . 'app' . DS . 'model' . DS . 'session.model.php');
        
        $error = false; 
        /** extract data **/
        $pass = trim($_POST['apppwd']);
        $user = trim($_POST['appusername']);
        $mail = filter_var(trim($_POST['appemail']), FILTER_SANITIZE_EMAIL);
        
        /** validate data **/
        if(empty($pass) || strlen($pass) < 8) {
            $error = true;
            $response[] = '<div class="alert alert-danger"><i class="fa fa-times"></i> Your password is too short, 8 characters minimum.</div>';
        }
        
        if(empty($user)){
            $error = true;
            $response[] = '<div class="alert alert-danger"><i class="fa fa-times"></i> Your username cannot be empty.</div>';
        }
        if(empty($mail)){
            $error = true;
            $response[] = '<div class="alert alert-danger"><i class="fa fa-times"></i> Your email cannot be empty.</div>';
        }
        
        if(!filter_var($mail, FILTER_VALIDATE_EMAIL)){
            $error = true;
            $response[] = '<div class="alert alert-danger"><i class="fa fa-times"></i> Please input a <strong>valid</strong> email address.</div>';
        }
        
        /** if data is OK, we can install DB and create ROOT **/
        if($error == false){ 
            /** Install DB **/
            $sql = "
                DROP TABLE IF EXISTS `slides`;
                DROP TABLE IF EXISTS `projects`;
                DROP TABLE IF EXISTS `sessions`;
                DROP TABLE IF EXISTS `users`;
                DROP TABLE IF EXISTS `slide_contents`;
                DROP TABLE IF EXISTS `slide_list`;
                DROP TABLE IF EXISTS `steps`;
                DROP TABLE IF EXISTS `slide_types`;
                DROP TABLE IF EXISTS `pages`;
                
                CREATE TABLE `users` (
                  `idusers` int(11) NOT NULL AUTO_INCREMENT,
                  `email` varchar(255) NOT NULL,
                  `password` varchar(60) NOT NULL,
                  `name` varchar(255) NOT NULL,
                  `role` varchar(45) NOT NULL DEFAULT 'user',
                  `created_at` timestamp NULL DEFAULT NULL,
                  `modified_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  `token` VARCHAR(255) DEFAULT NULL,
                  PRIMARY KEY (`idusers`),
                  UNIQUE KEY `email_UNIQUE` (`email`),
                  KEY `idx_role` (`role`)
                ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
                
                
                CREATE TABLE `projects` (
                  `idprojects` int(11) NOT NULL AUTO_INCREMENT,
                  `hash` varchar(255) NOT NULL,
                  `title` varchar(255) DEFAULT NULL,
                  `user` int(11) NOT NULL,
                  `created_at` timestamp NULL DEFAULT NULL,
                  `modified_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`idprojects`),
                  KEY `idxProjectUser` (`user`),
                  KEY `idxProjectHash` (`hash`),
                  CONSTRAINT `fkProjectUser` FOREIGN KEY (`user`) REFERENCES `users` (`idusers`) ON DELETE NO ACTION ON UPDATE NO ACTION
                ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
                
                CREATE TABLE `sessions` (
                  `idsessions` int(11) NOT NULL AUTO_INCREMENT,
                  `session` varchar(255) NOT NULL,
                  `user` int(11) NOT NULL,
                  `created_at` timestamp NULL DEFAULT NULL,
                  `modified_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`idsessions`),
                  UNIQUE KEY `session_UNIQUE` (`session`),
                  KEY `idxSessionsUsers` (`user`),
                  CONSTRAINT `fkSessionsUsers` FOREIGN KEY (`user`) REFERENCES `users` (`idusers`) ON DELETE NO ACTION ON UPDATE NO ACTION
                ) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
                
                CREATE TABLE `slide_types` (
                  `idslide_types` int(11) NOT NULL AUTO_INCREMENT,
                  `slide_type` varchar(30) NOT NULL,
                  PRIMARY KEY (`idslide_types`)
                ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
                
                LOCK TABLES `slide_types` WRITE;
                /*!40000 ALTER TABLE `slide_types` DISABLE KEYS */;
                INSERT INTO `slide_types` VALUES (1,'Informative'),(2,'Interactive'),(3,'Branching'),(4,'Recap');
                /*!40000 ALTER TABLE `slide_types` ENABLE KEYS */;
                UNLOCK TABLES;
                
                CREATE TABLE `steps` (
                  `idsteps` int(11) NOT NULL AUTO_INCREMENT,
                  `position` int(11) NOT NULL,
                  `title` varchar(255) NOT NULL,
                  `description` text NOT NULL,
                  PRIMARY KEY (`idsteps`),
                  KEY `idxStepPosition` (`position`)
                ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
                                            
                
                CREATE TABLE `slide_list` (
                  `idslide_list` int(11) NOT NULL AUTO_INCREMENT,
                  `position` int(11) NOT NULL DEFAULT '1',
                  `title` text NOT NULL,
                  `description` text NOT NULL,
                  `step` int(11) NOT NULL,
                  `slide_type` int(11) NOT NULL,
                  PRIMARY KEY (`idslide_list`),
                  KEY `idxSlidelistStep` (`step`),
                  KEY `idxSlidelistWeight` (`position`,`step`),
                  KEY `idxSlidelistSlidetype` (`slide_type`),
                  CONSTRAINT `fkSlidelistSlidetype` FOREIGN KEY (`slide_type`) REFERENCES `slide_types` (`idslide_types`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                  CONSTRAINT `fkSlidelistStep` FOREIGN KEY (`step`) REFERENCES `steps` (`idsteps`) ON DELETE NO ACTION ON UPDATE NO ACTION
                ) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8;
                
                
                CREATE TABLE `slides` (
                  `idslides` int(11) NOT NULL AUTO_INCREMENT,
                  `project` int(11) NOT NULL,
                  `slide` int(11) NOT NULL,
                  `extra` text,
                  `answer` text,
                  `choice` varchar(120) DEFAULT NULL,
                  `status` tinyint(4) NOT NULL DEFAULT '0',
                  `created_at` timestamp NULL DEFAULT NULL,
                  `modified_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`idslides`),
                  UNIQUE KEY `project` (`project`,`slide`),
                  KEY `idxSlideProject` (`project`),
                  KEY `idxSlideSlideList` (`slide`),
                  CONSTRAINT `fkSlideProject` FOREIGN KEY (`project`) REFERENCES `projects` (`idprojects`) ON DELETE CASCADE ON UPDATE NO ACTION,
                  CONSTRAINT `fkSlideSlidelist` FOREIGN KEY (`slide`) REFERENCES `slide_list` (`idslide_list`) ON DELETE CASCADE ON UPDATE NO ACTION,
                  
                ) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;
                
                CREATE TABLE `pages` (
                    `idpages` int(11) NOT NULL AUTO_INCREMENT,
                    `title` varchar(255) NOT NULL,
                    `contents` text,
                    `url` varchar(255) NOT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `modified_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`idpages`)
                ) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
                
                
                CREATE OR REPLACE VIEW `view_slide_index` AS (
                    select concat_ws('.',`steps`.`position`,`slide_list`.`position`) AS `slide_index`,
                        `slide_list`.`step` AS `step`,
                        `slide_list`.`title` AS `title` 
                    from `slide_list`
                    inner join steps on step = idsteps
                    order by `steps`.`position`,`slide_list`.`position`);

                CREATE OR REPLACE VIEW `slides_with_step` AS 
                select `slides`.`idslides` AS `idslides`,`slides`.`project` AS `project`,`slides`.`slide` AS `slide`,`slides`.`extra` AS `extra`,`slides`.`answer` AS `answer`,`slides`.`choice` AS `choice`,`slides`.`status` AS `status`,`slides`.`created_at` AS `created_at`,`slides`.`modified_at` AS `modified_at`,`steps`.`position` AS `step`,`slide_list`.`position` AS `position`  
                from ((`slides` 
                    join `slide_list` on((`slides`.`slide` = `slide_list`.`idslide_list`))) 
                    join `steps` on((`slide_list`.`step` = `steps`.`idsteps`)));
            ";
            
            $dns = DBTYPE . ':dbname=' . DBNAME . ';host=' . DBHOST . ';charset=utf8';
            $PDO = new PDO($dns, DBUSER, DBPASS);
            $PDO->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $noDb = false;
            
            try{ 
                $create = $PDO->exec($sql);
            }
            catch(PDOException $e) {
                $noDb = true;
                $response[] = '<div class="alert alert-danger"><i class="fa fa-times"></i> <strong>Could not create tables and data!</strong> DB Error: ' . $e->getMessage() . '</div>';
            }            
            if( !$noDb )  {
                $response[] = '<div class="alert alert-success"><i class="fa fa-check"></i> Database tables and data all set up!</div>';
                $data = array(  'name'     => $user,
                                'email'    => $mail,
                                'password' => crypt($pass, '$1$'.SECRET),
                                'role'     => 'root'
                            );
                
                $User = new User;
                $Session = new Session;
                
                $idUser = $User->create($data);
                if($idUser){
                    $Session = new Session;
                    $Session->createSession($idUser);                    
                    $response[] = '<div class="alert alert-success"><h2><i class="fa fa-check"></i> User created successfully!</h2><p>Everything went right. Isn\'t that nice? Please remember to remove this file from your online directory.</p></div>';
                }
                else {
                    $response[] = '<div class="alert alert-danger"><i class="fa fa-times"></i> Oh No! I coudn\'t create the user profile. This probably means there\'s something wrong with the permissions of the database user."</p></div>';
                }
            }
        }
    }
    echo json_encode($response);

} else { 
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        
        <title>Installation: Tool Selection Assistant</title>
    
        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Lato:300,700|Francois+One" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
        <!-- Bootstrap -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
        <link rel="stylesheet" href="/dist/css/main.css">
        
        <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
          <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <style>
            .alert { border-radius: 0px; -moz-border-radius: 0px; -webkit-border-radius: 0px;  padding: 8px; margin: 8px 0px; }
            .alert h2 { margin-top: 0px; }
        </style>
    </head>
    <body>
        <div class="container-fluid" id="top">
            <div class="row">
                <div class="steps">
                    <div class="step step-1"></div>
                    <div class="step step-2"></div>
                    <div class="step step-3"></div>
                    <div class="step step-4"></div>
                </div>
            </div>
            
            
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
                    <h2>Welcome to the <span class="hilite">Tool Selection Assistant</span> installation</h2>
                    <?php if($noLocal): ?>
                    <div class="alert alert-danger">
                        <h2><i class="fa fa-times"></i> Attention!</h2>
                        <p>
                            No <strong>/config/local.php</strong> found! <br />
                            Without it the installer cannot work properly. <br />
                            Please look into the <a href="readme.txt">Readme file</a>, or follow instructions below.
                        </p>
                    </div>
                    <?php endif; ?>
                    <p>
                        Before continuing, please note that you need to properly configure your enviroment variables, found in the file <strong>/config/local.example.php</strong>.
                        We suggest you copy the file and rename it to "local.php" and proceed to edit the information in the new file. After doing this, you may click on the "Check Configuration" button to make sure everything is working smoothly. 
                    </p>
                    
                    <button class="btn btn-alt btn-lg" id="config-check" type="button">Check Configuration</button>
                    <div class="config-checks"></div>
                    <p>If everything is working fine, you may proceed to install the Root user and start working with the TSA.</p>
                    <hr />
                    
                    <p>Please fill in this form to install the application.</p>
                    <form class="form-horizontal" method="post" action="/install.php" id="install">
                        
                        <div class="form-box">
                            <h3>Application Configuration</h3>
                            <div class="install-response"></div>
                            <div class="form-group">
                                <label for="appusername" class="col-sm-3 control-label">Root Username *</label>
                                <div class="col-sm-9">
                                    <input type="text" name="appusername" id="appusername" class="form-control" value="Root">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="apppwd" class="col-sm-3 control-label">Root Password *</label>
                                <div class="col-sm-9">
                                    
                                    <div class="input-group">
                                        <input type="text" name="apppwd" id="apppwd" class="form-control">
                                        
                                        <span class="input-group-btn">
                                            <button class="btn btn-addon" type="button" id="key-gen">Generate</button>
                                        </span>
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="appemail" class="col-sm-3 control-label">Root Email</label>
                                <div class="col-sm-9">
                                    <input type="text" name="appemail" id="appemail" class="form-control">
                                </div>
                            </div>
                        </div>
                        <small>Fields marked with a * are mandatory.</small>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-alt btn-lg btn-block" id="install-app">INSTALL</button>
                        </div>
                        
                    </form>
                </div>
            </div>
        </div>
        <br/><br/>
       
        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script>
            $(document).ready(function(){
                $('#key-gen').click(function(e){
                    var StringCollection = 'ABCDEFGHIJKLMNOPQRSTUVXYZ!$.,-;:_#+*[]^?&()=|abcefghijklmnopqrstuvxyz1234567890';
                    var randomizedString = '';
                    for(i = 0; i < 17; i++){
                        randomizedString += StringCollection.charAt((Math.floor( (Math.random() * StringCollection.length) + 1)));
                    }
                    $('#apppwd').val(randomizedString);
                    
                    e.preventDefault();
                    return false;    
                });
                
                $('#config-check').click(function(e){
                    $('.config-checks').children().remove();
                    $.get('/install.php', {'config-check': 1}, function(response){
                        $.each(response, function() {
                            $('.config-checks').append(this);    
                        })  
                    }, 'json' );     
                    
                });
                
                $('#install-app').click(function(e){
                    $('.install-response').children().remove();
                    
                    var data = $('#install').serialize();
                    $.post('/install.php', data, function(response){
                        $.each(response, function() {
                            $('.install-response').append(this);    
                        })  
                    }, 'json' );     
                    e.preventDefault();
                    return false;
                });
            });
        </script>
    </body>
</html>
<?php } ?>
