<?php
defined('TYPO3_MODE') or die('Access denied.');

$extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('realty');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('realty_pi1',
    'FILE:EXT:realty/pi1/flexform_pi1_ds.xml');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    array(
        'LLL:EXT:realty/Resources/Private/Language/locallang_db.xlf:tt_content.list_type_pi1',
        'realty_pi1',
        'EXT:realty/ext_icon.svg',
    ),
    'list_type'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('realty', 'Configuration/TypoScript/', 'Realty Manager');

if (TYPO3_MODE === 'BE') {
    $GLOBALS['TBE_MODULES_EXT']['xMOD_db_new_content_el']['addElClasses']['tx_realty_pi1_wizicon']
        = $extPath . 'pi1/class.tx_realty_pi1_wizicon.php';

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'web',
        'txrealtyM1',
        '',
        '',
        array(
            'routeTarget' => 'tx_realty_BackendModul::mainAction',
            'access' => 'user,group',
            'name' => 'web_txrealtyM1',
            #'icon' => 'EXT:realty/ext_icon.svg', // > TYPO3 8 only
            #'labels' => 'LLL:EXT:realty/Resources/Private/Language/locallang_mod.xlf', // > TYPO3 8 only
            'labels' => array(
                'tabs_images' => array(
                    'tab' => 'EXT:realty/ext_icon.svg',
                ),
                'll_ref' => 'LLL:EXT:realty/Resources/Private/Language/locallang_mod.xlf',
            )
        )
    );
}
