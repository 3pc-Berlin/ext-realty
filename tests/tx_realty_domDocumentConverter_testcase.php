<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2010 Saskia Metzler <saskia@merlin.owl.de>
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

require_once(t3lib_extMgm::extPath('realty') . 'lib/class.tx_realty_translator.php');
require_once(t3lib_extMgm::extPath('realty') . 'lib/class.tx_realty_fileNameMapper.php');
require_once(t3lib_extMgm::extPath('realty') . 'tests/fixtures/class.tx_realty_domDocumentConverterChild.php');

/**
 * Unit tests for the tx_realty_domDocumentConverter class in the 'realty'
 * extension.
 *
 * @package TYPO3
 * @subpackage tx_realty
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class tx_realty_domDocumentConverter_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_realty_domDocumentConverter instance to be tested
	 */
	private $fixture;

	/**
	 * @var integer static_info_tables UID of Germany
	 */
	const DE = 54;

	public function setUp() {
		$this->fixture = new tx_realty_domDocumentConverterChild(
			new tx_realty_fileNameMapper()
		);
	}

	public function tearDown() {
		$this->fixture->__destruct();

		unset($this->fixture);
	}


	/////////////////////
	// Utlity functions
	/////////////////////

	/**
	 * Loads an XML string, sets the raw realty data and returns a DOMDocument
	 * of the provided string.
	 *
	 * @param string XML string to set for converting, must contain
	 *               wellformed XML, must not be empty
	 *
	 * @return DOMDocument DOMDocument of the provided XML string
	 */
	private function setRawDataToConvert($xmlString) {
		$loadedXml = DOMDocument::loadXML($xmlString);
		$this->fixture->setRawRealtyData($loadedXml);

		return $loadedXml;
	}


	/////////////////////////////////////
	// Testing the domDocumentConverter
	/////////////////////////////////////

	public function testFindFirstGrandchildReturnsGrandchildIfItExists() {
		$this->setRawDataToConvert(
			'<immobilie>'
				.'<child>'
					.'<grandchild>foo</grandchild>'
				.'</child>'
			.'</immobilie>'
		);

		$this->assertEquals(
			'foo',
			$this->fixture->findFirstGrandchild('child', 'grandchild')->nodeValue
		);
	}

	public function testFindFirstGrandchildReturnsNullIfTheGrandchildDoesNotExists() {
		$this->setRawDataToConvert(
			'<immobilie>'
				.'<child/>'
			.'</immobilie>'
		);

		$this->assertNull(
			$this->fixture->findFirstGrandchild('child', 'grandchild')
		);
	}

	public function testFindFirstGrandchildReturnsNullIfTheGivenDomnodeIsEmpty() {
		$this->setRawDataToConvert(
			'<immobilie/>'
		);

		$this->assertNull(
			$this->fixture->findFirstGrandchild('child', 'grandchild')
		);
	}

	public function testTheFirstGrandchildIsFoundAlthoughTheSecondChildAndItsChildAlsoMatch() {
		$this->setRawDataToConvert(
			'<immobilie>'
				.'<child>'
					.'<grandchild>foo</grandchild>'
				.'</child>'
				.'<child>'
					.'<grandchild>bar</grandchild>'
				.'</child>'
			.'</immobilie>'
		);

		$this->assertEquals(
			'foo',
			$this->fixture->findFirstGrandchild('child', 'grandchild')->nodeValue
		);
	}

	public function testTheFirstGrandchildIsFoundAlthoughTheFirstChildHasTwoMatchingChildren() {
		$this->setRawDataToConvert(
			'<immobilie>'
				.'<child>'
					.'<grandchild>foo</grandchild>'
					.'<grandchild>bar</grandchild>'
				.'</child>'
			.'</immobilie>'
		);

		$this->assertEquals(
			'foo',
			$this->fixture->findFirstGrandchild('child', 'grandchild')->nodeValue
		);
	}

	public function testGetNodeNameDoesNotChangeNodeNameWithoutXmlNamespace() {
		$node = new DOMDocument();
		$child = $node->appendChild(
			$node->createElement('foo')
		);

		$this->assertEquals(
			'foo',
			$this->fixture->getNodeName($child)
		);
	}

	public function testGetNodeNameReturnsNameWithoutXmlNamespaceWhenNameWithXmlNamespaceGiven() {
		$node = new DOMDocument();
		$child = $node->appendChild(
			$node->createElement('prefix:foo')
		);

		$this->assertEquals(
			'foo',
			$this->fixture->getNodeName($child)
		);
	}

	public function testAddOneElementToTheRealtyDataArray() {
		$data = array();
		$this->fixture->addElementToArray($data, 'foo', 'bar');

		$this->assertEquals(
			array('foo' => 'bar'),
			$data
		);
	}

	public function testAddTwoElementsToTheRealtyDataArray() {
		$data = array();
		$this->fixture->addElementToArray($data, 'foo', 'foo');
		$this->fixture->addElementToArray($data, 'bar', 'bar');

		$this->assertEquals(
			array('foo' => 'foo', 'bar' => 'bar'),
			$data
		);
	}

	public function testAddOneElementTwiceToTheRealtyDataArray() {
		$data = array();
		$this->fixture->addElementToArray($data, 'foo', 'foo');
		$this->fixture->addElementToArray($data, 'foo', 'bar');

		$this->assertEquals(
			array('foo' => 'bar'),
			$data
		);
	}

	public function testGetConvertedDataWhenNoRecordsAreGiven() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter/>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataWhenOneRecordIsGiven() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie/>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(array()),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataWhenSeveralRecordsAreGiven() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie/>'
					.'<immobilie/>'
					.'<immobilie/>'
					.'<immobilie/>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(
				array(),
				array(),
				array(),
				array()
			),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataWhenSeveralRecordsAreGivenAndContainContent() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<geo>'
							.'<strasse>bar</strasse>'
							.'<plz>bar</plz>'
						.'</geo>'
					.'</immobilie>'
					.'<immobilie>'
						.'<geo>'
							.'<strasse>foo</strasse>'
						.'</geo>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(
				array('street' => 'bar', 'zip' => 'bar'),
				array('street' => 'foo'),
			),
			$this->fixture->getConvertedData($node)
		);
	}

	/**
	 * @test
	 */
	public function getConvertedDataReadsObjectTitle() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<freitexte>' .
							'<objekttitel>klein und teuer</objekttitel>' .
						'</freitexte>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('title' => 'klein und teuer')),
			$this->fixture->getConvertedData($node)
		);
	}

	/**
	 * @test
	 */
	public function getConvertedDataReplacesSingleLinefeedInObjectTitleWithSpace() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<freitexte>' .
							'<objekttitel>foo' . LF . 'bar</objekttitel>' .
						'</freitexte>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('title' => 'foo bar')),
			$this->fixture->getConvertedData($node)
		);
	}

	/**
	 * @test
	 */
	public function getConvertedDataReplacesDoubleLinefeedInObjectTitleWithSpace() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<freitexte>' .
							'<objekttitel>foo' . LF . LF. 'bar</objekttitel>' .
						'</freitexte>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('title' => 'foo bar')),
			$this->fixture->getConvertedData($node)
		);
	}

	/**
	 * @test
	 */
	public function getConvertedDataReplacesSingleCarriageReturnInObjectTitleWithSpace() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<freitexte>' .
							'<objekttitel>foo' . CR . 'bar</objekttitel>' .
						'</freitexte>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('title' => 'foo bar')),
			$this->fixture->getConvertedData($node)
		);
	}

	/**
	 * @test
	 */
	public function getConvertedDataReplacesDoubleCarriageReturnInObjectTitleWithSpace() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<freitexte>' .
							'<objekttitel>foo' . CR . CR . 'bar</objekttitel>' .
						'</freitexte>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('title' => 'foo bar')),
			$this->fixture->getConvertedData($node)
		);
	}

	/**
	 * @test
	 */
	public function getConvertedDataReplacesCrLfInObjectTitleWithSpace() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<freitexte>' .
							'<objekttitel>foo' . CRLF .'bar</objekttitel>' .
						'</freitexte>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('title' => 'foo bar')),
			$this->fixture->getConvertedData($node)
		);
	}

	/**
	 * @test
	 */
	public function getConvertedDataReplacesSingleTabInObjectTitleWithSpace() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<freitexte>' .
							'<objekttitel>foo' . TAB . 'bar</objekttitel>' .
						'</freitexte>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('title' => 'foo bar')),
			$this->fixture->getConvertedData($node)
		);
	}

	/**
	 * @test
	 */
	public function getConvertedDataReplacesMultipleTabsInObjectTitleWithSpace() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<freitexte>' .
							'<objekttitel>foo' . TAB . TAB . 'bar</objekttitel>' .
						'</freitexte>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('title' => 'foo bar')),
			$this->fixture->getConvertedData($node)
		);
	}

	/**
	 * @test
	 */
	public function getConvertedDataForSaleTrueCreatesSaleObject() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<objektkategorie>' .
							'<vermarktungsart KAUF="true"/>' .
						'</objektkategorie>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('object_type' => REALTY_FOR_SALE)),
			$this->fixture->getConvertedData($node)
		);
	}

	/**
	 * @test
	 */
	public function getConvertedDataForRentTrueCreatesRentObject() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<objektkategorie>' .
							'<vermarktungsart MIETE_PACHT="true"/>' .
						'</objektkategorie>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('object_type' => REALTY_FOR_RENTING)),
			$this->fixture->getConvertedData($node)
		);
	}

	/**
	 * @test
	 */
	public function getConvertedDataForSaleTrueAndRentFalseCreatesSaleObject() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<objektkategorie>' .
							'<vermarktungsart KAUF="true" MIETE_PACHT="false"/>' .
						'</objektkategorie>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('object_type' => REALTY_FOR_SALE)),
			$this->fixture->getConvertedData($node)
		);
	}

	/**
	 * @test
	 */
	public function getConvertedDataForSaleFalseAndRentTrueCreatesRentObject() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<objektkategorie>' .
							'<vermarktungsart KAUF="false" MIETE_PACHT="true"/>' .
						'</objektkategorie>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('object_type' => REALTY_FOR_RENTING)),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataReturnsUniversalDataInEachRecord() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<firma>foo</firma>'
					.'<openimmo_anid>bar</openimmo_anid>'
					.'<immobilie/>'
					.'<immobilie/>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$universalData = array(
			'employer' => 'foo',
			'openimmo_anid' => 'bar'
		);
		$result = $this->fixture->getConvertedData($node);

		$this->assertEquals(
			$universalData,
			$result[0]
		);
		$this->assertEquals(
			$universalData,
			$result[1]
		);
	}

	public function testGetConvertedDataWhenSeveralPropertiesAreGiven() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<geo>'
							.'<strasse>foobar</strasse>'
							.'<plz>bar</plz>'
						.'</geo>'
						.'<freitexte>'
							.'<lage>foo</lage>'
						.'</freitexte>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(
				array(
					'street' => 'foobar',
					'zip' => 'bar',
					'location' => 'foo'
				)
			),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataSetsLocalizedPetsTitleIfValueIsStringTrue() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<verwaltung_objekt>'
							.'<haustiere>true</haustiere>'
						.'</verwaltung_objekt>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$result = $this->fixture->getConvertedData($node);
		$this->assertEquals(
			tx_oelib_ObjectFactory::make('tx_realty_translator')->translate('label_allowed'),
			$result[0]['pets']
		);
	}

	public function testGetConvertedDataSetsLocalizedPetsTitleIfValueIsOne() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<verwaltung_objekt>'
							.'<haustiere>1</haustiere>'
						.'</verwaltung_objekt>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$result = $this->fixture->getConvertedData($node);
		$this->assertEquals(
			tx_oelib_ObjectFactory::make('tx_realty_translator')->translate('label_allowed'),
			$result[0]['pets']
		);
	}

	public function testGetConvertedDataSubstitudesSurplusDecimalsWhenAPositiveNumberIsGiven() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<geo>'
							.'<strasse>1.00</strasse>'
						.'</geo>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(array('street' => '1')),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataSubstitudesSurplusDecimalsWhenANegativeNumberIsGiven() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<geo>'
							.'<strasse>-1.00</strasse>'
						.'</geo>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(array('street' => '-1')),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataSubstitudesTwoSurplusDecimalsWhenZeroIsGiven() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<geo>'
							.'<strasse>0.00</strasse>'
						.'</geo>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(array('street' => '0')),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataSubstitudesOneSurplusDecimalWhenZeroIsGiven() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<geo>'
							.'<strasse>0.0</strasse>'
						.'</geo>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(array('street' => '0')),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataNotSubstitudesTwoNonSurplusDecimalsFromAPositiveNumber() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<geo>'
							.'<strasse>1.11</strasse>'
						.'</geo>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(array('street' => '1.11')),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataNotSubstitudesTwoNonSurplusDecimalsFromANegativeNumber() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<geo>'
							.'<strasse>-1.11</strasse>'
						.'</geo>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(array('street' => '-1.11')),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataNotSubstitudesOneNonSurplusDecimals() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<geo>'
							.'<strasse>1.1</strasse>'
						.'</geo>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(array('street' => '1.1')),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataFetchesAlternativeContactEmail() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<kontaktperson>'
							.'<email_direkt>any-email@address.org</email_direkt>'
						.'</kontaktperson>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(array('contact_email' => 'any-email@address.org')),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataFetchesPhoneNumber() {
		$node = DOMDocument::loadXML(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<kontaktperson>'
							.'<tel_zentrale>1234567</tel_zentrale>'
						.'</kontaktperson>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);
		$this->fixture->setRawRealtyData($node);


		$this->assertEquals(
			$this->fixture->getConvertedData($node),
			array(array('contact_phone' => '1234567'))
		);
	}

	public function testGetConvertedDataFetchesAlternativePhoneNumber() {
		$node = DOMDocument::loadXML(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<kontaktperson>'
							.'<tel_privat>1234567</tel_privat>'
						.'</kontaktperson>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);
		$this->fixture->setRawRealtyData($node);


		$this->assertEquals(
			$this->fixture->getConvertedData($node),
			array(array('contact_phone' => '1234567'))
		);
	}

	public function testGetConvertedDataReplacesLowercasedBooleanStringsWithTrueBooleans() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<geo>'
							.'<strasse>true</strasse>'
							.'<plz>false</plz>'
						.'</geo>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(
				array(
					'street' => TRUE,
					'zip' => FALSE
				)
			),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataReplacesUppercasedBooleanStringsWithTrueBooleans() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<geo>'
							.'<strasse>TRUE</strasse>'
							.'<plz>FALSE</plz>'
						.'</geo>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(
				array(
					'street' => TRUE,
					'zip' => FALSE
				)
			),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataReplacesQuotedBooleanStringsWithTrueBooleans() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<geo>'
							.'<strasse>"true"</strasse>'
							.'<plz>"false"</plz>'
						.'</geo>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(
				array(
					'street' => TRUE,
					'zip' => FALSE
				)
			),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataGetsEstateSize() {
		$node = DOMDocument::loadXML(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<flaechen>'
							.'<grundstuecksflaeche>12345</grundstuecksflaeche>'
						.'</flaechen>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);
		$this->fixture->setRawRealtyData($node);

		$this->assertEquals(
			array(array('estate_size' => '12345')),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataGetsStateIfValidStateProvided() {
		$node = DOMDocument::loadXML(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<zustand_angaben>' .
							'<zustand ZUSTAND_ART="gepflegt" />' .
						'</zustand_angaben>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);
		$this->fixture->setRawRealtyData($node);

		$this->assertEquals(
			array(array('state' => 8)),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataDoesNotGetStateIfInvalidStateProvided() {
		$node = DOMDocument::loadXML(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<zustand_angaben>' .
							'<zustand ZUSTAND_ART="geputzt" />' .
						'</zustand_angaben>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);
		$this->fixture->setRawRealtyData($node);

		$this->assertEquals(
			array(array()),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataCanGetOneValidHeatingType() {
		$node = DOMDocument::loadXML(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<ausstattung>' .
							'<heizungsart ZENTRAL="true" />' .
						'</ausstattung>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);
		$this->fixture->setRawRealtyData($node);

		$this->assertEquals(
			array(array('heating_type' => 2)),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataCanGetMultipleValidHeatingTypesFromHeatingTypeNode() {
		$node = DOMDocument::loadXML(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<ausstattung>' .
							'<heizungsart ZENTRAL="true" OFEN="true" ETAGE="true" />' .
						'</ausstattung>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);
		$this->fixture->setRawRealtyData($node);

		$this->assertEquals(
			array(array('heating_type' => '2,9,11')),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataCanGetMultipleValidHeatingTypesFromFiringNode() {
		$node = DOMDocument::loadXML(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<ausstattung>' .
							'<befeuerung OEL="true" GAS="true" BLOCK="true" />' .
						'</ausstattung>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);
		$this->fixture->setRawRealtyData($node);

		$this->assertEquals(
			array(array('heating_type' => '5,8,12')),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataCanGetHeatingTypesFromFiringNodeAndHeatingTypeNode() {
		$node = DOMDocument::loadXML(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<ausstattung>' .
							'<heizungsart OFEN="true" />' .
							'<befeuerung BLOCK="true" />' .
						'</ausstattung>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);
		$this->fixture->setRawRealtyData($node);

		$this->assertEquals(
			array(array('heating_type' => '11,12')),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataDoesNotGetInvalidHeatingTypeFromHeatingTypeNode() {
		$node = DOMDocument::loadXML(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<ausstattung>' .
							'<heizungsart BACKOFEN="true" />' .
						'</ausstattung>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);
		$this->fixture->setRawRealtyData($node);

		$this->assertEquals(
			array(array()),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataDoesNotGetInvalidHeatingTypeFromFiringNode() {
		$node = DOMDocument::loadXML(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<ausstattung>' .
							'<befeuerung KERZE="true" />' .
						'</ausstattung>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);
		$this->fixture->setRawRealtyData($node);

		$this->assertEquals(
			array(array()),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataOnlyGetsValidHeatingTypesIfValidAndInvalidTypesProvided() {
		$node = DOMDocument::loadXML(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<ausstattung>' .
							'<heizungsart ZENTRAL="true" FUSSBODEN="true" BACKOFEN="true" />' .
						'</ausstattung>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);
		$this->fixture->setRawRealtyData($node);

		$this->assertEquals(
			array(array('heating_type' => '2,4')),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testCreateRecordsForImagesIfOneImageAppendixWithoutAnImagePathIsGiven() {
		$this->setRawDataToConvert(
			'<immobilie>'
				.'<anhang>'
					.'<anhangtitel>foo</anhangtitel>'
				.'</anhang>'
			.'</immobilie>'
		);

		$this->assertEquals(
			array(),
			$this->fixture->createRecordsForImages()
		);
	}

	public function testCreateRecordsForImagesIfOneImageValidImageAppendixIsGiven() {
		$this->setRawDataToConvert(
			'<immobilie>'
				.'<anhang>'
					.'<anhangtitel>bar</anhangtitel>'
					.'<daten>'
						.'<pfad>tx_realty_image_test.jpg</pfad>'
					.'</daten>'
				.'</anhang>'
			.'</immobilie>'
		);

		$this->assertEquals(
			array(
				array(
					'caption' => 'bar',
					'image' => 'tx_realty_image_test.jpg'
				)
			),
			$this->fixture->createRecordsForImages()
		);
	}

	public function testCreateRecordsForImagesIfOneImageImageAppendixWithoutTitleIsGiven() {
		$this->setRawDataToConvert(
			'<immobilie>'
				.'<anhang>'
					.'<anhangtitel/>'
					.'<daten>'
						.'<pfad>tx_realty_image_test.jpg</pfad>'
					.'</daten>'
				.'</anhang>'
			.'</immobilie>'
		);

		$this->assertEquals(
			array(
				array(
					'caption' => '',
					'image' => 'tx_realty_image_test.jpg'
				)
			),
			$this->fixture->createRecordsForImages()
		);
	}

	public function testCreateRecordsForImagesIfTwoValidImageAppendixesAreGiven() {
		$this->setRawDataToConvert(
			'<immobilie>'
				.'<anhang>'
					.'<anhangtitel>bar</anhangtitel>'
					.'<daten>'
						.'<pfad>tx_realty_image_test2.jpg</pfad>'
					.'</daten>'
				.'</anhang>'
				.' <anhang>'
					.'<anhangtitel>foo</anhangtitel>'
					.'<daten>'
						.'<pfad>tx_realty_image_test.jpg</pfad>'
					.'</daten>'
				.'</anhang>'
			.'</immobilie>'
		);
		$images = $this->fixture->createRecordsForImages();

		$this->assertEquals(
			array(
				'caption' => 'bar',
				'image' => 'tx_realty_image_test2.jpg'
			),
			$images[0]
		);
		$this->assertEquals(
			array(
				'caption' => 'foo',
				'image' => 'tx_realty_image_test.jpg'
			),
			$images[1]
		);
	}

	public function testCreateRecordsForImagesIfTwoImageAppendixesWithTheSameTitleAreGiven() {
		$this->setRawDataToConvert(
			'<immobilie>'
				.'<anhang>'
					.'<anhangtitel>bar</anhangtitel>'
					.'<daten>'
						.'<pfad>tx_realty_image_test2.jpg</pfad>'
					.'</daten>'
				.'</anhang>'
				.' <anhang>'
					.'<anhangtitel>bar</anhangtitel>'
					.'<daten>'
						.'<pfad>tx_realty_image_test.jpg</pfad>'
					.'</daten>'
				.'</anhang>'
			.'</immobilie>'
		);
		$images = $this->fixture->createRecordsForImages();

		$this->assertEquals(
			array(
				'caption' => 'bar',
				'image' => 'tx_realty_image_test2.jpg'
			),
			$images[0]
		);
		$this->assertEquals(
			array(
				'caption' => 'bar',
				'image' => 'tx_realty_image_test.jpg'
			),
			$images[1]
		);
	}

	public function testCreateRecordsForImagesOfTwoRealtyObjectsWithOneImageEachCreatesOneImageRecordPerImage() {
		$this->setRawDataToConvert(
			'<openimmo>'
				.'<immobilie>'
					.'<anhang>'
						.'<anhangtitel>bar</anhangtitel>'
						.'<daten>'
							.'<pfad>tx_realty_image_test2.jpg</pfad>'
						.'</daten>'
					.'</anhang>'
				.'</immobilie>'
				.'<immobilie>'
					.' <anhang>'
						.'<anhangtitel>foo</anhangtitel>'
						.'<daten>'
							.'<pfad>tx_realty_image_test.jpg</pfad>'
						.'</daten>'
					.'</anhang>'
				.'</immobilie>'
			.'</openimmo>'
		);

		$this->assertEquals(
			1,
			count($this->fixture->createRecordsForImages())
		);
	}

	public function testCreateRecordsForImagesOfTwoRealtyObjectsInOneFileWithAnIdenticallyNamedImage() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<immobilie>' .
					'<anhang>' .
						'<anhangtitel>bar</anhangtitel>' .
						'<daten>' .
							'<pfad>tx_realty_image_test.jpg</pfad>' .
						'</daten>' .
					'</anhang>' .
				'</immobilie>' .
				'<immobilie>' .
					'<anhang>' .
						'<anhangtitel>foo</anhangtitel>' .
						'<daten>' .
							'<pfad>tx_realty_image_test.jpg</pfad>' .
						'</daten>' .
					'</anhang>' .
				'</immobilie>' .
			'</openimmo>'
		);
		$result = $this->fixture->getConvertedData($node);

		$this->assertEquals(
			array(
				'caption' => 'bar',
				'image' => 'tx_realty_image_test.jpg'
			),
			$result[0]['images'][0]
		);
		$this->assertEquals(
			array(
				'caption' => 'foo',
				'image' => 'tx_realty_image_test_00.jpg'
			),
			$result[1]['images'][0]
		);
	}

	public function testGetConvertedDataImportsAttributeValuesCorrectly() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<ausstattung>'
							.'<fahrstuhl PERSONEN="false"/>'
							.'<kueche EBK="true"/>'
						.'</ausstattung>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(
				array(
					'elevator' => FALSE,
					'fitted_kitchen' => TRUE
				)
			),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataImportsTheHoaFee() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<preise>' .
							'<hausgeld>12345</hausgeld>' .
						'</preise>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('hoa_fee' => '12345')),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataImportsRentExcludingBillsFromNettokaltmiete() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<preise>' .
							'<nettokaltmiete>12345</nettokaltmiete>' .
						'</preise>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('rent_excluding_bills' => '12345')),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataImportsRentExcludingBillsFromNettokaltmieteWhenKaltmieteIsPresent() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<preise>' .
							'<nettokaltmiete>12345</nettokaltmiete>' .
							'<kaltmiete>54321</kaltmiete>' .
						'</preise>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('rent_excluding_bills' => '12345')),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataForNettokaltmieteMissingAndExistingKaltmieteImportsRentExcludingBillsFromKaltmiete() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<preise>' .
							'<kaltmiete>54321</kaltmiete>' .
						'</preise>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('rent_excluding_bills' => '54321')),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataForNettokaltmieteEmptyAndNonEmptyKaltmieteImportsRentExcludingBillsFromKaltmiete() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<preise>' .
							'<nettokaltmiete></nettokaltmiete>' .
							'<kaltmiete>54321</kaltmiete>' .
						'</preise>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('rent_excluding_bills' => '54321')),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataImportsTheLanguage() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<verwaltung_objekt>'
							.'<user_defined_anyfield>'
								.'<sprache>foo</sprache>'
							.'</user_defined_anyfield>'
						.'</verwaltung_objekt>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(array('language' => 'foo')),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataImportsLongitudeAndLatitudeAndSetsFlagIfBothAreProvided() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<geo>' .
							'<geokoordinaten laengengrad="foo" breitengrad="bar"/>' .
						'</geo>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(
				array(
					'exact_longitude' => 'foo',
					'exact_latitude' => 'bar',
					'exact_coordinates_are_cached' => TRUE,
				)
			),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataNotImportsTheCoordinatesIfOnlyOneIsProvided() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<geo>' .
							'<geokoordinaten laengengrad="foo"/>' .
						'</geo>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array()),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataNotImportsTheCoordinatesIfOneIsNonEmptyAndOneIsEmpty() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<geo>' .
							'<geokoordinaten laengengrad="foo" breitengrad=""/>' .
						'</geo>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array()),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataImportsTheCountryAsUidOfTheStaticCountryTableForValidCode() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<geo>' .
							'<land iso_land="DEU"/>' .
						'</geo>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('country' => self::DE)),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataImportsTheCountryAsUidOfTheStaticCountryTableForValidCodeTwice() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<geo>' .
							'<land iso_land="DEU"/>' .
						'</geo>' .
					'</immobilie>' .
					'<immobilie>' .
						'<geo>' .
							'<land iso_land="DEU"/>' .
						'</geo>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('country' => self::DE), array('country' => self::DE)),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataNotImportsTheCountryForInvalidCode() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<geo>' .
							'<land iso_land="foo"/>' .
						'</geo>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array()),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataNotImportsTheCountryForEmptyCode() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<geo>' .
							'<land iso_land=""/>' .
						'</geo>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array()),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataImportsTheCurrency() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<preise>' .
							'<waehrung iso_waehrung="EUR"/>' .
						'</preise>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('currency' => 'EUR')),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataImportsTheValueForNewBuilding() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<zustand_angaben>'
							.'<alter ALTER_ATTR="neubau" />'
						.'</zustand_angaben>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(array('old_or_new_building' => 1)),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataImportsTheValueForOldBuilding() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<zustand_angaben>'
							.'<alter ALTER_ATTR="altbau" />'
						.'</zustand_angaben>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(array('old_or_new_building' => 2)),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testConvertedDataDoesNotContainTheKeyOldOrNewBuildingIfNoValueWasSet() {
		$node = $this->setRawDataToConvert(
			'<openimmo>'
				.'<anbieter>'
					.'<immobilie>'
						.'<zustand_angaben>'
							.'<alter ALTER_ATTR="" />'
						.'</zustand_angaben>'
					.'</immobilie>'
				.'</anbieter>'
			.'</openimmo>'
		);

		$this->assertEquals(
			array(array()),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataImportsTheValueForShowAddressIfThisIsEnabled() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<verwaltung_objekt>' .
							'<objektadresse_freigeben>1</objektadresse_freigeben>' .
						'</verwaltung_objekt>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('show_address' => 1)),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testGetConvertedDataImportsTheValueForShowAddressIfThisIsDisabled() {
		$node = $this->setRawDataToConvert(
			'<openimmo>' .
				'<anbieter>' .
					'<immobilie>' .
						'<verwaltung_objekt>' .
							'<objektadresse_freigeben>0</objektadresse_freigeben>' .
						'</verwaltung_objekt>' .
					'</immobilie>' .
				'</anbieter>' .
			'</openimmo>'
		);

		$this->assertEquals(
			array(array('show_address' => 0)),
			$this->fixture->getConvertedData($node)
		);
	}

	public function testFetchDomAttributesIfValidNodeGiven() {
		$node = new DOMDocument();
		$element = $node->appendChild(
			$node->createElement('foo')
		);
		$element->setAttributeNode(new DOMAttr('foo', 'bar'));

		$this->assertEquals(
			array('foo' => 'bar'),
			$this->fixture->fetchDomAttributes($element)
		);
	}

	public function testFetchDomAttributesIfNodeWithoutAttributesGiven() {
		$node = new DOMDocument();
		$element = $node->appendChild(
			$node->createElement('foo')
		);

		$this->assertEquals(
			array(),
			$this->fixture->fetchDomAttributes($element)
		);
	}
}
?>