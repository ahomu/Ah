<?php
namespace ah;

require_once dirname(__FILE__).'/../init.php';

require_once DIR_LIB.'/test/ah/action/Mock.php';
require_once DIR_LIB.'/test/ah/action/sub/Mock.php';

/**
 * Test class for Resolver.
 * Generated by PHPUnit on 2011-05-05 at 12:40:33.
 */
class ResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Resolver
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
        $cachePath = DIR_TMP;
        \Util_Dir::removeFilesRecursivity($cachePath);
    }

    public function testExternal()
    {
        ob_start();
        $Action = Resolver::external('/mock', 'GET');
        $obResBody = ob_get_clean();

        // 出力があるべき
        $this->assertNotEmpty($obResBody);

        // 基本Actionが継承されているべき
        $this->assertInstanceOf('ah\action\Base', $Action);

        // 正しいアクションが生成されるべき
        $this->assertInstanceOf('app\action\Mock', $Action);

        ob_start();
        $Action = Resolver::external('/mock', 'POST');
        $obResBody = ob_get_clean();
        $code   = $Action->Response->getStatusCode();

        // 未定義メソッドへのリクエストに対して例外を投げるべき
        $this->assertEquals(405, $code);
    }

    public function testInternal()
    {
        ob_start();
        $Action = Resolver::internal('/sub/mock', 'GET');
        $obResBody = ob_get_clean();

        // なにも出力されないべき
        $this->assertEmpty($obResBody);

        // 正しいアクションが生成されるべき
        $this->assertInstanceOf('\app\action\sub\Mock', $Action);
    }

    public function testIncludes()
    {
        ob_start();
        $Response = Resolver::includes('/mock', 'GET');
        $obResBody = ob_get_clean();

        // なにも出力されないべき
        $this->assertEmpty($obResBody);

        // 文字列が返却されるべき
        $this->assertInternalType('string', $Response);
    }

    public function testRedirect()
    {
        $this->markTestSkipped(
          'リダイレクトメソッドのテストをスキップする.'
        );
    }

    public function testResolvings()
    {
        // 1階層パス + 2引数
        $Action = Resolver::internal('/mock/hoge/fuga', 'GET');
        $this->assertInstanceOf('app\action\Mock', $Action);
        $this->assertEquals('hoge', $Action->Params->get('foo'));
        $this->assertEquals('fuga', $Action->Params->get('bar'));

        // 2階層パス + 2引数
        $Action = Resolver::internal('/sub/mock/hoge/fuga', 'GET');
        $this->assertInstanceOf('app\action\sub\Mock', $Action);
        $this->assertEquals('hoge', $Action->Params->get('foo'));
        $this->assertEquals('fuga', $Action->Params->get('bar'));
    }
}
?>