<?php
namespace ah;

require_once dirname(__FILE__).'/../init.php';

/**
 * Test class for Validator.
 * Generated by PHPUnit on 2011-05-05 at 22:17:36.
 */
class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Validator
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Validator;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function conditionProvider()
    {
        return array(
            array(
                // rule
                array(
                    'hoge' => 'required',
                    'fuga' => array('range' => array(-1, 2), 'numeric', 'equal' => 1),
                ),
                // params
                array(
                    'hoge' => 'value',
                    'fuga' => '1',
                ),
                // validAll
                true
            ),
            array(
                // rule
                array(
                    'hoge' => array('required', 'min' => '3', 'equalTo' => 'piyo'),
                    'fuga' => array('equalTo' => array('hoge', true), 'int', 'numeric'),
                    'piyo' => array('equal' => array(3, true)),
                ),
                // params
                array(
                    'hoge' => 3,
                    'fuga' => 3,
                    'piyo' => '3',
                ),
                // validAll
                false
            ),
        );
    }

    /**
     * @dataProvider conditionProvider
     */
    public function testValidate($rule, $params)
    {
        $Validator = $this->object->validate($rule, $params);

        $this->assertAttributeSame($params, '_rawparams', $this->object);
        $this->assertAttributeInternalType('array', '_result', $this->object);
        $this->assertSame($Validator, $this->object);
    }

    /**
     * @dataProvider conditionProvider
     */
    public function testIsValid($rule, $params)
    {
        $this->object->validate($rule, $params);

        $this->assertTrue($this->object->isValid('hoge'));
        $this->assertTrue($this->object->isValid('fuga'));
        $this->assertFalse($this->object->isValid('piyo'));
    }

    /**
     * @dataProvider conditionProvider
     */
    public function testIsValidAll($rule, $params, $validAll)
    {
        $this->object->validate($rule, $params);

        $this->assertSame($validAll, $this->object->isValidAll());
    }

    public function testRequired()
    {
        $this->assertTrue($this->object->fire('required', true, array()));
        $this->assertTrue($this->object->fire('required', '0', array()));

        $this->assertFalse($this->object->fire('required', null, array()));
        $this->assertFalse($this->object->fire('required', false, array()));
        $this->assertFalse($this->object->fire('required', '', array()));
        $this->assertFalse($this->object->fire('required', 0, array()));
    }

    public function testNotEmpty()
    {
        $this->assertTrue($this->object->fire('notEmpty', 1, array()));
        $this->assertTrue($this->object->fire('notEmpty', 'a', array()));
        $this->assertTrue($this->object->fire('notEmpty', true, array()));

        $this->assertFalse($this->object->fire('notEmpty', 0, array()));
        $this->assertFalse($this->object->fire('notEmpty', '0', array()));
        $this->assertFalse($this->object->fire('notEmpty', '', array()));
        $this->assertFalse($this->object->fire('notEmpty', false, array()));
    }

    public function testMin()
    {
        $this->assertTrue($this->object->fire('min', 3, array(2)));
        $this->assertTrue($this->object->fire('min', 3, array(-2)));
        $this->assertTrue($this->object->fire('min', '3', array('-21')));

        $this->assertFalse($this->object->fire('min', 2, array(4)));
        $this->assertFalse($this->object->fire('min', -3, array(-2)));
        $this->assertFalse($this->object->fire('min', '-3', array('-2')));
    }

    public function testMax()
    {
        $this->assertTrue($this->object->fire('max', 2, array(4)));
        $this->assertTrue($this->object->fire('max', -3, array(-2)));
        $this->assertTrue($this->object->fire('max', '-3', array('-2')));

        $this->assertFalse($this->object->fire('max', 3, array(2)));
        $this->assertFalse($this->object->fire('max', 3, array(-2)));
        $this->assertFalse($this->object->fire('max', '3', array('-2')));
    }

    public function testRange()
    {
        $this->assertTrue($this->object->fire('range', 2, array(1, 2)));
        $this->assertTrue($this->object->fire('range', -3, array(-3, 123456789)));
        $this->assertTrue($this->object->fire('range', '-3', array('-5', '-1')));

        $this->assertFalse($this->object->fire('range', 3, array(1, 2)));
        $this->assertFalse($this->object->fire('range', 3, array(-2, -2)));
        $this->assertFalse($this->object->fire('range', '3', array('-2', '2')));
    }

    public function testAlpha()
    {
        $this->assertTrue($this->object->fire('alpha', 'abc', array()));
        $this->assertTrue($this->object->fire('alpha', 'a', array()));

        $this->assertFalse($this->object->fire('alpha', 'a1b', array()));
        $this->assertFalse($this->object->fire('alpha', '', array()));
        $this->assertFalse($this->object->fire('alpha', false, array()));
        $this->assertFalse($this->object->fire('alpha', 0, array()));
        $this->assertFalse($this->object->fire('alpha', '2', array()));
        $this->assertFalse($this->object->fire('alpha', 'http://', array()));
    }

    public function testInt()
    {
        $this->assertTrue($this->object->fire('int', 0, array()));
        $this->assertTrue($this->object->fire('int', 1, array()));
        $this->assertTrue($this->object->fire('int', -2, array()));

        $this->assertFalse($this->object->fire('int', 0.032, array()));
        $this->assertFalse($this->object->fire('int', '1', array()));
        $this->assertFalse($this->object->fire('int', +0123.45e6, array()));
    }

    public function testFloat()
    {
        $this->assertTrue($this->object->fire('float', 1.234, array()));
        $this->assertTrue($this->object->fire('float', 1.2e3, array()));
        $this->assertTrue($this->object->fire('float', 7E-10, array()));

        $this->assertFalse($this->object->fire('float', '1.234', array()));
        $this->assertFalse($this->object->fire('float', '1.2e3', array()));
        $this->assertFalse($this->object->fire('float', '7E-10', array()));
    }

    public function testNumeric()
    {
        $this->assertTrue($this->object->fire('numeric', 0, array()));
        $this->assertTrue($this->object->fire('numeric', 12345, array()));
        $this->assertTrue($this->object->fire('numeric', '12345', array()));
        $this->assertTrue($this->object->fire('numeric', '1.2345', array()));
        $this->assertTrue($this->object->fire('numeric', +0123.45e6, array()));
        $this->assertTrue($this->object->fire('numeric', '+0123.45e6', array()));

        $this->assertFalse($this->object->fire('numeric', '123abc', array()));
    }

    public function testAlnum()
    {
        $this->assertTrue($this->object->fire('alnum', 'abc', array()));
        $this->assertTrue($this->object->fire('alnum', '123', array()));
        $this->assertTrue($this->object->fire('alnum', 'abc123', array()));
        $this->assertTrue($this->object->fire('alnum', 0, array()));
        $this->assertTrue($this->object->fire('alnum', 123, array()));

        $this->assertFalse($this->object->fire('alnum', 'http://123', array()));
        $this->assertFalse($this->object->fire('alnum', '', array()));
        $this->assertFalse($this->object->fire('alnum', false, array()));
    }

    public function testRegex()
    {
        $this->assertTrue($this->object->fire('regex', 'abc123', array('/\w{3}\d{3}/')));
        $this->assertTrue($this->object->fire('regex', 'abcdef', array('/\w+/')));

        $this->assertFalse($this->object->fire('regex', '', array('/\w/')));
        $this->assertFalse($this->object->fire('regex', 'abc', array('/\d+/')));
    }

    public function testDate()
    {
        $this->assertTrue($this->object->fire('date', '1001.01.01', array()));
        $this->assertTrue($this->object->fire('date', '1001/01/01', array()));
        $this->assertTrue($this->object->fire('date', '1001-01-01', array()));

        $this->assertFalse($this->object->fire('date', 0, array()));
        $this->assertFalse($this->object->fire('date', 1001-01-01, array()));
    }
}
?>