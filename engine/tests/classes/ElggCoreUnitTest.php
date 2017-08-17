<?php

use Elgg\Database\Seeds\Seedable;
use Elgg\Database\Seeds\Seeding;
use Elgg\Testing;

/**
 * Simpletest Base Unit Test
 *
 * Extend this class to run tests in a simpletest suite
 *
 * DO NOT WRITE ANY MORE TESTS EXTENDING THIS CLASS
 * USE PHPUNIT INTEGRATION TESTS INSTEAD
 * SIMPLETEST SUITE WILL PROBABLY GO AWAY AFTER ELGG 3.0
 */
abstract class ElggCoreUnitTest extends UnitTestCase implements Seedable, \Elgg\Testable {

	use Seeding;
	use Testing;

	/**
	 * Returns names of services that should be reset when BaseTestCase::reset() is called
	 * @return mixed
	 */
	public static function getResettableServices() {
		return [];
	}

	/**
	 * Reset the application to original state without bootstrapping it all over again
	 * @return void
	 */
	public static function reset() {
		foreach (static::getResettableServices() as $service) {
			_elgg_services()->reset($service);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	final public function setUp() {
		static::reset();

		// We do not want overflowing ignored access
		$this->assertFalse((bool) elgg_get_ignore_access());

		// We do not want overflowing show hidden status
		$this->assertFalse((bool) access_get_show_hidden_status());

		// Make sure the application has been bootstrapped correctly
		$this->assertIsA(elgg(), \Elgg\Application::class);
		$this->assertIsA(_elgg_services(), \Elgg\Di\ServiceProvider::class);
		$this->assertIsA(_elgg_services()->config, \Elgg\Config::class);

		// Simpletest suite runs with an admin user logged in
		$this->assertTrue(elgg_is_admin_logged_in());

		$this->up();
	}

	/**
	 * {@inheritdoc}
	 */
	final public function tearDown() {
		$this->down();

		// We do not want overflowing ignored access
		$this->assertFalse((bool) elgg_get_ignore_access());

		// We do not want overflowing show hidden status
		$this->assertFalse((bool) access_get_show_hidden_status());

		// Simpletest suite runs with an admin user logged in
		$this->assertTrue(elgg_is_admin_logged_in());
	}

	/**
	 * Called after setUp() method and can be used by test cases to setup their test logic
	 * @return mixed
	 */
	abstract function up();

	/**
	 * Called before tearDown() method and can be used by test cases to clear their test logic
	 * @return mixed
	 */
	abstract function down();

	/**
	 * Will trigger a pass if the two entity parameters have
	 * the same "value" and same type. Otherwise a fail.
	 *
	 * @param mixed  $first   Entity to compare.
	 * @param mixed  $second  Entity to compare.
	 * @param string $message Message to display.
	 *
	 * @return boolean
	 */
	public function assertIdenticalEntities(\ElggEntity $first, \ElggEntity $second, $message = '%s') {
		if (!($res = $this->assertIsA($first, '\ElggEntity'))) {
			return $res;
		}
		if (!($res = $this->assertIsA($second, '\ElggEntity'))) {
			return $res;
		}
		if (!($res = $this->assertEqual(get_class($first), get_class($second)))) {
			return $res;
		}

		return $this->assert(new \Elgg\Simpletest\IdenticalEntityExpectation($first), $second, $message);
	}

	/**
	 * Replace the current user session
	 *
	 * @param ElggUser $user New user to login as (null to log out)
	 *
	 * @return ElggUser|null Removed session user (or null)
	 */
	public function replaceSession(ElggUser $user = null) {
		$session = elgg_get_session();
		$old = $session->getLoggedInUser();
		if ($user) {
			$session->setLoggedInUser($user);
		} else {
			$session->removeLoggedInUser();
		}

		return $old;
	}

}

