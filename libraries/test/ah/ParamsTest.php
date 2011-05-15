<?php
namespace ah;

require_once dirname(__FILE__).'/../init.php';

/**
 * Test class for Params.
 * Generated by PHPUnit on 2011-05-05 at 22:16:53.
 */
class ParamsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Params
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Params(
            array('hoge', 'fuga'),
            array('hoge' => 'value1', 'fuga' => "<script>alert('Hello World');</script>", 'piyo' => 'value3'),
            null
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    public function testSet()
    {
        $this->assertTrue($this->object->set('hoge', 'new value'));
        $this->assertEquals('new value', $this->object->get('hoge'));

        $this->assertFalse($this->object->set('piyo', 'new value'));
    }

    public function testGet()
    {
        $this->assertEquals('value1', $this->object->get('hoge'));

        $this->assertEquals("<script>alert('Hello World');</script>", $this->object->get('fuga', true));
        $this->assertNotEquals("<script>alert('Hello World');</script>", $this->object->get('fuga'));

        $this->assertFalse($this->object->get('piyo'));
    }

    /**
     * @todo Implement testValidate().
     */
    public function testValidate()
    {
    }

    /**
     * @todo Implement testIsValidAll().
     */
    public function testIsValidAll()
    {
    }

    /**
     * @todo Implement testIsValid().
     */
    public function testIsValid()
    {
    }

    public function testToArray()
    {
        $this->assertSame(array('hoge' => 'value1', 'fuga' => "<script>alert('Hello World');</script>"), $this->object->toArray());
    }
}
?>