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
        if (preg_match_all('!(?<content>.*?)(?<box>\[--box\|(?<boxtype>.*?)--](?<boxcontent>.*?)\[--endbox--])\s*(?<trailer></[pP]>)?!s', $string, $matches)) {
            $output = array();
            foreach($matches['content'] as $content) {
                $trailer = array_shift($matches['trailer']);

                $box = new stdClass();
                $box->type = array_shift($matches['boxtype']);
                $box->text = array_shift($matches['boxcontent']);

                $output[] = array(
                    'content' => $content . $trailer,
                    'box' => $box
                );
            }
            return $output;
        } else {
            return array(array('content' => $string));
        }
    }

    /** inject textarea and parse tags in text **/
    function injectAnswerField($string, $name = 'answer', $origin = null){
        $tmpl = TwigManager::getInstance()->createTemplate("<textarea id=\"answer\" name=\"{{name}}\" class=\"form-control\" rows=\"8\">{{ answer }}</textarea>");
        return preg_replace_callback(
            '|\[--answer--]|m',
            function ($matches) use ($name, $origin, $tmpl) {
                return $tmpl->render(array('name' => $name, 'answer' => @$origin['answer']));
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

        $tmpl = TwigManager::getInstance()->createTemplate("<textarea id=\"multianswer-{{i}}\" name=\"{{name}}[{{i}}]\" class=\"form-control\" rows=\"8\">{{ answer }}</textarea>");

        return preg_replace_callback(
            '|\[--multiple-answer-(?<i>\d)--]|',
            function ($matches) use ($a, $name, $tmpl) {
                $i = $matches['i'];
                return $tmpl->render(array('i' => $i, 'name' => $name, 'answer' => $a[$i]));
                
            },
            $string);
            
    }

    function injectAnswers($string, $original, $project) {

        $string = preg_replace(
            '|\[--box\|(?<name>\w+)--](.*?)\[--endbox--]|s',
            "",
            $string
        );

        return preg_replace_callback('/\[--(.*?)\--]/',
            function ($matches) use ($original, $project) {
                $parts = explode('|', $matches[1]);

                if (substr($parts[0], 0, 15) == "multiple-answer") {
                    $multiparts = explode('-', $parts[0]);
                    $multipart = array_pop($multiparts);
                    return "<p class=\"previous-answer box box-answer  recap-answer\" data-field=\"{$parts[0]}\">" . $original['multianswer'][$multipart] . "</p>";
                }

                if ($parts[0] == 'customform') {
                    return customform($parts[1], $original, $project, true);
                }

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
                        $tmpl = TwigManager::getInstance()->createTemplate("<p class=\"previous-answer box box-answer  recap-answer\" data-field=\"answer\">{{ answer }}</p>");
                        return $tmpl->render(array('answer' => $original['answer']));
                        break;
                    case "radio":
                        if ($original[$parts[1]] == $parts[2]) {
                            return "<p class=\"previous-answer box box-answer recap-answer\" data-field=\"{$parts[1]}\">Selected: " . $parts[3] . "</p>";
                        }
                        break;
                    case "check":
                        if (@$original[$parts[1]]) {
                            return "<p class=\"previous-answer box box-answer  recap-answer\" data-field=\"{$parts[1]}\">Checked: " . $parts[2] . "</p>";
                        }
                        break;
                    case "array":
                        $tmpl = TwigManager::getInstance()->createTemplate("<ul class=\"previous-answer box box-answer recap-answer\" data-field=\"{{name}}\">
{% for value in answers %}
<li>{{ value }}</li>
{% endfor %}
</ul>");
                        $s = $tmpl->render(array('answers' => $original[$parts[1]],  'name' => $parts[1]));
                        return $s;
                    default:
                        $tmpl = TwigManager::getInstance()->createTemplate("<p class=\"previous-answer box box-answer recap-answer\" data-field=\"{{name}}\">{{ answer }}</p>");

                        return $tmpl->render(array('name' => $parts[1], 'answer' => $original[$parts[1]]));
                        break;
                }
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
        
        return preg_replace_callback(
            '!\[--prev\|(\d)\.(\d+)\|([\w\d\-\[\]]+?)--]!',
            function ($matches) use ($project, $Slides) {
                $step = $matches[1];
                $slide = $matches[2];
                $slidecontent = $Slides->findPreviousAnswer($project, $step, $slide);
                $name = $matches[3];
                
                //return "$name $project, $step, $slide";
                
                if (!$slidecontent) {
                    return "<b>previous answer not found</b>";
                }
                
                $data = json_decode($slidecontent->answer, true);

                $tmpl = TwigManager::getInstance()->createTemplate(
                "<div class=\"previous-answer box box-answer\"><h3>{{step}}.{{slide}} {{title}}</h3>
<div id=\"answerBox\">{% if answer is iterable %}{{ answer|join(',') }}{% else %}{{ answer }}{% endif %}</div>
<!-- <a href=\"#\" class=\"prev-answer\" data-toggle=\"modal\" data-target=\".editPrevAnswer\">I need to change this answer.</a> -->
</div>");
                $previous = $tmpl->render(array('answer' => $data['name'], 'step' => $step, 'slide' => $slide, 'title' => $slidecontent->title));
                return $previous;
            },
            $string
        );
        
        // return array( 'content' => $string, 'slide' => $slide, 'multi' => $multi );

    }

    function formatBox($type, $text) {
        $tmpl = TwigManager::getInstance()->createTemplate("<div class=\"box box-{{box}}\"><h3>{% if box == 'casestudy'%}case study{% else %}{{ box }}{% endif %}</h3>{{ text|raw }}</div>");

        return $tmpl->render(array('box' => $type, 'text' => $text));

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
        return preg_replace_callback(
            '/\[--choicebutton\|(?<name>\w+)\|(?<title>[\s\w]+)--]/', 
            function ($matches) {
                return "<a href=\"#\" class=\"btn btn-alidade btn-lg picker\" data-target=\"#{$matches['name']}\">{$matches['title']}</a>&nbsp;";
            },
            $string);

    }

    function injectChoicePanels($string, $depth=0) {
        $s = preg_replace_callback(
            '/\[--choicepanel\|(?<name>[\w\d]+)--](.*?)\[--endchoicepanel(\|\1)?--]/s',
            function ($matches) {
                return "<div class=\"row hide picks\" id=\"{$matches['name']}\">{$matches[2]}</div>";
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
        return preg_replace_callback(
            '/\[--radio\|(?<name>[\w\d]+)\|(?<key>[\w\d]+)\|(?<title>.*?)--]/',
            function ($matches) use ($original) {
                $name = $matches['name'];
                $key = $matches['key'];
                if (@$original[$name] == $key) {
                    $sel = "checked";
                } else {
                    $sel = "";
                }
                return "<div class=\"radio\"><label><input id=\"choice-{$key}\" name=\"{$name}\" $sel class=\"choice\" type=\"radio\" value=\"{$key}\"> {$matches['title']}</label></div>";
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
            echo '<link type="text/css" rel="stylesheet" href="' . $css . '">';
        }
    }
