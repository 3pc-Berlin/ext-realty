<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Saskia Metzler <saskia@merlin.owl.de>
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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

require_once(t3lib_extMgm::extPath('realty') . 'lib/tx_realty_constants.php');

/**
 * Unit tests for the tx_realty_pi1_Formatter class in the 'realty'
 * extension.
 *
 * @package TYPO3
 * @subpackage tx_realty
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class tx_realty_pi1_Formatter_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_realty_pi1_Formatter
	 */
	private $fixture;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var tx_realty_Model_RealtyObject a dummy realty object
	 */
	private $realtyObject;

	/**
	 * @var integer static_info_tables UID of Germany
	 */
	const DE = 54;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_realty');
		$this->testingFramework->createFakeFrontEnd();

		$this->realtyObject = tx_oelib_MapperRegistry
			::get('tx_realty_Mapper_RealtyObject')->getNewGhost();
		$this->realtyObject->setData(array('title' => 'test realty object'));

		$this->fixture = new tx_realty_pi1_Formatter(
			$this->realtyObject->getUid(),
			array(
				'defaultCountryUID' => self::DE,
				'currencyUnit' => '&euro;',
				'numberOfDecimals' => 2,
			),
			$GLOBALS['TSFE']->cObj
		);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->realtyObject, $this->fixture, $this->testingFramework);
	}


	//////////////////////////////
	// Tests for the constructor
	//////////////////////////////

	public function testConstructAnExceptionIfCalledWithAZeroRealtyObjectUid() {
		$this->setExpectedException(
			'Exception', '$realtyObjectUid must be greater than zero.'
		);

		$this->fixture = new tx_realty_pi1_Formatter(
			0, array(), $GLOBALS['TSFE']->cObj
		);
	}

	public function testConstructThrowsAnExceptionIfCalledWithAUidOfADeletedRealtyObject() {
		$this->realtyObject->markAsDead();

		$this->setExpectedException(
			'Exception', 'There was no realty object to load with the ' .
				'provided UID of ' . $this->realtyObject->getUid() .
				'. The formatter can only work for existing, non-deleted ' .
				'realty objects.'
		);

		new tx_realty_pi1_Formatter(
			$this->realtyObject->getUid(), array(), $GLOBALS['TSFE']->cObj
		);
	}


	///////////////////////////////////////////
	// Tests for getting formatted properties
	///////////////////////////////////////////

	public function testGetPropertyThrowsExceptionForEmptyKey() {
		$this->setExpectedException('Exception', '$key must not be empty.');

		$this->fixture->getProperty('');
	}

	public function testGetPropertyReturnsTheLabelOfAValidState() {
		$this->realtyObject->setProperty('state', 8);

		$this->assertEquals(
			$this->fixture->translate('label_state.8'),
			$this->fixture->getProperty('state')
		);
	}

	public function testGetPropertyReturnsAnEmptyStringIfTheStateIsNotSet() {
		$this->assertEquals(
			'',
			$this->fixture->getProperty('state')
		);
	}

	public function testGetPropertyReturnsAnEmptyStringIfTheObjectHasAnInvalidValueForState() {
		$this->realtyObject->setProperty('state', 1000000);

		$this->assertEquals(
			'',
			$this->fixture->getProperty('state')
		);
	}

	public function testGetPropertyReturnsTheLabelOfAValidHeatingType() {
		$this->realtyObject->setProperty('heating_type', '1');

		$this->assertEquals(
			$this->fixture->translate('label_heating_type.1'),
			$this->fixture->getProperty('heating_type')
		);
	}

	public function testGetPropertyReturnsTheLabelsOfAListOfValidHeatingTypes() {
		$this->realtyObject->setProperty('heating_type', '1,3,4');

		$this->assertEquals(
			$this->fixture->translate('label_heating_type.1') . ', ' .
				$this->fixture->translate('label_heating_type.3') . ', ' .
				$this->fixture->translate('label_heating_type.4'),
			$this->fixture->getProperty('heating_type')
		);
	}

	public function testGetPropertyReturnsAnEmptyStringIfTheHeatingTypeIsNotSet() {
		$this->assertEquals(
			'',
			$this->fixture->getProperty('heating_type')
		);
	}

	public function testGetPropertyReturnsAnEmptyStringIfTheObjectHasAnInvalidValueForHeatingType() {
		$this->realtyObject->setProperty('heating_type', 10000);

		$this->assertEquals(
			'',
			$this->fixture->getProperty('heating_type')
		);
	}

	public function testGetPropertyReturnsEmptyStringForCountrySameAsDefaultCountry() {
		$this->realtyObject->setProperty('country', self::DE);

		$this->assertEquals(
			'',
			$this->fixture->getProperty('country')
		);
	}

	public function testGetPropertyReturnsTheCountryNameForCountryDifferentFromDefaultCountry() {
		// randomly chosen the country UID of Australia
		$this->realtyObject->setProperty('country', 14);

		$this->assertEquals(
			'Australia',
			$this->fixture->getProperty('country')
		);
	}

	public function testGetPropertyReturnsTitleOfCity() {
		$this->realtyObject->setProperty(
				'city',
				$this->testingFramework->createRecord(
					REALTY_TABLE_CITIES, array('title' => 'test city')
				)
			);

		$this->assertEquals(
			'test city',
			$this->fixture->getProperty('city')
		);
	}

	public function testGetPropertyReturnsHtmlSpecialcharedTitleOfCity() {
		$this->realtyObject->setProperty(
				'city',
				$this->testingFramework->createRecord(
					REALTY_TABLE_CITIES, array('title' => 'test<br/>city')
				)
			);

		$this->assertEquals(
			htmlspecialchars('test<br/>city'),
			$this->fixture->getProperty('city')
		);
	}

	public function testGetPropertyReturnsEstateSizeAsFormattedAreaWithDecimals() {
		$this->realtyObject->setProperty('estate_size', 12345);
		$localeConvention = localeconv();

		$this->assertEquals(
			'12 345' . $localeConvention['decimal_point'] . '00&nbsp;' .
				$this->fixture->translate('label_squareMeters'),
			$this->fixture->getProperty('estate_size')
		);
	}

	public function testGetPropertyReturnsHoaFeeAsFormattedPriceWithDecimals() {
		$this->realtyObject->setProperty('hoa_fee', 12345);
		$localeConvention = localeconv();

		$this->assertEquals(
			'12 345' . $localeConvention['decimal_point'] . '00&nbsp;&euro;',
			$this->fixture->getProperty('hoa_fee')
		);
	}

	public function testGetPropertyReturnsMessageNowForUsableFromIfNoValueIsSet() {
		$this->assertEquals(
			$this->fixture->translate('message_now'),
			$this->fixture->getProperty('usable_from')
		);
	}

	public function testGetPropertyReturnsValueOfUsableFrom() {
		$this->realtyObject->setProperty('usable_from', '1.1.');

		$this->assertEquals(
			'1.1.',
			$this->fixture->getProperty('usable_from')
		);
	}

	public function testGetPropertyReturnsHtmlspecialcharedValueOfUsableFrom() {
		$this->realtyObject->setProperty('usable_from', '1.<br/>1.');

		$this->assertEquals(
			htmlspecialchars('1.<br/>1.'),
			$this->fixture->getProperty('usable_from')
		);
	}

	public function testGetPropertyReturnsNonZeroValueOfFloor() {
		$this->realtyObject->setProperty('floor', 3);

		$this->assertEquals(
			'3',
			$this->fixture->getProperty('floor')
		);
	}

	public function testGetPropertyReturnsEmptyStringForZeroValueOfFloor() {
		$this->realtyObject->setProperty('floor', 0);

		$this->assertEquals(
			'',
			$this->fixture->getProperty('floor')
		);
	}

	public function testGetPropertyReturnsMessageYesForRentedIfRentedIsSet() {
		$this->realtyObject->setProperty('rented', 1);

		$this->assertEquals(
			$this->fixture->translate('message_yes'),
			$this->fixture->getProperty('rented')
		);
	}

	public function testGetPropertyReturnsAnEmptyStringForRentedIfRentedIsNotSet() {
		$this->realtyObject->setProperty('rented', 0);

		$this->assertEquals(
			'',
			$this->fixture->getProperty('rented')
		);
	}

	public function testGetPropertyReturnsAddress() {
		$this->realtyObject->setProperty('show_address', 1);
		$this->realtyObject->setProperty('street', 'Main Street');
		$this->realtyObject->setProperty('zip', '12345');
		$this->realtyObject->setProperty(
			'city',
			$this->testingFramework->createRecord(
				REALTY_TABLE_CITIES, array('title' => 'Test Town')
			)
		);

		$this->assertEquals(
			'Main Street<br />12345 Test Town',
			$this->fixture->getProperty('address')
		);
	}

	public function testGetPropertyReturnsCroppedTitle() {
		$this->realtyObject->setProperty(
				'title',
				'This title is longer than 75 Characters, so the' .
					' rest should be cropped and be replaced with dots'
			);

		$this->assertEquals(
			'This title is longer than 75 Characters, so the rest should be' .
				' cropped and…',
			$this->fixture->getProperty('cropped_title')
		);
	}

	public function testGetPropertyReturnsHtmlspecialcharedCroppedTitle() {
		$this->realtyObject->setProperty(
				'title',
				'This title is longer than <b>75</b> Characters, so the' .
					' rest should be cropped and be replaced with dots'
			);

		$this->assertEquals(
			htmlspecialchars(
				'This title is longer than <b>75</b> Characters,' .
					' so the rest should be crop…'
			),
			$this->fixture->getProperty('cropped_title')
		);
	}
}
?>