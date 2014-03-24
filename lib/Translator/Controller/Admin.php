<?php
/**
 * Translator module for Zikula
 *
 * @author Christian Deinert
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License (GPL) 3.0
 * @package Translator
 * @subpackage Controller
 */

/**
 * Administrative UI-oriented operations.
 */
class Translator_Controller_Admin extends Translator_AbstractController
{
    /**
     * The default entrypoint.
     *
     * This function redirects the user to the function 'view'.
     *
     * @return void
     */
    public function main()
    {
        $this->redirect(ModUtil::url($this->name, 'admin', 'view'));
    }
    
    /**
     * View all Gettext msgid's avaiable in the Translator Module.
     *
     * Parameters passed via POST
     * ---------------------------------
     * * string searchfor    optional; The String to be searched for.              Default null
     * * string searchby     optional; The DB-field in which to search.            Default 'sourcestring'
     * * int    startnum     optional; Row-number of First item to show.           Default null
     * * int    itemsperpage optional; Number of the items to show.                Default Module variable 'itemsperpage' if avaiable, otherwise 50
     * * string sort         optional; The DB-field to order by.                   Default 'trans_id'
     * * string sortdir      optional; The sorting direction.                      Default 'asc'
     * * string mod          optional; The Module to show the Gettext msgids from. Default null
     *
     * Parameters passed to the Template
     * ---------------------------------
     * * string searchfor            The passed in Parameter
     * * string searchby             The passed in Parameter
     * * int    startnum             The passed in Parameter
     * * int    itemsperpage         The passed in Parameter
     * * string sort                 The passed in Parameter
     * * string sortdir              The passed in Parameter
     * * array  translationLanguages Array with configured Languages for the Translator module
     * * array  items                Array with the items to show
     * * int    count                The number of items
     * * array  links                Array with Administration links
     * * string mod                  The passed in Parameter
     * * array  awl_modules          Array with all configured Modules for the Translator module
     *
     * @return string The rendered template output
     */
    public function view()
    {
        // Security Check
        if (!SecurityUtil::checkPermission('Translator::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        
        // Get parameters from whatever input we need.
        $params = array(
            'searchfor' => null,
            'searchby' => 'sourcestring',
            'startnum' => null,
            'itemsperpage' => $this->getVar('itemsperpage', 50),
            'sort' => 'trans_id',
            'sortdir' => 'asc',
            'mod' => null,
        );
        $this->getGet($params);
        
        $items = ModUtil::apiFunc($this->name, 'Translation', 'getAll', $params);
        $count = ModUtil::apiFunc($this->name, 'Translation', 'countAll', $params);
        
        // Assign parameters to the view and return it.
        $this->assign2View($params);
        
        return $this->view
            ->assign('translationLanguages', $this->getVar('translationLanguages'))
            ->assign('items', $items)
            ->assign('count', $count)
            ->assign('links', ModUtil::apiFunc($this->name, 'Admin', 'getSecondaryLinks'))
            ->assign('awl_modules', ModUtil::apiFunc($this->name, 'admin', 'getModules'))
            ->fetch('admin/view.tpl');
    }
    
    /**
     * Edit all Gettext msgids' msgstr avaiable in the Translator Module for each configured language.
     *
     * Parameters passed via POST
     * ---------------------------------
     * * string searchfor    optional; The String to be searched for.              Default null
     * * string searchby     optional; The DB-field in which to search.            Default 'sourcestring'
     * * int    startnum     optional; Row-number of First item to show.           Default null
     * * int    itemsperpage optional; Number of the items to show.                Default Module variable 'itemsperpage' if avaiable, otherwise 50
     * * string sort         optional; The DB-field to order by.                   Default 'trans_id'
     * * string sortdir      optional; The sorting direction.                      Default 'asc'
     * * string mod          optional; The Module to show the Gettext msgids from. Default null
     *
     * Parameters passed to the Template
     * ---------------------------------
     * * string searchfor            The passed in Parameter
     * * string searchby             The passed in Parameter
     * * int    startnum             The passed in Parameter
     * * int    itemsperpage         The passed in Parameter
     * * string sort                 The passed in Parameter
     * * string sortdir              The passed in Parameter
     * * array  translationLanguages Array with configured Languages for the Translator module
     * * array  items                Array with the items to show
     * * int    count                The number of items
     * * array  links                Array with Administration links
     * * string mod                  The passed in Parameter
     * * array  awl_modules          Array with all configured Modules for the Translator module
     *
     * @return string The rendered template output
     */
    public function edit()
    {
        // Security Check
        if (!SecurityUtil::checkPermission('Translator::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        
        // Get parameters from whatever input we need.
        $params = array(
            'searchfor' => null,
            'searchby' => 'sourcestring',
            'startnum' => null,
            'itemsperpage' => $this->getVar('itemsperpage', 20),
            'sort' => 'trans_id',
            'sortdir' => 'asc',
            'mod' => null,
        );
        $this->getGet($params);
        
        $items = ModUtil::apiFunc($this->name, 'Translation', 'getAll', $params);
        $count = ModUtil::apiFunc($this->name, 'Translation', 'countAll', $params);
        
        // Assign parameters to the view and return it.
        $this->assign2View($params);
        
        return $this->view
            ->assign('translationLanguages', $this->getVar('translationLanguages'))
            ->assign('items', $items)
            ->assign('count', $count)
            ->assign('links', ModUtil::apiFunc($this->name, 'Admin', 'getSecondaryLinks'))
            ->assign('awl_modules', ModUtil::apiFunc($this->name, 'admin', 'getModules'))
            ->fetch('admin/edit.tpl');
    }
    
    /**
     * Processes the update process.
     *
     * Redirects to Edit-Controller.
     *
     * Parameters passed via POST
     * -----------------------------------
     * * string searchfor              optional; The String to be searched for.                   Default null
     * * string searchby               optional; The DB-field in which to search.                 Default 'sourcestring'
     * * int    startnum               optional; Row-number of First item to show.                Default null
     * * int    itemsperpage           optional; Number of the items to show.                     Default Module variable 'itemsperpage' if avaiable, otherwise 50
     * * string sort                   optional; The DB-field to order by.                        Default 'trans_id'
     * * string sortdir                optional; The sorting direction.                           Default 'asc'
     * * string mod                    optional; The Module to show the Gettext msgids from.      Default null
     *
     * Dynamic Parameters passed via POST
     * -----------------------------------
     * * array  upd_targetstring_{language}  Holds the informations which translationstrings are to update. defined by {language}
     * * string targetstring_{id}_{language} Holds the translation. defined by {id} and {language}
     *
     * @return void
     */
    public function store()
    {
        // Security Check
        if (!SecurityUtil::checkPermission('Translator::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        
        if ($this->request->isPost()) {
            // Get parameters from whatever input we need.
            $params = array(
                'searchfor' => null,
                'searchby' => 'sourcestring',
                'startnum' => null,
                'itemsperpage' => $this->getVar('itemsperpage', 20),
                'sort' => 'trans_id',
                'sortdir' => 'asc',
                'mod' => null,
            );
            $this->getPost($params);
            
            $translationLanguages = $this->getVar('translationLanguages');
            
            if (!empty($translationLanguages)) {
                foreach ($translationLanguages as $translang) {
                    $toupdate = $this->request->request->get('upd_targetstring_'.$translang, null);
                    
                    if (is_array($toupdate) && !empty($toupdate)) {
                        foreach ($toupdate as $upd_set) {
                            $upd_set = explode('||', $upd_set);
                            $targetstring = $this->request->request->get('targetstring_'.$upd_set[0].'_'.$upd_set[1], null);
                            
                            if ($targetstring !== null) {
                                ModUtil::apiFunc($this->name, 'Translation', 'save', array(
                                    'trans_id' => $upd_set[0],
                                    'language' => $upd_set[1],
                                    'targetstring' => $targetstring,
                                ));
                            }
                        }
                        
                        LogUtil::registerStatus($this->__('Translations saved'));
                    }
                }
            }
        }
        
        $this->redirect(ModUtil::url($this->name, 'admin', 'edit', $params));
    }
    
    /**
     * View all possible export options.
     *
     * Parameters passed to the Template
     * -----------------------------------
     * * array modules The avaiable modules
     * * array translationLanguages The avaiable languages
     * * array links Admin menu links
     *
     * @return string The rendered template output
     */
    public function exportTranslations()
    {
        // Security Check
        if (!SecurityUtil::checkPermission('Translator::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        
        $translatorModules = $this->getVar('translatorModules');
        $modules = array();
        
        if (is_array($translatorModules)) {
            foreach ($translatorModules as $key => $val) {
                $modInfo = ModUtil::apiFunc('Extensions', 'admin', 'modify', array('id' => $val));
                
                $modules[] = array(
                    'mod_id' => $val,
                    'moddesc' => $modInfo['displayname'],
                );
            }
        }
        
        $translationLanguages = $this->getVar('translationLanguages');
         
        if (!is_array($translatorLanguages)) {
            $translatorLanguages = array();
        }
         
        return $this->view
            ->assign('modules', $modules)
            ->assign('translationLanguages', $translationLanguages)
            ->assign('links', ModUtil::apiFunc($this->name, 'Admin', 'getSecondaryLinks'))
            ->fetch('admin/exportTranslations.tpl');
    }
    
    /**
     * Processes the export to a .pot file.
     *
     * Redirects to exportTranslations-Controller
     *
     * Parameters passed via POST
     * ---------------------------------
     * * int mod_id The ID of the module to export
     *
     * @throws Zikula_Exception_Forbidden Thrown if the parameter 'mod_id' is null
     * @return void
     */
    public function export2Pot()
    {
        // Security Check
        if (!SecurityUtil::checkPermission('Translator::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        
        $mod_id = $this->request->query->get('mod_id', null);
        
        if ($mod_id == null) {
            throw new Zikula_Exception_Forbidden();
        }
        
        ModUtil::apiFunc($this->name, 'Export', 'export2Pot', array('mod_id' => $mod_id));
        
        $this->redirect(ModUtil::url($this->name, 'admin', 'exportTranslations'));
    }
    
    /**
     * Processes the Export to a .po file.
     *
     * Redirects to exportTranslations-Controller.
     *
     * Parameters passed via POST
     * ---------------------------------
     * * int    mod_id                  The ID of the module to export
     * * string language    optional;   The language to export
     *
     * @throws Zikula_Exception_Forbidden Thrown if the parameter 'mod_id' is null
     * @return void
     */
    public function export2Po()
    {
        // Security Check
        if (!SecurityUtil::checkPermission('Translator::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        
        $mod_id = $this->request->query->get('mod_id', null);
        $language = $this->request->query->get('language', null);
        
        if ($mod_id == null) {
            throw new Zikula_Exception_Forbidden();
        }
        if ($language == null) {
            $translationLanguages = $this->getVar('translationLanguages');
            
            foreach ($translationLanguages as $lang) {
                ModUtil::apiFunc($this->name, 'Export', 'export2po', array('mod_id' => $mod_id, 'language' => $lang));
            }
        } else {
            ModUtil::apiFunc($this->name, 'Export', 'export2po', array('mod_id' => $mod_id, 'language' => $language));
        }
        
        $this->redirect(ModUtil::url($this->name, 'admin', 'exportTranslations'));
    }
    
    /**
     * Starts the search and import of new Strings to translate.
     *
     * Parameters passed to the Template
     * ---------------------------------
     * * array items JSON encoded array containing the modules to search in
     *
     * @return string The rendered template output
     */
    public function addNewTranslations()
    {
        // Security Check
        if (!SecurityUtil::checkPermission('Translator::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        
        $items = ModUtil::apiFunc($this->name, 'admin', 'getModules');
        
        return $this->view
            ->assign('items', json_encode($items))
            ->fetch('admin/addNewTranslations.tpl');
    }
    
    /**
     * View all possible import options.
     *
     * Parameters passed to the Template
     * -----------------------------------
     * * array importmodules    The avaiable modules
     * * array links            Admin menu links
     *
     * @return string The rendered template output
     */
    public function importTranslations()
    {
        // Security Check
        if (!SecurityUtil::checkPermission('Translator::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        
        $avaiableModules = ModUtil::apiFunc('Extensions', 'Admin', 'listmodules', array('state' => ModUtil::STATE_ACTIVE));
        $translationLanguages = $this->getVar('translationLanguages');
        $importmodules = array();
        
        foreach ($avaiableModules as $module) {
            $modulepath = 'modules/'.$module['directory'];
            $tmparray = array();
            
            if (file_exists($modulepath.'/locale/module_'.$module['name'].'.pot')) {
                $tmparray[] = array(
                    'file' => $modulepath.'/locale/module_'.$module['name'].'.pot',
                    'language' => '',
                    'type' => 'pot',
                );
            }
            
            foreach ($translationLanguages as $language) {
                if (file_exists($modulepath.'/locale/'.$language.'/LC_MESSAGES/module_'.$module['name'].'.po')) {
                    $tmparray[] = array(
                        'file' => $modulepath.'/locale/'.$language.'/LC_MESSAGES/module_'.$module['name'].'.po',
                        'language' => $language,
                        'type' => 'po',
                    );
                }
            }
            
            if (count($tmparray) > 0) {
                $importmodules[] = array(
                    'mod_id' => $module['id'],
                    'moddesc' => $module['displayname'],
                    'files' => $tmparray,
                );
            }
        }
        
        return $this->view
            ->assign('importmodules', $importmodules)
            ->assign('links', ModUtil::apiFunc($this->name, 'Admin', 'getSecondaryLinks'))
            ->fetch('admin/importTranslations.tpl');
    }
    
    /**
     * Processes the import procedure.
     *
     * Redirects to importTranslations-Controller.
     *
     * Parameters passed via POST
     * ---------------------------------
     * * int    mod_id                  The id of the Module
     * * string file The                file which holds the translations
     * * string type The                filetype
     * * string language    optional;   The language to import
     *
     * @throws Zikula_Exception_Fatal Thrown if the parameters 'mod_id', 'file', 'type' are null
     * @return void
     */
    public function importFromFile()
    {
        // Security Check
        if (!SecurityUtil::checkPermission('Translator::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        
        $mod_id = $this->request->query->get('mod_id', null);
        $file = $this->request->query->get('file', null);
        $filetype = $this->request->query->get('filetype', null);
        $language = $this->request->query->get('language', null);
        
        if ($mod_id == null) {
            throw new Zikula_Exception_Fatal();
        }
        if ($file == null) {
            throw new Zikula_Exception_Fatal();
        }
        if ($filetype == null) {
            throw new Zikula_Exception_Fatal();
        }
        
        if ($filetype == 'pot') {
            ModUtil::apiFunc($this->name, 'Import', 'importFromPot', array('mod_id' => $mod_id, 'file' => $file));
            LogUtil::registerStatus($this->__('Sourcestrings from .pot-File imported'));
        } elseif ($filetype == 'po') {
            ModUtil::apiFunc(
                $this->name,
                'Import',
                'importFromPo',
                array(
                    'mod_id' => $mod_id,
                    'file' => $file,
                    'language' => $language,
                )
            );
            LogUtil::registerStatus($this->__('Translation from .po-File imported'));
        }
        
        $this->redirect(ModUtil::url($this->name, 'admin', 'importTranslations'));
    }
    
    /**
     * Configuration of the Modules to translate.
     *
     * Parameters passed to the Template
     * -----------------------------------
     * * array avaiableModules All avaiable modules
     *
     * @return string The rendered template output
     */
    public function configModules()
    {
        // Security Check
        if (!SecurityUtil::checkPermission('Translator::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        
        $avaiableModules = ModUtil::apiFunc('Extensions', 'Admin', 'listmodules', array('state' => ModUtil::STATE_ACTIVE));
        $modulesVar = $this->getVar('translatorModules');
        
        if (!is_array($modulesVar)) {
            $modulesVar = array();
        }
        
        foreach ($avaiableModules as $key => $val) {
            $tmpkey = array_search($val['id'], $modulesVar);
            
            if ($tmpkey !== false) {
                $avaiableModules[$key]['active'] = true;
            } else {
                $avaiableModules[$key]['active'] = false;
            }
        }
        
        return $this->view
            ->assign('avaiableModules', $avaiableModules)
            ->fetch('admin/configModules.tpl');
    }
    
    /**
     * Processes the changes on the Module configuration.
     *
     * Redirects to configModules-Controller.
     *
     * Parameters passed via POST
     * ---------------------------------
     * * array modules The modules to translate
     *
     * @return void
     */
    public function storeConfigModules()
    {
        // Security Check
        if (!SecurityUtil::checkPermission('Translator::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        
        if ($this->request->isPost()) {
            $modules = $this->request->request->get('modules', null);
            
            if (!empty($modules) && is_array($modules)) {
                $this->delVar('translatorModules');
                $this->setVar('translatorModules', $modules);
            }
            
            LogUtil::registerStatus($this->__('Configuration successfully updated'));
        }
        
        $this->redirect(ModUtil::url($this->name, 'admin', 'configModules'));
    }
    
    /**
     * Configuration of the Languages to translate.
     *
     * Parameters passed to the Template
     * -----------------------------------
     * * array allLanguages All possible languages
     *
     * @return string The rendered template output
     */
    public function configLanguages()
    {
        // Security Check
        if (!SecurityUtil::checkPermission('Translator::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        
        $translationLanguages = $this->getVar('translationLanguages');
        $allLanguages = ZLanguage::languageMap();
        
        foreach ($allLanguages as $key => $val) {
            if (strlen($key) == 2) {
                $allLanguages[$key] = array(
                    'desc' => $val,
                    'selected' => false,
                );
                
                if (!empty($translationLanguages)) {
                    if (in_array($key, $translationLanguages)) {
                        $allLanguages[$key]['selected'] = true;
                    }
                }
            } else {
                unset($allLanguages[$key]);
            }
        }
        
        return $this->view
            ->assign('allLanguages', $allLanguages)
            ->fetch('admin/configLanguages.tpl');
    }
    
    /**
     * Processes the changes on the Language configuration.
     *
     * Redirects to configLanguages-Controller.
     *
     * Parameters passed via POST
     * ---------------------------------
     * * array translationLanguages All languages to translate into
     *
     * @return void
     */
    public function storeLanguages()
    {
        // Security Check
        if (!SecurityUtil::checkPermission('Translator::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }
        
        if ($this->request->isPost()) {
            $translationLanguages = $this->request->request->get('translationLanguages');
            
            $this->delVar('translationLanguages');
            $this->setVar('translationLanguages', $translationLanguages);
            
            LogUtil::registerStatus($this->__('Configuration successfully updated'));
        }
        
        $this->redirect(ModUtil::url($this->name, 'admin', 'configLanguages'));
    }
}
