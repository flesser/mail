<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Thomas Müller <thomas.mueller@tmit.eu>
 *
 * ownCloud - Mail
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\Mail\Tests\Db;

use OC\AppFramework\Db\Db;
use Test\TestCase;
use OCA\Mail\Db\CollectedAddressMapper;
use OCA\Mail\Db\CollectedAddress;

/**
 * Class CollectedAddressMapperTest
 *
 * @group DB
 *
 * @package OCA\Mail\Tests\Db
 */
class CollectedAddressMapperTest extends TestCase {

	/** @var \OCP\IDBConnection */
	private $db;
	private $userId = 'testuser';
	/** @var CollectedAddressMapper */
	private $mapper;
	/** @var CollectedAddress */
	private $address1;
	/** @var CollectedAddress */
	private $address2;

	protected function setUp() {
		parent::setUp();

		$this->db = \OC::$server->getDatabaseConnection();
		$this->mapper = new CollectedAddressMapper(new Db($this->db));

		$this->address1 = new CollectedAddress();
		$this->address1->setEmail('user1@example.com');
		$this->address1->setUserId($this->userId);

		$this->address2 = new CollectedAddress();
		$this->address2->setEmail('user2@example.com');
		$this->address2->setUserId($this->userId);

		$sql = 'INSERT INTO *PREFIX*mail_collected_addresses (`email`, `user_id`) VALUES (?, ?)';
		$stmt = $this->db->prepare($sql);

		$stmt->execute([
			$this->address1->getEmail(),
			$this->address1->getUserId(),
		]);
		$this->address1->setId($this->db->lastInsertId('PREFIX*mail_collected_addresses'));
		$stmt->execute([
			$this->address2->getEmail(),
			$this->address2->getUserId(),
		]);
		$this->address2->setId($this->db->lastInsertId('PREFIX*mail_collected_addresses'));
	}

	protected function tearDown() {
		parent::tearDown();

		$sql = 'DELETE FROM *PREFIX*mail_collected_addresses WHERE `id` = ?';
		$stmt = $this->db->prepare($sql);
		if (!empty($this->address1)) {
			$stmt->execute([$this->address1->getId()]);
		}
		if (!empty($this->address2)) {
			$stmt->execute([$this->address2->getId()]);
		}
	}

	public function matchingData() {
		return [
			['user1@example.com', ['user1@example.com']],
			['ser', ['user1@example.com', 'user2@example.com']],
		];
	}

	/**
	 * @dataProvider matchingData
	 */
	public function testFindMatching($query, $result) {
		$matches = $this->mapper->findMatching($this->userId, $query);

		$this->assertCount(count($result), $matches);
		$i = 0;
		foreach ($matches as $match) {
			$this->assertInstanceOf('\OCA\Mail\Db\CollectedAddress', $match);
			$this->assertTrue(in_array($match->getEmail(), $result));
			$this->assertEquals($this->userId, $match->getUserId());
			$i++;
		}
	}

	public function existsData() {
		return [
			['user1@example.com', true],
			['user3@example.com', false],
		];
	}

	/**
	 * @dataProvider existsData
	 */
	public function testExists($email, $expected) {
		$actual = $this->mapper->exists($this->userId, $email);

		$this->assertSame($expected, $actual);
	}

}
