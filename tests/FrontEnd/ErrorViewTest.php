<?php
/***************************************************************
* Copyright notice
*
* (c) 2008-2012 Saskia Metzler <saskia@merlin.owl.de>
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Unit tests for the tx_realty_pi1_ErrorView class in the "realty" extension.
 *
 * @package TYPO3
 * @subpackage tx_realty
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class tx_realty_FrontEnd_ErrorViewTest extends tx_phpunit_testcase {
	/**
	 * @var tx_realty_pi1_ErrorView
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_realty');
		$this->testingFramework->createFakeFrontEnd(
			$this->testingFramework->createFrontEndPage()
		);

		$this->fixture = new tx_realty_pi1_ErrorView(
			array('templateFile' => 'EXT:realty/pi1/tx_realty_pi1.tpl.htm'),
			$GLOBALS['TSFE']->cObj
		);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}

	public function testRenderReturnsTranslatedMessage() {
		$this->assertContains(
			$this->fixture->translate('message_access_denied'),
			$this->fixture->render(array('message_access_denied'))
		);
	}

	public function testRenderReturnsLinkedPleaseLoginMessage() {
		$this->fixture->setConfigurationValue(
			'loginPID', $this->testingFramework->createFrontEndPage()
		);

		$this->assertContains(
			'<a href',
			$this->fixture->render(array('message_please_login'))
		);
	}

	public function testRenderReturnsPleaseLoginMessageWithLoginPidWithinTheLink() {
		$loginPid = $this->testingFramework->createFrontEndPage();
		$this->fixture->setConfigurationValue('loginPID', $loginPid);

		$this->assertContains(
			'?id=' . $loginPid,
			$this->fixture->render(array('message_please_login'))
		);
	}

	public function testRenderReturnsPleaseLoginMessageWithRedirectUrl() {
		$this->fixture->setConfigurationValue(
			'loginPID', $this->testingFramework->createFrontEndPage()
		);

		$this->assertContains(
			urlencode('?id=' . $GLOBALS['TSFE']->id),
			$this->fixture->render(array('message_please_login'))
		);
	}

	public function testRenderReturnsWrappingErrorViewSubpart() {
		$this->assertContains(
			'class="error"',
			$this->fixture->render(array('message_access_denied'))
		);
	}

	public function testRenderReturnsNoUnreplacedMarkers() {
		$this->assertNotContains(
			'###',
			$this->fixture->render(array('message_access_denied'))
		);
	}
}
?>