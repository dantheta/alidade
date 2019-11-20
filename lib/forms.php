<?php

$lawful_bases = array(
    'consent' => 'Consent',
    'contractual' => 'Contractual',
    'legal_obligation' => 'Legal Obligation',
    'vital_interests' => 'Vital Interests',
    'public_task' => 'Public Task',
    'legitimate_interests' => 'Legitimate Interests'
    );

function alpaca_field($name, $data) {
    if (!@$data) {
        $d = "[]";
    } else {
        $d = json_encode(@$data);
    }

    $s = <<<EOM
<div id="$name"></div>
<script type="text/javascript">
$('#$name').alpaca({
    data: $d,
    options: {
        name: "${name}",
        id: "$name"
    }
});
</script>
EOM;
    return $s;

}

function get_sanitized_name($value) {
    return preg_replace('/[^\w\d]+/', '_', strtolower($value));
}

function customform($slide, $original, $project, $recap=false) {  // original json data

    # get previous answer data
    $Slide = new Slide();

    switch($slide) {
    case "2.2":
    case "2.3":
        $previous = $Slide->findPreviousAnswer($project, 2, 1);
        break;
    default: die("unknown slide $slide"); break;
    };
    $previousanswer = json_decode($previous->answer, TRUE);

    switch($slide) {
    case "2.2": return customform_2_2($original, $previousanswer, $recap); break;
    case "2.3": return customform_2_3($original, $previousanswer, $recap); break;
    }

}

function customform_2_2($answer, $previousanswer, $recap) {
    if ($recap) {
        return customform_2_2_recap($answer, $previousanswer);
    }
    $s = '<div class="custom-form">';

    foreach($previousanswer['data_collected'] as $category) {
        $fieldname = get_sanitized_name($category);
        $title = ucfirst($category);
        $s .=<<<EOM
<div class="fieldcontainer" id="$category">
<legend>$title</legend>
EOM;
        $s .= alpaca_field("{$fieldname}___purposes", @$answer["{$fieldname}___purposes"]);

        $s .=<<<EOM
</div>
EOM;
    }
    $s .= "</div>";
    return $s;

}

function customform_2_2_recap($answer, $previousanswer) {

    $s = '<div class="custom-form">';
    foreach($previousanswer['data_collected'] as $category) {
        $fieldname = get_sanitized_name($category);
        $title = ucfirst($category);
        $s .=<<<EOM
<div class="recap-fieldcontainer" id="$category">
<h3>$title</h3>
<ul class="box box-answer previous-answer recap-answer" data-field="{$fieldname}">
EOM;
        foreach($answer["{$fieldname}___purposes"] as $value) {
            $s .= "<li>$value</li>\n";
        }

        $s .=<<<EOM
<ul>
</div>
EOM;
    }
    $s .= "</div>";
    return $s;

}


function customform_2_3($answer, $previousanswer, $recap) {
    global $lawful_bases;

    if ($recap) {
        return customform_2_3_recap($answer, $previousanswer);
    }

    $s = '<div class="custom-form">';

    foreach($previousanswer['data_collected'] as $category) {
        $fieldname = get_sanitized_name($category);
        $title = ucfirst($category);

        $s .=<<<EOM
<div class="fieldcontainer" id="$category">
<legend>$title</legend>
<div>
Select a lawful basis for processing $category:
<select name="{$fieldname}___lawful_basis">

EOM;
        foreach($lawful_bases as $basisname => $basis_title) {
            if ($answer["{$fieldname}___lawful_basis"] == $basisname) {
                $sel = " selected";
            } else {
                $sel = '';
            }
            $s .=<<<EOM
    <option value="$basisname" $sel>$basis_title</option>

EOM;
        }
        $s .=<<<EOM
</select>
</div>
</div>
EOM;
    }
    $s .= "</div>";
    return $s;

}

function customform_2_3_recap($answer, $previousanswer) {

    global $lawful_bases;

    $s = '<div class="custom-form">';

    foreach($previousanswer['data_collected'] as $category) {
        $fieldname = get_sanitized_name($category);
        $title = ucfirst($category);
        $answerfield = "{$fieldname}___lawful_basis";
        $s .=<<<EOM
<div class="recap-fieldcontainer" id="$category">
<h3>$title</h3>
<div class="box box-answer previous-answer recap-answer" data-field="{$answerfield}">
Lawful basis for processing $category:  {$lawful_bases[$answer[$answerfield]]}
</div>
</div>
EOM;
    }
    $s .= "</div>";
    return $s;


}

?>
