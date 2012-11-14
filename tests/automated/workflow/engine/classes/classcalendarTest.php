<?php
require_once PATH_TRUNK . 'gulliver/thirdparty/smarty/libs/Smarty.class.php';
require_once PATH_TRUNK . 'gulliver/system/class.xmlform.php';
require_once PATH_TRUNK . 'gulliver/system/class.xmlDocument.php';
require_once PATH_TRUNK . 'gulliver/system/class.form.php';
require_once PATH_TRUNK . 'gulliver/system/class.dbconnection.php';
require_once PATH_TRUNK . 'gulliver/thirdparty/propel/Propel.php';
require_once PATH_TRUNK . 'gulliver/thirdparty/creole/Creole.php';
require_once PATH_TRUNK . 'gulliver/thirdparty/pear/PEAR.php';
require_once PATH_TRUNK . 'workflow/engine/classes/class.calendar.php';

/**
 * Generated by ProcessMaker Test Unit Generator on 2012-07-12 at 22:32:26.
*/

class classcalendarTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var calendar
    */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
    */
    protected function setUp()
    {
        $userUid = '';
        $proUid  = '';
        $tasUid  = '';
        //$this->object = new calendar($userUid, $proUid, $tasUid);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
    */
    protected function tearDown()
    {
    }

    /**
     * This is the default method to test, if the class still having
     * the same number of methods.
    */
    public function testNumberOfMethodsInThisClass()
    {
        $methods = get_class_methods('calendar');
        $this->assertTrue( count($methods) == 56);
    }

    /**
    * @covers calendar::calendar
    * @todo   Implement testcalendar().
    */
    public function testcalendar()
    {
        //$methods = get_class_methods($this->object);
        $methods = get_class_methods('calendar');
        $this->assertTrue( in_array('calendar', $methods ), 'exists method calendar' );
        $r = new ReflectionMethod('calendar', 'calendar');
        $params = $r->getParameters();
        $this->assertTrue( $params[0]->getName() == 'userUid');
        $this->assertTrue( $params[0]->isArray() == false);
        $this->assertTrue( $params[0]->isOptional () == true);
        $this->assertTrue( $params[0]->getDefaultValue() == '');
        $this->assertTrue( $params[1]->getName() == 'proUid');
        $this->assertTrue( $params[1]->isArray() == false);
        $this->assertTrue( $params[1]->isOptional () == true);
        $this->assertTrue( $params[1]->getDefaultValue() == '');
        $this->assertTrue( $params[2]->getName() == 'tasUid');
        $this->assertTrue( $params[2]->isArray() == false);
        $this->assertTrue( $params[2]->isOptional () == true);
        $this->assertTrue( $params[2]->getDefaultValue() == '');
    }

    /**
    * @covers calendar::addCalendarLog
    * @todo   Implement testaddCalendarLog().
    */
    public function testaddCalendarLog()
    {
        //$methods = get_class_methods($this->object);
        $methods = get_class_methods('calendar');
        $this->assertTrue( in_array('addCalendarLog', $methods ), 'exists method addCalendarLog' );
        $r = new ReflectionMethod('calendar', 'addCalendarLog');
        $params = $r->getParameters();
        $this->assertTrue( $params[0]->getName() == 'msg');
        $this->assertTrue( $params[0]->isArray() == false);
        $this->assertTrue( $params[0]->isOptional () == false);
    }

    /**
    * @covers calendar::setupCalendar
    * @todo   Implement testsetupCalendar().
    */
    public function testsetupCalendar()
    {
        //$methods = get_class_methods($this->object);
        $methods = get_class_methods('calendar');
        $this->assertTrue( in_array('setupCalendar', $methods ), 'exists method setupCalendar' );
        $r = new ReflectionMethod('calendar', 'setupCalendar');
        $params = $r->getParameters();
        $this->assertTrue( $params[0]->getName() == 'userUid');
        $this->assertTrue( $params[0]->isArray() == false);
        $this->assertTrue( $params[0]->isOptional () == false);
        $this->assertTrue( $params[1]->getName() == 'proUid');
        $this->assertTrue( $params[1]->isArray() == false);
        $this->assertTrue( $params[1]->isOptional () == false);
        $this->assertTrue( $params[2]->getName() == 'tasUid');
        $this->assertTrue( $params[2]->isArray() == false);
        $this->assertTrue( $params[2]->isOptional () == false);
    }

    /**
    * @covers calendar::getNextValidBusinessHoursRange
    * @todo   Implement testgetNextValidBusinessHoursRange().
    */
    public function testgetNextValidBusinessHoursRange()
    {
        //$methods = get_class_methods($this->object);
        $methods = get_class_methods('calendar');
        $this->assertTrue( in_array('getNextValidBusinessHoursRange', $methods ), 'exists method getNextValidBusinessHoursRange' );
        $r = new ReflectionMethod('calendar', 'getNextValidBusinessHoursRange');
        $params = $r->getParameters();
        $this->assertTrue( $params[0]->getName() == 'date');
        $this->assertTrue( $params[0]->isArray() == false);
        $this->assertTrue( $params[0]->isOptional () == false);
        $this->assertTrue( $params[1]->getName() == 'time');
        $this->assertTrue( $params[1]->isArray() == false);
        $this->assertTrue( $params[1]->isOptional () == false);
    }

  }
