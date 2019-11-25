<?php

define('ROOT', dirname(__DIR__));
define('DS', '/');
include __DIR__ . "/../lib/vendor/autoload.php";
include __DIR__ . "/../lib/twigmanager.class.php";
include __DIR__ . "/../lib/functions.php";

use PHPUnit\Framework\TestCase;

class EditFieldTest extends TestCase {

    public function testAnswerField() {
        $output = injectAnswerField("stuff\n[--answer--]\nmore stuff", "answer");
        
        $this->assertSame($output, "stuff\n<textarea id=\"answer\" name=\"answer\" class=\"form-control\" rows=\"8\"></textarea>\nmore stuff");
    }
    public function testAnswerFieldWithOriginal() {
        $original = array('answer' => 'foo');
        $output = injectAnswerField("stuff\n[--answer--]\nmore stuff", "answer", $original);
        
        $this->assertSame($output, "stuff\n<textarea id=\"answer\" name=\"answer\" class=\"form-control\" rows=\"8\">foo</textarea>\nmore stuff");
    }

    public function testMultipleAnswer() {
        $origin = array();
        $output = injectMultipleAnswerField("stuff\n[--multiple-answer-0--]\nsection\n[--multiple-answer-1--]\nmore stuff", $origin);
        
        $this->assertSame(
            $output,
            "stuff\n<textarea id=\"multianswer-0\" name=\"multianswer[0]\" class=\"form-control\" rows=\"8\"></textarea>\nsection\n<textarea id=\"multianswer-1\" name=\"multianswer[1]\" class=\"form-control\" rows=\"8\"></textarea>\nmore stuff"
            );

    }
    public function testMultipleAnswerWithOriginal() {
        $origin = array('multianswer' => array(0 => 'foo', 1 => 'bar'));
        $output = injectMultipleAnswerField("stuff\n[--multiple-answer-0--]\nsection\n[--multiple-answer-1--]\nmore stuff", $origin);
        
        $this->assertSame(
            $output,
            "stuff\n<textarea id=\"multianswer-0\" name=\"multianswer[0]\" class=\"form-control\" rows=\"8\">foo</textarea>
section
<textarea id=\"multianswer-1\" name=\"multianswer[1]\" class=\"form-control\" rows=\"8\">bar</textarea>\nmore stuff"
            );

    }

    public function testBox() {
        $output = injectBox("stuff\n[--box|casestudy--]\nSomeText\nSomeText2\n[--endbox--]\nmore stuff");
        
        $this->assertCount(2, $output);
        // strip random IDs from output for test comparison
        $this->assertSame($output['content'], "stuff\n\nmore stuff");
        $this->assertSame($output['boxes'][0], "<div class=\"box box-casestudy\"><h3>case study</h3>\nSomeText\nSomeText2\n</div>");
        
    }
    
    public function testChoiceButton() {
        $output = injectChoiceButtons("stuff\n[--choicebutton|foo|Foo--]\nmore stuff");
        
        $this->assertSame(
            $output,
            "stuff\n<a href=\"#\" class=\"btn btn-alidade btn-lg picker\" data-target=\"#foo\">Foo</a>&nbsp;\nmore stuff"
        );
        
    }
    
    public function testChoicePanels() {
        $output = injectChoicePanels("stuff\n[--choicepanel|foo--]Some Stuff[--endchoicepanel--]\nmore stuff");
        
        $this->assertSame(
            $output,
            "stuff\n<div class=\"row hide picks\" id=\"foo\">Some Stuff</div>\nmore stuff"
        );
    }

    public function testNamedChoicePanels() {
        $output = injectChoicePanels("stuff\n[--choicepanel|foo--]Some Stuff[--endchoicepanel|foo--]\nmore stuff");

        $this->assertSame(
            $output,
            "stuff\n<div class=\"row hide picks\" id=\"foo\">Some Stuff</div>\nmore stuff"
        );
    }

    public function testNamedChoicePanelsBad() {
        $output = injectChoicePanels("stuff\n[--choicepanel|foo--]Some Stuff[--endchoicepanel|bar--]\nmore stuff");

        $this->assertNotSame(
            $output,
            "stuff\n<div class=\"row hide picks\" id=\"foo\">Some Stuff</div>\nmore stuff"
        );
    }
    public function testNamedChoicePanelsNested() {
        $output = injectChoicePanels("stuff\n[--choicepanel|foo--]Some Stuff [--choicepanel|bar--]  [--endchoicepanel|bar--]  [--endchoicepanel|foo--]\nmore stuff");

        $this->assertSame(
            $output,
            "stuff\n<div class=\"row hide picks\" id=\"foo\">Some Stuff <div class=\"row hide picks\" id=\"bar\">  </div>  </div>\nmore stuff"
        );
    }

    public function testChoicePanelsMultiline() {
        $output = injectChoicePanels("stuff\n[--choicepanel|foo1--]\nSome Stuff\n[--endchoicepanel--]\nmore stuff");
        
        $this->assertSame(
            $output,
            "stuff\n<div class=\"row hide picks\" id=\"foo1\">\nSome Stuff\n</div>\nmore stuff"
        );
    }
    
