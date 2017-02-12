<?php
require_once 'PHPUnit/Framework/TestCase.php';

use TwentyTwoEstore\Libs\JsonContentParser;

/**
 * JsonContentParser test case.
 */
class JsonContentParserTest extends PHPUnit_Framework_TestCase
{
	/**
	 *
	 * @var JsonContentParser
	 */
	private $JsonContentParser;

	private $inputOk = '[{"data":"2013-01-02","cena":165.83},{"data":"2013-01-03","cena":166.97}]';

	private $inputWrong = 'ada872yd(90shdn3';

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp()
	{
		parent::setUp ();

		$this->JsonContentParser = new JsonContentParser();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown()
	{
		$this->JsonContentParser = null;

		parent::tearDown ();
	}

	/**
	 * Constructs the test case.
	 */
	public function __construct()
	{
		require_once 'bootstrap.php';
	}

	/**
	 * Tests JsonContentParser->getContentFormat()
	 */
	public function testGetContentFormat()
	{
		$actual = $this->JsonContentParser->getContentFormat();
		$this->assertEquals('json', $actual);
	}

	/**
	 * Tests JsonContentParser->parse()
	 */
	public function testParse_Empty()
	{
		try{
			$this->JsonContentParser->parse('');
		} catch (\Exception $ex){
			$this->assertInstanceOf('\TwentyTwoEstore\Libs\ExecuteException', $ex);
			$this->assertTrue(strpos($ex->getMessage(), "can't be decoded or the encoded") !== false);
		}
	}

	/**
	 * Tests JsonContentParser->parse()
	 */
	public function testParse_Wrong()
	{
		try{
			$this->JsonContentParser->parse($this->inputWrong);
		} catch (\Exception $ex){
			$this->assertInstanceOf('\TwentyTwoEstore\Libs\ExecuteException', $ex);
			$this->assertTrue(strpos($ex->getMessage(), "can't be decoded or the encoded") !== false);
		}
	}

	/**
	 * Tests JsonContentParser->parse()
	 */
	public function testParse_Ok()
	{
		$actual = $this->JsonContentParser->parse($this->inputOk);
		$this->assertTrue(is_array($actual));
		$this->assertEquals(2, count($actual));
		$this->assertEquals('2013-01-02', $actual[0]['data']);
	}
}
