<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Christoph Wurst <wurst.christoph@gmail.com>
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
namespace OCA\Mail\Tests\Imap;

use OCA\Mail\Account;
use OCA\Mail\Db\MailAccount;
use OCA\Mail\Mailbox;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Account
	 */
	private static $account;

	/**
	 * @var string[]
	 */
	private static $createdMailboxes = [];

	public static function setUpBeforeClass() {
		$user = 'user@domain.tld';
		$password = 'mypassword';
		$password = \OC::$server->getCrypto()->encrypt($password);
		$a = new MailAccount();
		$a->setId(-1);
		$a->setName('ownCloudMail');
		$a->setInboundHost('localhost');
		$a->setInboundPort(993);
		$a->setInboundUser($user);
		$a->setInboundPassword($password);
		$a->setInboundSslMode('ssl');
		$a->setEmail($user);
		$a->setOutboundHost('localhost');
		$a->setOutboundPort(465);
		$a->setOutboundUser($user);
		$a->setOutboundPassword($password);
		$a->setOutboundSslMode('none');

		self::$account = new Account($a);
		self::$account->getImapConnection();
	}

	public static function tearDownAfterClass() {
		foreach(self::$createdMailboxes as $createdMailbox) {
			try {
				self::deleteMailbox($createdMailbox);
			} catch(\Exception $ex) {

			}
		}
	}

	/**
	 * @param string $name
	 * @return \OCA\Mail\Mailbox
	 */
	public function createMailBox($name) {
		try {
			$this->getTestAccount()->getMailbox($name);
			$this->deleteMailbox($name);
		} catch (\Exception $e) {
			// Ignore mailbox not found
		}

		$mailbox = $this->getTestAccount()->createMailbox($name);
		self::$createdMailboxes[$name]= $mailbox;
		return $mailbox;
	}

	/**
	 * @return Account
	 */
	protected function getTestAccount() {
		return self::$account;
	}

	/**
	 * @param string $mailbox
	 */
	private static function deleteMailbox($mailbox) {
		self::$account->deleteMailbox($mailbox);
	}

	/**
	 * @param string $name
	 */
	public function existsMailBox($name) {
		$mb = $this->getTestAccount()->getMailbox($name);
		try {
			$mb->getStatus();
			return true;
		} catch (\Exception $ex) {
			return false;
		}
	}


	/**
	 * @param string $name
	 */
	protected function assertMailBoxExists($name) {
		$this->assertTrue($this->existsMailBox($name));
	}

	/**
	 * @param string $name
	 */
	protected function assertMailBoxNotExists($name) {
		$this->assertFalse($this->existsMailBox($name));
	}

	protected function createTestMessage(
		Mailbox $mailbox,
		$subject = 'Don\'t panic!',
		$contents = 'Don\'t forget your towel',
		$from = 'someone@there.com',
		$to = 'me@here.com'
	) {
		$message = "From: $from
Subject: $subject
To: $to
Message-ID: <20150415133206.Horde.M8uzSs0lxFX6uUE2sc6_rw5@localhost>
User-Agent: Horde Application Framework 5
Date: Wed, 15 Apr 2015 13:32:06 +0000
Content-Type: text/plain; charset=UTF-8; format=flowed; DelSp=Yes
MIME-Version: 1.0


$contents";
		$mailbox->saveMessage($message);
	}

}
