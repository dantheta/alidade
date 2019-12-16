<?php

    require_once("forms.php");

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

    /* Split content at infoboxes, return list of content/box arrays */
    function splitBoxes($string) {
        if (preg_match_all('!(?<content>.*?)(?<box>\[--box\|(?<boxtype>.*?)--](?<boxcontent>.*?)\[--endbox--])\s*(?<trailer></[pP]>)?!s',
                $string,
                $matches)) {
            $output = array();
            foreach($matches['content'] as $content) {
                $trailer = array_shift($matches['trailer']);

                $box = new stdClass();
                $box->type = array_shift($matches['boxtype']);
                $box->text = trim(array_shift($matches['boxcontent']));

                $output[] = array(
                    'content' => $content . $trailer,
                    'box' => $box
                );
            }
            // work out how much of the string has been consumed
            $matchoffset = 0;
            foreach($matches[0] as $s) {
                $matchoffset += strlen($s);
            }
            $remainder = trim(substr($string, $matchoffset));

            // return remaining string as another content segment
            if ($remainder) {
                $output[] = array('content' => $remainder);
            }

            return $output;
        } else {
            return array(array('content' => $string));
        }
    }

    function loadFieldTemplate() {
        return TwigManager::getInstance()->load("project/forms.html");
    }

    /** inject textarea and parse tags in text **/
    function injectAnswerField($string, $name = 'answer', $origin = null){
        $tmpl = loadFieldTemplate();
        return preg_replace_callback(
            '|\[--answer--]|m',
            function ($matches) use ($name, $origin, $tmpl) {
                return $tmpl->renderBlock('answer', array('name' => $name, 'answer' => @$origin['answer']));
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

        $tmpl = loadFieldTemplate();

        return preg_replace_callback(
            '|\[--multiple-answer-(?<i>\d)--]|',
            function ($matches) use ($a, $name, $tmpl) {
                $i = $matches['i'];
                return $tmpl->renderBlock('multi_answer_field', array('i' => $i, 'name' => $name, 'answer' => $a[$i]));
                
            },
            $string);
            
    }

    function injectAnswers($string, $original, $project, &$warnings=null) {
        // Warnings is passed by reference, since this function adds a "done" flag to
        // each warning displayed.  This prevents repeat rendering.
        if (is_null($warnings)) {
            $warnings = array();
        }
        $string = preg_replace(
            '|\[--box\|(?<name>\w+)--](.*?)\[--endbox--]|s',
            "",
            $string
        );

        $tmplb = TwigManager::getInstance()->load("project/recap_fields.html");

        return preg_replace_callback('/\[--(.*?)\--]/',
            function ($matches) use ($original, $project, $tmplb, &$warnings) {
                $parts = explode('|', $matches[1]);

                $out = "";
                if (substr($parts[0], 0, 15) == "multiple-answer") {
                    $multiparts = explode('-', $parts[0]);
                    $multipart = array_pop($multiparts);
                    $out = $tmplb->renderBlock("multianswer", array(
                        'original' => $original,
                        'multipart' => $multipart,
                        'parts' => $parts
                    ));
                    $critname = "multianswer-" . $multipart;
                } elseif ($parts[0] == 'customform') {
                    $out = customform($parts[1], $original, $project, true);
                } else {

                    switch ($parts[0]) {
                        case "prev":
                        case "box":
                        case "endbox":
                        case "choicebutton":
                        case "choicepanel":
                        case "endchoicepanel":
                            return "";
                            break;
                        case "answer":
                            $out = $tmplb->renderBlock('answer', array('answer' => $original['answer']));
                            $critname = "answer";
                            break;
                        case "radio":
                            if ($original[$parts[1]] == $parts[2]) {
                                $out = $tmplb->renderBlock('radio', array('parts' => $parts));
                            }
                            $critname = $parts[1];
                            break;
                        case "check":
                            if (@$original[$parts[1]]) {
                                $out = $tmplb->renderBlock('check', array('parts' => $parts));
                            }
                            $critname = $parts[1];
                            break;
                        case "array":
                            $out = $tmplb->renderBlock('array', array('answers' => $original[$parts[1]], 'name' => $parts[1]));
                            $critname = $parts[1];
                            break;
                        default:
                            $out = $tmplb->renderBlock('default', array('name' => $parts[1], 'answer' => $original[$parts[1]]));
                            $critname = $parts[1];
                            break;
                    }
                }
                foreach($warnings as &$warn) {
                    foreach ($warn['criteria'] as $criterion) {
                        if ($criterion['name'] == $critname) {
                            if (!@$warn['done']) {
                                $warn['done'] = 1;
                                $out .= $tmplb->renderBlock('warning', array('warn' => $warn, 'name' => $critname));
                            }
                            break;
                        }
                    }
                }
                return $out;
            },
            $string
        );
    }

    function injectParam($string, $param, $value){
        return str_replace('[--'.$param.'--]', $value, $string);
    }

    function injectPrevAnswer($string, $project){
        //$string = strip_tags($string, '<div><b><i><a><ul><ol><li>');
        
        $Slides = new Slide;
        $tmpl = loadFieldTemplate();
        return preg_replace_callback(
            '!\[--prev\|(\d)\.(\d+)\|([\w\d\-\[\]]+?)--]!',
            function ($matches) use ($project, $Slides, $tmpl) {
                $step = $matches[1];
                $slide = $matches[2];
                $slidecontent = $Slides->findPreviousAnswer($project, $step, $slide);
                $name = $matches[3];
                
                //return "$name $project, $step, $slide";
                
                if (!$slidecontent) {
                    return $tmpl->renderBlock('answer_not_found');
                }
                
                $data = json_decode($slidecontent->answer, true);

                $previous = $tmpl->renderBlock('previous_answer', array(
                    'answer' => $data['name'],
                    'step' => $step,
                    'slide' => $slide,
                    'title' => $slidecontent->title
                ));
                return $previous;
            },
            $string
        );
        
        // return array( 'content' => $string, 'slide' => $slide, 'multi' => $multi );

    }

    function formatBox($type, $text) {
        $tmpl = loadFieldTemplate();

        return $tmpl->renderBlock('box', array('box' => $type, 'text' => $text));

    }

    function injectBox($string){
    
        $boxes = array();

        $output = preg_replace_callback(
            '|\[--box\|(?<name>\w+)--](.*?)\[--endbox--]|s',
            function ($matches) use (&$boxes) {
                $box = $matches['name'];
                $text = $matches[2];
                $boxes[] = formatBox($box, $text);
                return "";
            },
            $string);
        
        return array('content' => $output, 'boxes' => $boxes);
    }

    function injectChoiceButtons($string) {
        $tmpl = loadFieldTemplate();
        return preg_replace_callback(
            '/\[--choicebutton\|(?<name>[\w\d_]+)\|(?<title>[\s\w]+)--]/',
            function ($matches) use ($tmpl) {
                return $tmpl->renderBlock('choice_button', array('name' => $matches['name'], 'title' => $matches['title']));
            },
            $string);

    }

    function injectChoicePanels($string, $depth=0) {
        $tmpl = loadFieldTemplate();
        $s = preg_replace_callback(
            '/\[--choicepanel\|(?<name>[\w\d_]+)--](.*?)\[--endchoicepanel(\|\1)?--]/s',
            function ($matches) use ($tmpl) {
                return $tmpl->renderBlock('choice_panel', array('name' => $matches['name'], 'content' => $matches[2]));
            },
            $string);
        if ($s == $string || $depth == 3) {
            // no changes
            return $s;
        } else {
            // attempt another pass, for nested panels
            return injectChoicePanels($s, $depth+1);
        }
    }

    function injectArray($string, $original) {
        return preg_replace_callback(
            '/\[--array\|(?<name>[\w\d]+)--]/',
            function ($matches) use ($original) {
                $name = $matches['name'];
                return alpaca_field($name, @$original[$name]);
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
        $tmpl = loadFieldTemplate();
        return preg_replace_callback(
            '/\[--radio\|(?<name>[\w\d]+)\|(?<key>[\w\d]+)\|(?<title>.*?)--]/',
            function ($matches) use ($original, $tmpl) {
                if (@$original[$matches['name']] == $matches['key']) {
                    $sel = "checked";
                } else {
                    $sel = "";
                }
                return $tmpl->renderBlock('radio', array(
                    'name' => $matches['name'],
                    'key' => $matches['key'],
                    'sel' => $sel,
                    'title' => $matches['title']
                ));
            },
            $string);
    }

    function injectCheckboxes($string, $original=null) {
        $tmpl = loadFieldTemplate();
        return preg_replace_callback(
            '/\[--check\|(?<name>[\[\]\w]+)\|(?<title>.*?)--]/',
            function ($matches) use ($original, $tmpl) {
                if (@$original[$matches['name']]) {
                    $sel = "checked";
                } else {
                    $sel = "";
                }
                return $tmpl->renderBlock('check', array(
                    'name' => $matches['name'],
                    'sel' => $sel,
                    'title' => $matches['title']
                ));
            },
            $string);        
    }

    function injectCustomForm($string, $project, $original) {
        return preg_replace_callback(
            '/\[--customform\|(?<name>[\d\.]+)--]/',
            function ($matches) use ($original, $project) {
                return customform($matches['name'], $original, $project);
            },
            $string
        );
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
    function checkSlidePosition($currentStep, $currentSlide, $indexStep, $indexSlide, $status){
        $check = '';

        if($currentStep == $indexStep && $currentSlide == $indexSlide){
            return 'working';
        }
        if ($status) {
            return "done";
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
            echo '<link type="text/css" rel="stylesheet" href="' . $css . '">';
        }
    }

    /* warning functions */

    function loadWarnings() {
        $data = yaml_parse_file(ROOT . DS . 'content' . DS . 'warnings.yml');
        return $data['slides'];
    }

    function findWarnings($warningdata, $slide) {
        $out = array();
        foreach($warningdata as $warning) {
            if ($warning['slide'] == $slide) {
                $out[] = $warning;
            }
        }
        return $out;
    }

    function evaluateWarning($warning, $answer) {
        // for each slide, a set of acceptance criteria are defined.  If the criteria are not met, false is returned and the warning is displayed.

        $result = true;
        foreach($warning['criteria'] as $crit) {
            if (@$crit['any'] || @$crit['all']) {
                if (@$crit['any']) {
                    $fields = $crit['any'];
                }
                if (@$crit['all']) {
                    $fields = $crit['all'];
                }
                $values = array();
                foreach($fields as $fld) {
                    $values[] = $answer[$fld];
                }

            } elseif (substr($crit['name'], 0, 11) == "multianswer") {
                $multiparts = explode('-', $crit['name']);
                $multipart = array_pop($multiparts);
                $values = array($answer['multianswer'][$multipart]);
            } else {
                $values = array($answer[$crit['name']]);
            }
            foreach($values as $value) {
                if (@$crit['any']) {
                    $result = true;
                }
                if (@$crit['is'] == "empty") {
                    if (trim($value) != "") {
                        $result = false;
                    }
                }
                if (@$crit['is'] == "not-empty") {
                    if (trim($value) == "") {
                        $result = false;
                    }
                }
                if (@$crit['value']) {
                    if ($crit['value'] != $value) {
                        $result = false;
                    }
                }
                if (@$crit['not-value']) {
                    if ($crit['value'] == $value) {
                        $result = false;
                    }
                }
                if (@$crit['any']) {
                    if ($result) {
                        break;
                    }
                }
            }
            if (!@$crit['any'] && !$result) {
                break;
            }
        }
        return $result;
    }