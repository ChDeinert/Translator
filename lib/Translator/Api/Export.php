<?php
/**
 * Translator module for Zikula
 *
 * @author Christian Deinert
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License (GPL) 3.0
 * @package Translator
 * @subpackage Api
 */

use Translator_Helper_Translation as Helper;

/**
 * This Class provides the API for the Export
 */
class Translator_Api_Export extends Translator_AbstractApi
{
    /**
     * Export to a Po-File and compilation to Mo-File
     *
     * @param array $args
     */
    public function toPo(array $args)
    {
        $this->validator->hasValues($args, ['mod_id']);

        $languages = ModUtil::apiFunc($this->name, 'Translation', 'avaiableLanguages', $args);

        foreach ($languages as $lang) {
            $this->export($args['mod_id'], $lang);
        }
    }

    /**
     * Export to a Pot-File
     *
     * @param array $args
     */
    public function toPot(array $args)
    {
        $this->validator->hasValues($args, ['mod_id']);
        $this->export($args['mod_id']);
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
     * Compile the .po file into a .mo file
     *
     * @param string $file The .po file
     * @param string $moddesc The modules description
     * @throws Zikula_Exception_Fatal Thrown if $file is empty or the file does not exist
     * @return void
     */
    protected function compile($file = null, $moddesc)
    {
        if (empty($file) || !file_exists($file)) {
            throw new Zikula_Exception_Fatal();
        }

        require_once 'modules/Translator/lib/vendor/php-mo/php-mo.php';

        if (phpmo_convert($file)) {
            LogUtil::registerStatus($this->__f('Compiled .po- to .mo-File for module %s', $moddesc));
        } else {
            LogUtil::registerError($this->__f('Error while compiling .po- to .mo-File for module %s', $moddesc));
        }
    }

    /**
     * Export of Translations to a Po or Pot File
     *
     * The decision, whether it's an Po or Pot File depends on the language.
     * If that is null it will be a Pot File. If it's not empty it will be a Po File with compilation to Mo.
     *
     * @param number $mod_id
     * @param string $language
     */
    protected function export($mod_id, $language = null)
    {
        $filecontent = $this->writeHeader($language);
        $modtransArray = DBUtil::selectExpandedObjectArray(
            'translator_moduletranslations',
            [],
            "module_id = {$mod_id} and in_use = 1 and ignore_msgid = 0",
            'id'
        );

        foreach ($modtransArray as $modtrans) {
            $filecontent .= $this->writeOccurrences($modtrans['id']);
            $filecontent .= $this->writeMsgID($modtrans['id']);
            $filecontent .= $this->writeMsgStr($modtrans['id'], $language);
        }

        $modInfo = ModUtil::apiFunc('Extensions', 'admin', 'modify', ['id' => $mod_id]);
        $modulepath = 'modules/'.$modInfo['directory'];
        $this->checkPath($modulepath.'/locale');

        if ($language != null) {
            $this->checkPath($modulepath.'/locale/'.$language.'/LC_MESSAGES');
            $filename = $modulepath.'/locale/'.$language.'/LC_MESSAGES/module_'.mb_strtolower($modInfo['name']).'.po';
        } else {
            $filename = $modulepath.'/locale/module_'.mb_strtolower($modInfo['name']).'.pot';
        }

        $filehandler = fopen($filename, 'w');
        fwrite($filehandler, $filecontent);
        fclose($filehandler);

        if ($this->checkResult($filename, $modInfo, $language) && $language != null) {
            $this->compile($filename, $modInfo['displayname']);
        }
    }

    /**
     * Returns the text that will be written as Header into the Po or Pot File
     *
     * @param string $language
     * @return string
     */
    private function writeHeader($language = null)
    {
        $header = '# Automatic generated PO'.($language == null ? 'T' : '').'-File.'."\r\n";
        $header .= '# Copyright (C) YEAR THE PACKAGE\'S COPYRIGHT HOLDER'."\r\n";
        $header .= '# This file is distributed under the same license as the PACKAGE package.'."\r\n";
        $header .= '# FIRST AUTHOR <EMAIL@ADDRESS>, YEAR.'."\r\n";
        $header .= '#'."\r\n";
        $header .= 'msgid ""'."\r\n";
        $header .= 'msgstr ""'."\r\n";
        $header .= '"Project-Id-Version: \n"'."\r\n";
        $header .= '"Report-Msgid-Bugs-To: \n"'."\r\n";
        $header .= '"POT-Creation-Date: '.date("Y-m-d H:i:sO").'\n"'."\r\n";
        $header .= '"PO-Revision-Date: '.date("Y-m-d H:i:sO").'\n"'."\r\n";
        $header .= '"Last-Translator: \n"'."\r\n";
        $header .= '"Language-Team:  \n"'."\r\n";
        $header .= '"MIME-Version: 1.0\n"'."\r\n";
        $header .= '"Content-Type: text/plain; charset=UTF-8\n"'."\r\n";
        $header .= '"Content-Transfer-Encoding: 8bit\n"'."\r\n";
        $header .= '"Language: '.$language.'\n"'."\r\n\r\n";

        return $header;;
    }

    /**
     * Returns the Occurrences section of one Translation for writing into the File
     *
     * @param number $transmod_id
     * @return string
     */
    private function writeOccurrences($transmod_id)
    {
        $occurrences = DBUtil::selectExpandedObjectArray(
            'translator_translations_occurrences',
            [],
            ' transmod_id='.$transmod_id,
            'file'
        );
        $occurrences_string = '';

        foreach ($occurrences as $occurrence) {
            $occurrences_string .= '#: '.$occurrence['file'].':'.$occurrence['line']."\r\n";
        }

        return $occurrences_string;
    }

    /**
     * Returns the Sourcestring for writing into the File
     *
     * @param number $trans_id
     * @return string
     */
    private function writeMsgID($trans_id)
    {
        $sourcetrans = DBUtil::selectExpandedObjectByID('translator_moduletranslations', [], $trans_id, 'id');
        $msgid_string = 'msgid "'.
            Helper::prepForDisplay($sourcetrans['sourcestring']).
            '"'."\r\n";

        return $msgid_string;
    }

    /**
     * Writes the translated String for writing into the File
     *
     * If the translated String is empty, the sourcestring will be used.
     *
     * @param number $trans_id
     * @param string $language
     * @return string
     */
    private function writeMsgStr($trans_id, $language = null)
    {
        $msgstr_string = 'msgstr "';

        if ($language != null) {
            $targettrans = DBUtil::selectExpandedObject(
                'translator_translations_lang',
                [],
                " trans_id=".$trans_id." and `language`='".$language."' "
            );

            if ($targettrans == false || empty($targettrans) || $targettrans['targetstring'] == '') {
                $sourcetrans = DBUtil::selectExpandedObjectByID('translator_moduletranslations', [], $trans_id, 'id');
                $msgstr_string .= Helper::prepForDisplay($sourcetrans['sourcestring']).'"';
            } else {
                $msgstr_string .= Helper::prepForDisplay($targettrans['targetstring']).'"';
            }
        }

        $msgstr_string .= "\r\n\r\n";

        return $msgstr_string;
    }

    /**
     * Checks if a given Path is avaiable and creates it, if not
     *
     * @param string $path
     */
    private function checkPath($path)
    {
        if (!file_exists($path) || !is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }

    /**
     * Check if the file was created and writing of the Flash-Messages
     *
     * @param string $filename
     * @param array $modInfo
     * @param string $language
     * @return boolean
     */
    private function checkResult($filename, $modInfo, $language = null)
    {
        $filetype = $language != null ? 'Po' : 'Pot';

        if (file_exists($filename)) {
            LogUtil::registerStatus($this->__f('Created %s File for module %s', [$filetype, $modInfo['displayname']]));

            return true;
        } else {
            LogUtil::registerError($this->__f('Error while creating %s File for module %s', [$filetype, $modInfo['displayname']]));

            return false;
        }
    }
}
