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

/**
 * Class 'tx_realty_pi1_OverviewTableView' for the 'realty' extension.
 *
 * This class renders the overview table view.
 *
 * @package TYPO3
 * @subpackage tx_realty
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class tx_realty_pi1_OverviewTableView extends tx_realty_pi1_FrontEndView {
	/**
	 * Returns the overview table as HTML.
	 *
	 * @param array piVars array, must contain the key "showUid" with a valid
	 *              realty object UID as value
	 *
	 * @return string HTML for the overview table or an empty string if the
	 *                realty object with the provided UID has no data to show
	 */
	public function render(array $piVars = array()) {
		$objectNumber = htmlspecialchars(
			tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
				->find($piVars['showUid'])->getProperty('object_number')
		);

		$hasObjectNumber = $this->setOrDeleteMarkerIfNotEmpty(
			'object_number', $objectNumber, '', 'field_wrapper'
		);
		$hasTableRows = $this->createTableRows($piVars['showUid']);

		return ($hasObjectNumber || $hasTableRows)
			? $this->getSubpart('FIELD_WRAPPER_OVERVIEW_TABLE')
			: '';
	}

	/**
	 * Fills the subpart "OVERVIEW_ROW" with the contents of the current
	 * record's database fields specified via the TS setup variable
	 * "fieldsInSingleViewTable".
	 *
	 * @param integer UID of the realty object for which to create the table,
	 *                must be > 0
	 *
	 * @return boolean true if at least one row has been filled, false otherwise
	 */
	private function createTableRows($uid) {
		$fieldNames = $this->getFieldNames($uid);

		if (empty($fieldNames)) {
			$this->hideSubparts('overview_row');
			return false;
		}

		$rows = array();
		$rowCounter = 0;
		$formatterClassName
			= t3lib_div::makeInstanceClassName('tx_realty_pi1_Formatter');
		$formatter = new $formatterClassName($uid, $this->conf, $this->cObj);

		foreach ($fieldNames as $key) {
			if ($this->setMarkerIfNotEmpty(
				'data_current_row', $formatter->getProperty($key)
			)) {
				$position = ($rowCounter % 2) ? 'odd' : 'even';
				$this->setMarker('class_position_in_list', $position);
				$this->setMarker(
					'label_current_row', $this->translate('label_' . $key)
				);
				$rows[] = $this->getSubpart('OVERVIEW_ROW');
				$rowCounter++;
			}
		}

		$formatter->__destruct();
		$this->setSubpart('overview_row', implode(LF, $rows));

		return ($rowCounter > 0);
	}

	/**
	 * Returns the field names for which to create the overview table. They are
	 * derived from the configuration in "fieldsInSingleViewTable".
	 *
	 * @param integer UID of the realty object, must be > 0
	 *
	 * @return array field names with which to fill the overview table, will be
	 *               empty if none are configured
	 */
	private function getFieldNames($uid) {
		if (!$this->hasConfValueString('fieldsInSingleViewTable')) {
			return array();
		}

		$result = array();

		foreach (t3lib_div::trimExplode(
			',', $this->getConfValueString('fieldsInSingleViewTable'), true
		) as $key) {
			if (tx_oelib_MapperRegistry::get('tx_realty_Mapper_RealtyObject')
				->find($uid)->isAllowedKey($key)
			) {
				$result[] = $key;
			}
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realty/pi1/class.tx_realty_pi1_OverviewTableView.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/realty/pi1/class.tx_realty_pi1_OverviewTableView.php']);
}
?>