    public function testRadioButtons() {
        $output = injectRadioButtons("stuff\n[--radio|blark|foo|Foo--]\n[--radio|blark|bar|Bar--]\nmore stuff");
        
        $this->assertSame(
            $output,
            "stuff\n<div class=\"radio\"><label><input id=\"choice-foo\" name=\"blark\"  class=\"choice\" type=\"radio\" value=\"foo\"> Foo</label></div>
<div class=\"radio\"><label><input id=\"choice-bar\" name=\"blark\"  class=\"choice\" type=\"radio\" value=\"bar\"> Bar</label></div>\nmore stuff"
        );
    }
    
    public function testRadioButtonsWithOriginal() {
        $original = array('blark' => 'foo');
        $output = injectRadioButtons("stuff\n[--radio|blark|foo|Foo--]\n[--radio|blark|bar|Bar--]\nmore stuff", $original);
        
        $this->assertSame(
            $output,
            "stuff\n<div class=\"radio\"><label><input id=\"choice-foo\" name=\"blark\" checked class=\"choice\" type=\"radio\" value=\"foo\"> Foo</label></div>
<div class=\"radio\"><label><input id=\"choice-bar\" name=\"blark\"  class=\"choice\" type=\"radio\" value=\"bar\"> Bar</label></div>\nmore stuff"
        );
    }
    
    public function testCheckBoxes() {
        $output = injectCheckboxes("stuff\n[--check|opt1|My stuff--]\n[--check|opt2|Other stuff--]\nmore stuff");
        
        $this->assertSame(
            $output,
            "stuff
<div class=\"checkbox\"><input id=\"check-opt1\"  name=\"opt1\" type=\"checkbox\" value=\"My stuff\"> My stuff</div>
<div class=\"checkbox\"><input id=\"check-opt2\"  name=\"opt2\" type=\"checkbox\" value=\"Other stuff\"> Other stuff</div>
more stuff"
        );
    }

    public function testCheckBoxesWithOriginal() {
        $original = array('opt1' => 'My stuff');
        $output = injectCheckboxes("stuff\n[--check|opt1|My stuff--]\n[--check|opt2|Other stuff--]\nmore stuff", $original);
        
        $this->assertSame(
            $output,
            "stuff
<div class=\"checkbox\"><input id=\"check-opt1\" checked name=\"opt1\" type=\"checkbox\" value=\"My stuff\"> My stuff</div>
<div class=\"checkbox\"><input id=\"check-opt2\"  name=\"opt2\" type=\"checkbox\" value=\"Other stuff\"> Other stuff</div>
more stuff"
        );
    }
    
    public function testGetPlaceholders() {
        $result = getPlaceholders(" stuff [--box|foo|bar--] other [--check|opt|My Stuff--] other");
        
        $this->assertSame(
            $result,
            array(
                array('box','foo','bar'),
                array('check','opt','My Stuff')
                )
            );
            
    }


    public function testArray() {
        $result = injectArray("stuff\n[--array|items--]\nstuff",
            array(
                'items' => array(
                    'item1','item2','item3'
                    )
                )
            );

        $exp = <<<EOM
stuff
<div id="items"></div>
<script type="text/javascript">
$('#items').alpaca({
    data: ["item1","item2","item3"],
    options: {
        name: "items",
        id: "items"
    }
});
</script>
stuff
EOM;

        $this->assertSame($result, $exp);
    }


    function testSplitBoxes() {
        $orig =<<< EOM
<p>
some leading content
</p>
<p>[--box|info--]
my box content
[--endbox--]
</p>
more stuff
EOM;

    $content = splitBoxes($orig);

    $this->assertCount(2, $content);
    $this->assertTrue(array_key_exists('content', $content[0]));
    $this->assertTrue(array_key_exists('box', $content[0]));
    $this->assertEquals($content[0]['box']->type, "info");
    $this->assertEquals($content[0]['box']->text, "\nmy box content\n");

    $this->assertFalse(array_key_exists('box', $content[1]));
    $this->assertEquals($content[1]['content'], "\nmore stuff");

    }

    function testSplit2Boxes() {
            $orig =<<< EOM
<p>
some leading content
</p>
<p>[--box|info--]
my box content
[--endbox--]
</p>
<p>
some more leading content
</p>
<p>[--box|info--]
my second box content
[--endbox--]
</p>
more stuff
EOM;

        $content = splitBoxes($orig);

        $this->assertCount(3, $content);
        $this->assertTrue(array_key_exists('content', $content[0]));
        $this->assertTrue(array_key_exists('box', $content[0]));
        $this->assertEquals($content[0]['box']->type, "info");
        $this->assertEquals($content[0]['box']->text, "\nmy box content\n");

        $this->assertTrue(array_key_exists('content', $content[1]));
        $this->assertTrue(array_key_exists('box', $content[1]));

        $this->assertFalse(array_key_exists('box', $content[2]));
        $this->assertEquals($content[2]['content'], "\nmore stuff");
    }

    function testSplitNoBoxes() {
            $orig =<<< EOM
<p>
some leading content
</p>
<p>
EOM;

        $content = splitBoxes($orig);

        $this->assertCount(1, $content);
        $this->assertTrue(array_key_exists('content', $content[0]));
        $this->assertFalse(array_key_exists('box', $content[0]));

    }
}
