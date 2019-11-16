<?php

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
    return preg_replace('/[^\w\d]+/', '_', $value);
}

function customform($slide, $original, $project) {  // original json data

    # get previous answer data
    $Slide = new Slide();

    switch($slide) {
    case "2.2":
    case "2.3":
        $previous = $Slide->findPreviousAnswer($project, 2, 1);
        break;
    };
    $previousanswer = json_decode($previous->answer, TRUE);


    switch($slide) {
    case "2.2": return customform_2_2($original, $previousanswer); break;
    }

}

function customform_2_2($answer, $previousanswer) {
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

?>
