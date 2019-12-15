<?php

if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__));
    define('DS', '/');
}
include_once __DIR__ . "/../lib/vendor/autoload.php";
require_once __DIR__ . "/../lib/twigmanager.class.php";
include_once __DIR__ . "/../lib/functions.php";

use PHPUnit\Framework\TestCase;

class WarningTest extends TestCase {

    function setUp() {
        $this->warnings = array(
            array(
                'slide' => '1.1',
                'warning' => 'Some warning Text',
                'criteria' => array(
                    array(
                        'name' => 'multianswer-0',
                        'is' => 'empty'
                    )
                )
            ),
            array(
                'slide' => '1.2',
                'warning' => 'Some warning Text',
                'criteria' => array(
                    array(
                        'name' => 'choice',
                        'value' => 'yes'
                    )
                )
            ),
            array(
                'slide' => '1.3',
                'warning' => 'Some warning Text',
                'criteria' => array(
                    array(
                        'name' => 'multianswer-0',
                        'is' => 'empty'
                    ),
                    array(
                        'name' => 'choice',
                        'value' => 'yes'
                    )
                )
            ),
            array(
                'slide' => '1.4',
                'warning' => 'Some warning Text',
                'criteria' => array(
                    array(
                    'all' => array('field1','field2','field3'),
                    'is' => 'empty'
                    )
                )
            ),
            array(
                'slide' => '1.5',
                'warning' => 'Some warning Text',
                'criteria' => array(
                    array(
                        'all' => array('field1','field2','field3'),
                        'is' => 'not-empty'
                    )
                )
            )
        );
    }

    function testFindWarnings() {
        $warnings = $this->warnings;

        $found = findWarnings($warnings, "1.1");
        $this->assertCount(1, $found);
        $this->assertEquals($found[0]['slide'], "1.1");

        $found = findWarnings($warnings, "1.2");
        $this->assertCount(1, $found);
        $this->assertEquals($found[0]['slide'], "1.2");
    }

    function testEvaluateEmpty() {
        $warnings = $this->warnings;
        $warning = $warnings[0];

        $result = evaluateWarning($warning, array('multianswer' => array("")));
        $this->assertTrue($result);
        $result = evaluateWarning($warning, array('multianswer' => array("    ")));
        $this->assertTrue($result);
    }

    function testEvaluateNotEmpty() {
        $warnings = $this->warnings;
        $warning = $warnings[0];

        $result = evaluateWarning($warning, array('multianswer' => array("some stuff")));
        $this->assertFalse($result);
    }

    function testEvaluateEqualsValue() {
        $warnings = findWarnings($this->warnings, "1.2");

        $result = evaluateWarning($warnings[0], array('choice' => "yes"));
        $this->assertTrue($result);
    }

    function testEvaluateNotEqualsValue() {
        $warnings = findWarnings($this->warnings, "1.2");

        $result = evaluateWarning($warnings[0], array('choice' => "no"));
        $this->assertFalse($result);
    }

    function testEvaluateMultiAllMatch() {
        $warnings = findWarnings($this->warnings, "1.3");

        $result = evaluateWarning($warnings[0], array('choice' => "yes", 'multianswer' => array("")));
        $this->assertTrue($result);
    }

    function testEvaluateMultiFirstNotMatch() {
        $warnings = findWarnings($this->warnings, "1.3");

        $result = evaluateWarning($warnings[0], array('choice' => "no", 'multianswer' => array("")));
        $this->assertFalse($result);
    }

    function testEvaluateMultiSecondNotMatch() {
        $warnings = findWarnings($this->warnings, "1.3");

        $result = evaluateWarning($warnings[0], array('choice' => "yes", 'multianswer' => array("stuff")));
        $this->assertFalse($result);
    }

    function testEvaluateMultiNoneMatch() {
        $warnings = findWarnings($this->warnings, "1.3");

        $result = evaluateWarning($warnings[0], array('choice' => "no", 'multianswer' => array("stuff")));
        $this->assertFalse($result);
    }

    function testEvaluateAllEmpty() {
        // test fails if any of the fields listed in 'any' are empty
        $warnings = findWarnings($this->warnings, "1.4");

        $result = evaluateWarning($warnings[0], array('field1' => "", 'field2' => '', 'field3' => ''));
        $this->assertTrue($result);
    }
    function testEvaluateAllEmptyFail() {
        // test fails if any of the fields listed in 'any' are empty
        $warnings = findWarnings($this->warnings, "1.4");

        $result = evaluateWarning($warnings[0], array('field1' => '', 'field2' => 'set', 'field3' => 'set'));
        $this->assertFalse($result);
    }
    function testEvaluateAllNotEmpty() {
        // test fails if any of the fields listed in 'any' are empty
        $warnings = findWarnings($this->warnings, "1.5");

        $result = evaluateWarning($warnings[0], array('field1' => "set", 'field2' => 'set', 'field3' => 'set'));
        $this->assertTrue($result);
    }
    function testEvaluateAllNotEmptyFail() {
        // test fails if any of the fields listed in 'any' are empty
        $warnings = findWarnings($this->warnings, "1.5");

        $result = evaluateWarning($warnings[0], array('field1' => '', 'field2' => 'set', 'field3' => 'set'));
        $this->assertFalse($result);
    }

    function testLoad() {
        $data = loadWarnings();

        $this->assertTrue(is_array($data));
        $this->assertArrayHasKey("slide", $data[0]);
    }
}