<?php

    /** checks if user has a role
     * var object $user from Auth->getProfile() (Session->getSession())
     * returns boolean
     * */
    function hasRole($user, $role){

        if(ucfirst($user->role) == 'Root'){
            return true;
        }
        else {
            if($user->role == ucwords($role)){
                return true;
            }
            else {
                return false;
            }
        }
    }



    /** Prints out Bootstrap alerts
     * finds key of response and
     * uses it to format the alert
     * as wished
     * */
    function printResponse($response){
        foreach($response as $type => $text){
            switch($type) {
                case 'success':
                    $icon = 'check';
                    break;
                case 'danger':
                    $icon = 'exclamation-triangle';
                    break;
                case 'warning':
                    $icon = 'exclamation-circle';
                    break;
                case 'info':
                    $icon = 'info';
                    break;
            }
            echo '<div class="alert alert-' . $type . '  alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <i class="fa fa-' . $icon . '"></i> ' . $text . '

                </div>';
        }
    }

    /** Parse responses from deletion
     * and passes off the info to printResponse
     * */
    function parseResponse($response){
        $res = array();
        $r = explode(':', $response);

        switch($r[0]){
            case 'd':
                if($r[1] == 'err') {
                    $res['danger'] = 'Could not delete the desired element.';
                }
                elseif($r[1] == 'ok') {
                    $res['success'] = 'Element permanently deleted.';
                }
                break;
            default:
                break;
        }
        return $res;
    }

    /**
     * verify that an array index exists and is not empty or null.
     * can also do some type control.
     * */
    function verify($var, $strict = false, $type = 'string'){
        if(!isset($var) || empty($var) || is_null($var)){
            return false;
        }
        else {
            if($strict){
                switch($type){
                    case 'number':
                        if(is_numeric($var)){
                            return true;
                        }
                        break;
                    case 'string':
                        return true;
                        break;
                    case 'array':
                        if(is_array($var)){
                            return true;
                        }
                        break;
                    default:
                        return false;
                        break;
                }
            }
            else {
                return true;
            }

        }
    }

    /** prints friendly arrays
     * used mainly for debugging
     * */
    function dbga($array){
        echo '<div class="dbg"><pre>';
        print_r($array);
        echo '</pre></div>';
    }

    function dsql($sql){
        echo '<div class="dbg"><pre>';
        echo $sql;
        echo '</pre></div>';
    }


    /**
     *DateTime printers
     **/
    function dateFormat($timestamp){
        return date('D, j M Y, H:i', $timestamp);
    }

    function dateFormatNoTime($timestamp){
        return date('D, j M Y', $timestamp);
    }

    function dbDate($date){
        return date('Y-m-d H:i:s', strtotime($date));
    }
    function dbDateNoTime($string){
        $d = explode('/', $string);
        return implode('-', array_reverse($d));
    }

    function getOptionName($str) {
        $t = preg_replace('/[^A-Za-z0-9\- ]/', '', $str);
        return strtolower($t);
    }

    function getPlaceholders($str) {
        $ph = array();
        preg_match_all('/\[--(.*?)\--]/', $str, $matches);
        foreach ($matches[1] as $match) {
            $ph[] = explode('|', $match);
        }
        return $ph;
    }

    /** inject textarea and parse tags in text **/
    function injectAnswerField($string, $name = 'answer', $origin = null){
        
        return preg_replace_callback(
            '|\[--answer--]|m',
            function ($matches) use ($name, $origin) {
                return "<textarea id=\"answer\" name=\"${name}\" class=\"form-control\" rows=\"8\">" . (!is_null($origin) ? $origin['answer'] : '' ) . '</textarea>';
            },
            $string);
    }
    function injectMultipleAnswerField($string, $origin = null){
        // get data from answer fields
        $name = 'multianswer';
        $a = @$origin[$name];
        if (is_array($a)) {
            $a = array_map('trim', $a);
        }

        return preg_replace_callback(
            '|\[--multiple-answer-(?<i>\d)--]|',
            function ($matches) use ($a, $name) {
                $i = $matches['i'];
                return "<textarea id=\"multianswer-${i}\" name=\"${name}[${i}]\" class=\"form-control\" rows=\"8\">" . ( isset($a[$i]) ? $a[$i] : '' ) . '</textarea>';
                
            },
            $string);
            
    }

    function injectParam($string, $param, $value){
        return str_replace('[--'.$param.'--]', $value, $string);
    }

    function injectPrevAnswer($string){
        //$string = strip_tags($string, '<div><b><i><a><ul><ol><li>');

        preg_match_all('/\[--prev\|\d\.\d\--]/im', $string, $matches);
        $matches =  $matches[0];

        if(!empty($matches) && is_array($matches)){

            $p = explode('|', $matches[0]);
            $slide = str_replace('--]', '', $p[1]);
            $parts = explode('.', $slide);
            $step = $parts[0];
            $slide = $parts[1];
            // palce slide model here and use getPreviousANswer method
            $Slides = new Slide;
            $hash = filter_var($_GET['p'], FILTER_SANITIZE_SPECIAL_CHARS);
            $slide = $Slides->findPreviousAnswer($hash, $step, $slide);
            // if the answer had multi texts, manage that
            if(preg_match('/##break##/', $slide->answer) !== false){
                $parts = explode('##break##', $slide->answer);
                $text = '<ul><li>' . implode('</li><li>', $parts) . '</li></ul>';
                $multi = true;
            }
            else {
                $text = nl2br($slide->answer);
                $multi = false;
            }

            $previous = "<div class=\"previous-answer box box-answer\"><h3>" . $slide->step . "." . $slide->slide . " "  . $slide->title . "</h3><div id=\"answerBox\">" . $text . "</div><a href=\"#\" class=\"prev-answer\" data-toggle=\"modal\" data-target=\".editPrevAnswer\">I need to change this answer.</a></div>";

            $string = str_replace($matches[0], $previous, $string);

            return array( 'content' => $string, 'slide' => $slide, 'multi' => $multi );
        }
        else {
            return false;
        }
    }

    function injectRecap($string){
      preg_match_all('/\[--prev\|\d\.\d\--]/im', $string, $matches);
      if(!empty($matches[0]) && is_array($matches[0])){
        foreach($matches[0] as $i => $match){
          $p = explode('|', $match);
          $slide = str_replace('--]', '', $p[1]);
          $parts = explode('.', $slide);
          $step = $parts[0];
          $slide = $parts[1];

          // place slide model here and use getPreviousANswer method
          $Slides = new Slide;
          $hash = filter_var($_GET['p'], FILTER_SANITIZE_SPECIAL_CHARS);
          $slide = $Slides->findPreviousAnswer($hash, $step, $slide);

          $title = $slide->step . "." . $slide->slide . " " . $slide->title;
          if(preg_match('/##break##/', $slide->answer) !== false){
            $parts = explode('##break##', $slide->answer);
            $text = '<ul><li>' . implode('</li><li>', $parts) . '</li></ul>';
            $multi = true;
          }
          else {
            $text = nl2br($slide->answer);
            $multi = false;
          }
          $box =  "<div class=\"previous-answer box box-answer\"><h3>" . $title . "</h3>" .
                  "<div id=\"answerBox\">" . $text . "</div>" .
                  "</div>" ;
          /* "<a href=\"#\" class=\"prev-answer\" data-toggle=\"modal\" data-target=\".editPrevAnswer\">I need to change this answer.</a>*/
          $string = str_replace($match, $box, $string);
        }
        return $string;
      }
      else {
        return $string; 
      }
    }

    function injectBox($string){
    
        $boxes = array();
        $output = preg_replace_callback(
            '|\[--box\|(?<name>\w+)--](.*?)\[--endbox--]|s',
            function ($matches) use (&$boxes) {
                $box = $matches['name'];
                $text = $matches[2];
                $boxes[] = "<div class=\"box box-${box}\"><h3>" . ($box=='casestudy' ? 'case study' : $box) . '</h3>' . $text . '</div>';
                return "";
            },
            $string);
        
        return array('content' => $output, 'boxes' => $boxes);
    }

    function injectChoiceButtons($string) {
        return preg_replace_callback(
            '/\[--choicebutton\|(?<name>\w+)\|(?<title>[\s\w]+)--]/', 
            function ($matches) {
                return "<a href=\"#\" class=\"btn btn-alidade btn-lg picker\" data-target=\"#{$matches['name']}\">{$matches['title']}</a>&nbsp;";
            },
            $string);

    }

    function injectChoicePanels($string) {
        return preg_replace_callback(
            '/\[--choicepanel\|(?<name>\w+)--](.*?)\[--endchoicepanel--]/', 
            function ($matches) {
                return "<div class=\"row hide picks\" id=\"{$matches['name']}\">{$matches[2]}</div>";
            },
            $string);
        
    }

    function injectDropDown($string) {
        preg_match_all('/\[--dropdown\|(?<name>\w+)--](.*?)\[--enddropdown--]/', $string, $matches);
        $fullMatches = $matches[0];
        $names = $matches['name'];
        $contents = $matches[2];
        foreach($fullMatches as $match) {
            $name = array_shift($names);
            $content = array_shift($contents);
            
            $string = str_replace($match, "<select id=\"{$name}\" name=\"{$name}\">{$content}</select>", $string);
        }
        
        return $string;
    }

    function injectOptions($string) {
        preg_match_all('/\[--option\|(?<name>\w+)\|(?<title>[\s\w]+?)--]/', $string, $matches);
        $fullMatches = $matches[0];
        $titles = $matches['title'];
        $names = $matches['name'];
        foreach($fullMatches as $match) {
            $name = array_shift($names);
            $title = array_shift($titles);
            $string = str_replace($match,
                                  "<option value=\"{$name}\"> {$title}",
                                  $string);
        }
        return $string;            
    }

    function injectRadioButtons($string, $original=null) {
        return preg_replace_callback(
            '/\[--radio\|(?<name>\w+)\|(?<title>.*?)--]/',
            function ($matches) use ($original) {
                if (@$original['choice'] == $matches['name']) {
                    $sel = "checked";
                } else {
                    $sel = "";
                }
                return "<div class=\"radio\"><label><input id=\"choice-{$matches['name']}\" name=\"choice\" $sel class=\"choice\" type=\"radio\" value=\"{$matches['name']}\"> {$matches['title']}</label></div>";
            },
            $string);
    }

    function injectCheckboxes($string, $original=null) {
        return preg_replace_callback(
            '/\[--check\|(?<name>[\[\]\w]+)\|(?<title>.*?)--]/',
            function ($matches) use ($original) {
                if (@$original[$matches['name']]) {
                    $sel = "checked";
                } else {
                    $sel = "";
                }
                return "<div class=\"checkbox\"><input id=\"check-{$matches['name']}\" $sel name=\"{$matches['name']}\" type=\"checkbox\" value=\"{$matches['title']}\"> {$matches['title']}</div>";
            },
            $string);        
    }

    /** title printing, parsing position **/
    function printTitle($slide, $slideTitle){
        $cur = explode('.', $slide);
        switch($cur[0]){
            case 1:
                $title .= 'Understanding your needs';
                break;
            case 2:
                $title .= 'Understanding the tech';
                break;
            case 3:
                $title .= 'Trying tools out';
                break;
            case 4:
                $title .= 'Finding help';
                break;
            default:
                $title .= 'Quick tips';
                break;
        }

        echo $title . ' - ' . $slideTitle;

    }


    /** check slide position and status, return css class **/
    function checkSlidePosition($currentStep, $currentSlide, $indexStep, $indexSlide){
        $check = '';

        if($currentStep == $indexStep){
            if($currentSlide == $indexSlide){
                return 'working';
            }
            elseif($currentSlide > $indexSlide) {
                return 'done';
            }
        }
        elseif($currentStep > $indexStep) {
            return 'done';
        }
        return null;
    }


    /** Print js scripts from controller-defined variable $js **/
    function print_scripts($js, $inject=false){
        if(is_array($js)){
            foreach($js as $path){
                echo '<script src="' . $path . '"></script>';
            }
        }
        else {
            if($inject == true){
                echo '<script>' . $js . '</script>';
            }
           else {
                echo '<script src="' . $js . '"></script>';
           }
        }
    }
    /** Print css links from controller-defined variable $css **/
    function print_styles($css){
        if(is_array($css)){
            foreach($css as $path){
                echo '<link type="text/css" rel="stylesheet" href="' . $path . '">';
            }
        }
        else {
            echo '<link type="tex/css" rel="stylesheet" href="' . $css . '">';
        }
    }
