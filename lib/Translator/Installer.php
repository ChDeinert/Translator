<?php
/**
 * Translator module for Zikula
 *
 * @author Christian Deinert
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License (GPL) 3.0
 * @package Translator
 */

/**
 * Translator module installer.
 */
class Translator_Installer extends Zikula_AbstractInstaller
{
    /**
     * Provides an array containing default values for module variables (settings).
     *
     * @return array An array indexed by variable name containing the default values for those variables.
     */
    protected function getDefaultModVars()
    {
        return [
            'itemsperpage'         => 50,
            'translationLanguages' => ZLanguage::getInstalledLanguages(),
        ];
    }

    /**
     * Install the Translator module.
     *
     * @return boolean True on success or false on failure.
     */
    public function install()
    {
        if (!DBUtil::createTable('translator_modtrans')) {
            return false;
        }
        if (!DBUtil::createTable('translator_translations')) {
            return false;
        }
        if (!DBUtil::createTable('translator_translations_lang')) {
            return false;
        }
        if (!DBUtil::createTable('translator_translations_occurrences')) {
            return false;
        }

        $this->setVars($this->getDefaultModVars());

        // Installation successful
        return true;
    }

    /**
     * Upgrade the Translator module from an old version.
     *
     * @param string $oldversion The version from which the upgrade is beginning (the currently installed version);
     * @return boolean True on success or false on failure.
     */
    public function upgrade($oldversion)
    {
        /*
        switch ($oldversion) {
            case '1.0.0':

        }
        */
        // Update successful
        return true;
    }

    /**
     * Delete the Translator module.
     *
     * @return boolean True on success or false on failure.
     */
    public function uninstall()
    {
        if (!DBUtil::dropTable('translator_modtrans')) {
            return false;
        }
        if (!DBUtil::dropTable('translator_translations')) {
            return false;
        }
        if (!DBUtil::dropTable('translator_translations_lang')) {
            return false;
        }
        if (!DBUtil::dropTable('translator_translations_occurrences')) {
            return false;
        }

        // Delete any module variables
        $this->delVars();

        // Deletion successful
        return true;
    }
}
