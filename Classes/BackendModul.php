<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Module\BaseScriptClass;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\FormProtection\FormProtectionFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;
use TYPO3\CMS\Core\Cache\Frontend\AbstractFrontend as AbstractCacheFrontEnd;

/**
 * Backend module.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 *
 */
class tx_realty_BackendModul extends BaseScriptClass
{
    /**
     * The template object
     * @var \TYPO3\CMS\Backend\Template\DocumentTemplate
     */
    public $doc;

    /**
     * The name of the module
     * 
     * @var string
     */
    const MODULE_NAME = 'web_txrealtyM1';

    /**
     * @var Tx_Oelib_Template template object
     */
    private $template = null;

    /**
     * localized error message for the errors occurred during the access check
     *
     * @var string[]
     */
    private $errorMessages = array();

    /**
     * @var int tab import
     */
    const IMPORT_TAB = 0;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->MCONF = array(
            'name' => self::MODULE_NAME
        );
    }

    /**
     * Initializes the module.
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->initializeTemplate();
    }

    /**
     * Entrance from the backend module. This replace the _dispatch
     *
     * @param ServerRequestInterface $request The request object from the backend
     * @param ResponseInterface $response The reponse object sent to the backend
     *
     * @return ResponseInterface Return the response object
     */
    public function mainAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->getLanguageService()->includeLLFile('EXT:realty/Resources/Private/Language/locallang_mod.xlf');
        $this->init();
        $this->render();
        $response->getBody()->write($this->content);
        return $response;
    }

    /**
     * Renders the module content.
     *
     * @return string HTML for the module, will not be empty
     */
    public function render()
    {
        $this->content = '';
        $title = $this->translate('title');
        $this->content .= $this->doc->startPage($title) . $this->doc->header($title);

        if ($this->hasAccess()) {
            $this->content .= $this->doc->section(
                '',
                $this->createTab()
            );

            if (GeneralUtility::_GP('action') == 'startImport') {
                /** @var tx_realty_openImmoImport $importer */
                $importer = GeneralUtility::makeInstance('tx_realty_openImmoImport');
                $this->template->setMarker(
                    'import_logs',
                    nl2br(htmlspecialchars($importer->importFromZip()))
                );

                $this->content .= $this->template->getSubpart('IMPORT_RESULT');
            }

            $this->content .= $this->createImportButton();
        } else {
            $this->content .= $this->getErrorMessages();
        }

        $this->content .= $this->doc->endPage();
        return $this->doc->insertStylesAndJS($this->content);
    }

    /**
     * Initializes the template objects.
     *
     * @return void
     */
    private function initializeTemplate()
    {
        $this->doc = GeneralUtility::makeInstance(DocumentTemplate::class);
        $this->doc->backPath = $GLOBALS['BACK_PATH'];
        $this->doc->docType = 'xhtml_strict';
        $this->doc->styleSheetFile2
            = '../typo3conf/ext/realty/Resources/Public/Css/BackendModul.css';

        $this->template
            = Tx_Oelib_TemplateRegistry::getInstance()->getByFileName(
                'EXT:realty/Resources/Private/Backend/BackendModul.html'
        );
    }

    /**
     * Creates the OpenImmo import tab.
     *
     * @return string HTML for the OpenImmo tab, will not be empty
     */
    private function createTab()
    {
        $moduleToken = FormProtectionFactory::get()->generateToken('moduleCall', self::MODULE_NAME);
        return $this->doc->getTabMenu(
            array('M' => self::MODULE_NAME, 'moduleToken' => $moduleToken, 'id' => $this->id),
            'tab',
            self::IMPORT_TAB,
            array(self::IMPORT_TAB => $this->translate('import_tab'))
            );
    }

    /**
     * Creates an import button which will start the import of the OpenImmo
     * files.
     *
     * @return string the HTML output for the import button
     */
    private function createImportButton()
    {
        $moduleUrl = BackendUtility::getModuleUrl(self::MODULE_NAME, array('id' => $this->id));
        $this->template->setMarker('module_url', htmlspecialchars($moduleUrl));
        $this->template->setMarker(
            'label_start_import',
            $this->translate('start_import_button')
        );
        $this->template->setMarker('tab_number', self::IMPORT_TAB);
        $this->template->setMarker(
            'label_import_in_progress',
            $this->translate('label_import_in_progress')
        );

        return $this->template->getSubpart('IMPORT_BUTTON');
    }

    /**
     * Checks if the current BE user has access to the necessary data to import
     * realty records.
     *
     * @return bool TRUE if the BE user is an admin or if they have the
     *                 rights to access the necessary data, FALSE otherwise
     */
    private function hasAccess()
    {
        if ($this->getBackEndUserAuthentication()->isAdmin()) {
            return true;
        }

        return $this->userHasAccessToPages() && $this->userHasAccessToTables();
    }

    /**
     * Checks if the user has write permissions on the pages configured in
     * "pidForRealtyObjectsAndImages" and "pidForAuxiliaryRecords".
     *
     * @return bool TRUE if the user has write access to both pages, FALSE
     *                 otherwise
     */
    private function userHasAccessToPages()
    {
        $configurationProxy = Tx_Oelib_ConfigurationProxy::getInstance('realty');

        $objectsPid = $configurationProxy->getAsInteger(
            'pidForRealtyObjectsAndImages'
        );
        $canWriteObjectsPage = $this->getBackEndUserAuthentication()->doesUserHaveAccess(
            BackendUtility::getRecord('pages', $objectsPid), 16
        );

        $auxiliaryPid = $configurationProxy->getAsInteger(
            'pidForAuxiliaryRecords'
        );
        $canWriteAuxiliaryPage = $this->getBackEndUserAuthentication()->doesUserHaveAccess(
            BackendUtility::getRecord('pages', $auxiliaryPid), 16
        );

        if (!$canWriteObjectsPage) {
            $this->storeErrorMessage('objects_pid', $objectsPid);
        }
        if (!$canWriteAuxiliaryPage) {
            $this->storeErrorMessage('auxiliary_pid', $auxiliaryPid);
        }

        return $canWriteObjectsPage && $canWriteAuxiliaryPage;
    }

    /**
     * Checks if the user has write access to the database tables needed to
     * create realty objects and auxiliary records.
     *
     * @return bool TRUE if the user has the needed DB table access
     *                 permissions, FALSE otherwise
     */
    private function userHasAccessToTables()
    {
        $userHasAccessToTables = true;
        $neededTables = array(
            'tx_realty_objects',
            'tx_realty_apartment_types',
            'tx_realty_car_places',
            'tx_realty_cities',
            'tx_realty_districts',
            'tx_realty_house_types',
            'tx_realty_images',
            'tx_realty_pets',
        );

        foreach ($neededTables as $table) {
            if (!$this->getBackEndUserAuthentication()->check('tables_modify', $table)) {
                $userHasAccessToTables = false;
                $this->storeErrorMessage('table_access', $table);
                break;
            }
        }

        return $userHasAccessToTables;
    }

    /**
     * Stores a localized error message in $this->errorMessages.
     *
     * @param string $message
     *        the locallang key of the error message to store,
     *        must be an existing locallang label without the prefix 'error_message_'
     * @param string $value
     *        the value which should be included in the locallang message, must not be empty
     *
     * @return void
     */
    private function storeErrorMessage($message, $value)
    {
        $this->errorMessages[] = sprintf(
            $this->translate('error_message_' . $message),
            $value
        );
    }

    /**
     * Builds the HTML output for the error messages.
     *
     * @return string HTML output for the error messages, will be empty if no
     *                errors occurred during processing
     */
    private function getErrorMessages()
    {
        if (empty($this->errorMessages)) {
            return '';
        }

        $this->template->setMarker(
            'message_no_permissions',
                $this->translate('message_no_permission')

        );
        $errorList = implode('</li>' . LF . '<li>', $this->errorMessages);
        $this->template->setMarker('error_list', '<li>' . $errorList . '</li>');

        return $this->template->getSubpart('IMPORT_ERRORS');
    }

    /**
     * Returns $GLOBALS['BE_USER'].
     *
     * @return BackendUserAuthentication|null
     */
    protected function getBackEndUserAuthentication()
    {
        return isset($GLOBALS['BE_USER']) ? $GLOBALS['BE_USER'] : null;
    }

    /**
     * Returns $GLOBALS['LANG'].
     *
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Returns the localized string for $key.
     *
     * @param string $key the key of the localized string, must not be empty
     *
     * @return string
     */
    protected function translate($key)
    {
        return $this->getLanguageService()->getLL($key);
    }
}
