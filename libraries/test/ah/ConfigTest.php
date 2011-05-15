<?php
namespace ah;

require_once dirname(__FILE__).'/../init.php';

/**
 * Test class for Config.
 * Generated by PHPUnit on 2011-05-05 at 22:16:46.
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @todo Implement testLoad().
     */
    public function testLoad()
    {
        $conf = Config::load('map', 'GET');
        $mock = array(
            '/mock' => array('foo', 'bar'),
            '/sub/mock' => array('foo', 'bar')
        );
        $this->assertSame($mock, $conf);
    }
}
?>