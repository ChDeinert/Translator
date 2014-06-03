<?php
/**
 * Translator module for Zikula
 *
 * @author Christian Deinert
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License (GPL) 3.0
 * @package Translator
 * @subpackage Api
 */

/**
 * Administrative API functions for the Translator module.
 */
class Translator_Api_Admin extends Zikula_AbstractApi
{
    /**
     * Gets Modules activated for translation
     *
     * Returns the Module-ID and Module-Displayname
     * for the modules activated for Usage with the Translator Module
     *
     * @return array
     */
    public function getModules()
    {
        $configuredModules = $this->getVar('translatorModules');
        $modules = array();
        
        if (!is_array($configuredModules)) {
            $modules = array();
        } else {
            foreach ($configuredModules as $key => $val) {
                $moduleInfo = ModUtil::getInfo($val);
                $modules[] = array(
                    'mod_id' => $val,
                    'modname' => $moduleInfo['displayname'],
                );
            }
        }
        
        return $modules;
    }
    
    /**
     * Gets available admin panel links.
     *
     * Returns an Array with available admin panel links
     *
     * @return array
     */
    public function getlinks()
    {
        $links = array();
        
        $links[] = array(
            'url' => ModUtil::url('Translator', 'admin', 'view'),
            'text' => $this->__('Available Translations'),
            'class' => 'z-icon-es-view',
            'links' => $this->getSecondaryLinks(),
        );
        $links[] = array(
            'url' => ModUtil::url('Translator', 'admin', 'configLanguages'),
            'text' => $this->__('Configure translation languages'),
            'class' => 'z-icon-es-locale',
        );
        $links[] = array(
            'url' => ModUtil::url('Translator', 'admin', 'configModules'),
            'text' => $this->__('Configure translation modules'),
            'class' => 'z-icon-es-config',
        );
        
        return $links;
    }
    
    /**
     * Gets available secondary/dropdown admin panel links
     *
     * Returns an Array with available secondary/dropdown admin panel links
     *
     * @return array
     */
    public function getSecondaryLinks()
    {
        $links = array();
        
        $links[] = array(
            'url' => ModUtil::url('Translator', 'admin', 'view'),
            'text' => $this->__('View Available Translations'),
            'class' => 'z-icon-es-view',
        );
        $links[] = array(
            'url' => ModUtil::url('Translator', 'admin', 'edit'),
            'text' => $this->__('Edit Available Translations'),
            'class' => 'z-icon-es-edit',
        );
        $links[] = array(
            'url' => ModUtil::url('Translator', 'admin', 'exportTranslations'),
            'text' => $this->__('Export Translations'),
            'class' => 'z-icon-es-export',
        );
        $links[] = array(
            'url' => ModUtil::url('Translator', 'admin', 'addNewTranslations'),
            'text' => $this->__('Add New Translation Strings'),
            'class' => 'z-icon-es-search',
        );
        $links[] = array(
            'url' => ModUtil::url('Translator', 'admin', 'importTranslations'),
            'text' => $this->__('Import Translation from Module'),
            'class' => 'z-icon-es-import',
        );
        
        return $links;
    }
}
