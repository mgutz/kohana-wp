<?php

require_once 'MustacheTest.php';
require_once 'PHPUnit/Framework.php';

/**
 * A PHPUnit test case for Mustache.php.
 *
 * This is a very basic, very rudimentary unit test case. It's probably more important to have tests
 * than to have elegant tests, so let's bear with it for a bit.
 *
 * This class assumes an example directory exists at `../examples` with the following structure:
 *
 * @code
 *    examples
 *        foo
 *            Foo.php
 *            foo.mustache
 *            foo.txt
 *        bar
 *            Bar.php
 *            bar.mustache
 *            bar.txt
 * @endcode
 *
 * To use this test:
 *
 *  1. {@link http://www.phpunit.de/manual/current/en/installation.html Install PHPUnit}
 *  2. run phpunit from the `test` directory:
 *        `phpunit MustacheTest`
 *  3. Fix bugs. Lather, rinse, repeat.
 *
 * @extends PHPUnit_Framework_TestCase
 */
class MustacheAutoloadTest extends MustacheTest {

	/**
	 * Test everything in the `examples` directory.
	 *
	 * @dataProvider getExamples
	 * @access public
	 * @param mixed $class
	 * @param mixed $template
	 * @param mixed $output
	 * @return void
	 */
	public function testExamples($class, $template, $output) {
		if ($class == 'Delimiters') {
			$this->markTestSkipped("Known issue: sections don't respect delimiter changes");
			return;
		}

		$m = new $class;
		$this->assertEquals($output, $m->render());
	}
}