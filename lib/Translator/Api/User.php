<?php
/**
 * Translator module for Zikula
 *
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License (GPL) 3.0
 * @package Translator
 * @subpackage Controller
 */

/**
 * Assorted Api-Functions for the Nonadmin Translation
 */
class Translator_Api_User extends Translator_AbstractApi
{
    /**
     * Returns the files of a Module in which to search for untranslated Strings
     *
     * @param array $args
     * @return array
     */
    public function filesInModule(array $args)
    {
        $this->validator->hasValues($args, ['mod_id']);

        $moduleinformations = ModUtil::getInfo($args['mod_id']);
        $files = $this->processDirectory('modules/'.$moduleinformations['directory']);

        return $files;
    }

    /**
     * Post initialise: called from constructor
     *
     * @see Zikula_AbstractBase::postInitialize()
     */
    protected function postInitialize()
    {
        parent::postInitialize();
        $this->validator = new Translator_Validator_Api();
    }

    /**
     * Processes a Directory for files to scan for translation
     *
     * @param string $directory
     * @param array $files
     * @return array
     */
    protected function processDirectory($directory, array $files = array())
    {
        if (is_dir($directory)) { // It is a directory
            if ($dirhandler = opendir($directory)) {
                while (($file = readdir($dirhandler)) !== false) {
                    if ($file !== '.' && $file !== '..') {
                        $files = $this->processDirectory($directory.'/'.$file, $files);
                    }
                }
            }
        } else { // Either it is an file or it doesn't exist
            if (strrpos($directory, '.') !== false) {
                $fileextension = strtolower(substr($directory, strrpos($directory, '.') + 1));

                if ($fileextension == 'php' || $fileextension == 'js' || $fileextension == 'tpl') {
                    $files[] = $directory;
                }
            }
        }

        return $files;
    }
}
