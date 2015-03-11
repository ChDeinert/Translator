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
            $this->export2Po(['mod_id' => $args['mod_id'], 'language' => $lang]);
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
        $this->export2Pot($args);
    }

    /**
     * Export the Translationstrings into an .pot file.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * * int mod_id The id of the Module to create the .pot file for
     *
     * @param array $args All arguments passed to this function
     * @throws Zikula_Exception_Fatal Thrown if the parameter 'mod_id' is not set or empty
     * @return void
     */
    public function export2Pot($args)
    {
        $this->validator->hasValues($args, ['mod_id']);

        $modInfo = ModUtil::apiFunc('Extensions', 'admin', 'modify', array('id' => $args['mod_id']));
        $filecontent = '# Automatic generated POT-File.'."\r\n";
        $filecontent .= '# Copyright (C) YEAR THE PACKAGE\'S COPYRIGHT HOLDER'."\r\n";
        $filecontent .= '# This file is distributed under the same license as the PACKAGE package.'."\r\n";
        $filecontent .= '# FIRST AUTHOR <EMAIL@ADDRESS>, YEAR.'."\r\n";
        $filecontent .= '#'."\r\n";
        $filecontent .= 'msgid ""'."\r\n";
        $filecontent .= 'msgstr ""'."\r\n";
        $filecontent .= '"Project-Id-Version: \n"'."\r\n";
        $filecontent .= '"Report-Msgid-Bugs-To: \n"'."\r\n";
        $filecontent .= '"POT-Creation-Date: '.date("Y-m-d H:i:sO").'\n"'."\r\n";
        $filecontent .= '"PO-Revision-Date: '.date("Y-m-d H:i:sO").'\n"'."\r\n";
        $filecontent .= '"Last-Translator: \n"'."\r\n";
        $filecontent .= '"Language-Team:  \n"'."\r\n";
        $filecontent .= '"MIME-Version: 1.0\n"'."\r\n";
        $filecontent .= '"Content-Type: text/plain; charset=UTF-8\n"'."\r\n";
        $filecontent .= '"Content-Transfer-Encoding: 8bit\n"'."\r\n";
        $filecontent .= '"Language: \n"'."\r\n\r\n";
        $modtransArray = DBUtil::selectExpandedObjectArray(
            'translator_modtrans',
            array(),
            ' mod_id='.$args['mod_id'].' and in_use=1',
            'trans_id'
        );

        foreach ($modtransArray as $modtrans) {
            $occurrences = DBUtil::selectExpandedObjectArray(
                'translator_translations_occurrences',
                array(),
                ' transmod_id='.$modtrans['transmod_id'],
                'file'
            );

            foreach ($occurrences as $occurrence) {
                $filecontent .= '#: '.$occurrence['file'].':'.$occurrence['line']."\r\n";
            }

            $sourcetrans = DBUtil::selectExpandedObjectByID(
                'translator_translations',
                array(),
                $modtrans['trans_id'],
                'trans_id'
            );
            $filecontent .= 'msgid "'.$sourcetrans['sourcestring'].'"'."\r\n";
            $filecontent .= 'msgstr ""'."\r\n\r\n";
        }

        $modulepath = 'modules/'.$modInfo['directory'];

        if (!file_exists($modulepath.'/locale') || !is_dir($modulepath.'/locale')) {
            mkdir($modulepath.'/locale');
        }

        $modname_lc = mb_strtolower($modInfo['name']);
        $file = fopen($modulepath.'/locale/module_'.$modname_lc.'.pot', 'w');
        fwrite($file, $filecontent);
        fclose($file);

        if (file_exists($modulepath.'/locale/module_'.$modname_lc.'.pot')) {
            LogUtil::registerStatus($this->__f('Created .pot-File for module %s', $modInfo['displayname']));
        } else {
            LogUtil::registerError($this->__f('Error while creating .pot-File for module %s', $modInfo['displayname']));
        }
    }

    /**
     * Export the Translations into an .po file.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * * int    mod_id      The id of the Module to create the .po file for
     * * string language    The language to export
     *
     * @param array $args All arguments passed to this function
     * @throws Zikula_Exception_Fatal Thrown if the parameters 'mod_id', 'language' are not set or empty
     * @return void
     */
    public function export2Po($args)
    {
        $this->validator->hasValues($args, ['mod_id', 'language']);

        $modInfo = ModUtil::apiFunc('Extensions', 'admin', 'modify', array('id' => $args['mod_id']));
        $filecontent = '# Automatic generated POT-File.'."\r\n";
        $filecontent .= '# Copyright (C) YEAR THE PACKAGE\'S COPYRIGHT HOLDER'."\r\n";
        $filecontent .= '# This file is distributed under the same license as the PACKAGE package.'."\r\n";
        $filecontent .= '# FIRST AUTHOR <EMAIL@ADDRESS>, YEAR.'."\r\n";
        $filecontent .= '#'."\r\n";
        $filecontent .= 'msgid ""'."\r\n";
        $filecontent .= 'msgstr ""'."\r\n";
        $filecontent .= '"Project-Id-Version: \n"'."\r\n";
        $filecontent .= '"Report-Msgid-Bugs-To: \n"'."\r\n";
        $filecontent .= '"POT-Creation-Date: '.date("Y-m-d H:i:sO").'\n"'."\r\n";
        $filecontent .= '"PO-Revision-Date: '.date("Y-m-d H:i:sO").'\n"'."\r\n";
        $filecontent .= '"Last-Translator: \n"'."\r\n";
        $filecontent .= '"Language-Team:  \n"'."\r\n";
        $filecontent .= '"MIME-Version: 1.0\n"'."\r\n";
        $filecontent .= '"Content-Type: text/plain; charset=UTF-8\n"'."\r\n";
        $filecontent .= '"Content-Transfer-Encoding: 8bit\n"'."\r\n";
        $filecontent .= '"Language: '.$args['language'].'\n"'."\r\n\r\n";
        $modtransArray = DBUtil::selectExpandedObjectArray(
            'translator_modtrans',
            array(),
            ' mod_id='.$args['mod_id'].' and in_use=1',
            'trans_id'
        );

        foreach ($modtransArray as $modtrans) {
            $occurrences = DBUtil::selectExpandedObjectArray(
                'translator_translations_occurrences',
                array(),
                ' transmod_id='.$modtrans['transmod_id'],
                'file'
            );

            foreach ($occurrences as $occurrence) {
                $filecontent .= '#: '.$occurrence['file'].':'.$occurrence['line']."\r\n";
            }

            $sourcetrans = DBUtil::selectExpandedObjectByID(
                'translator_translations',
                array(),
                $modtrans['trans_id'],
                'trans_id'
            );
            $filecontent .= 'msgid "'.
                str_replace("####", "'", str_replace("++++", "\\", $sourcetrans['sourcestring'])).
                '"'."\r\n";
            $targettrans = DBUtil::selectExpandedObject(
                'translator_translations_lang',
                array(),
                " trans_id=".$modtrans['trans_id']." and `language`='".$args['language']."' "
            );

            if ($targettrans == false || empty($targettrans) || $targettrans['targetstring'] == '') {
                $targetstring = $sourcetrans['sourcestring'];
            } else {
                $targetstring = $targettrans['targetstring'];
            }

            $filecontent .= 'msgstr "'.str_replace("####", "'", str_replace("++++", "\\", $targetstring)).'"'."\r\n\r\n";
        }

        $modulepath = 'modules/'.$modInfo['directory'];

        if (!file_exists($modulepath.'/locale') || !is_dir($modulepath.'/locale')) {
            mkdir($modulepath.'/locale');
        }

        if (
            !file_exists($modulepath.'/locale/'.$args['language'].'/LC_MESSAGES')
            || !is_dir($modulepath.'/locale/'.$args['language'].'/LC_MESSAGES')
        ) {

            mkdir($modulepath.'/locale/'.$args['language'].'/LC_MESSAGES', 0777, true);
        }

        $modname_lc = mb_strtolower($modInfo['name']);
        $file = fopen($modulepath.'/locale/'.$args['language'].'/LC_MESSAGES/module_'.$modname_lc.'.po', 'w');
        fwrite($file, $filecontent);
        fclose($file);

        if (file_exists($modulepath.'/locale/'.$args['language'].'/LC_MESSAGES/module_'.$modname_lc.'.po')) {
            LogUtil::registerStatus($this->__f('Created .po-File for module %s', $modInfo['displayname']));
            $this->compilePo2Mo(
                $modulepath.'/locale/'.$args['language'].'/LC_MESSAGES/module_'.$modname_lc.'.po',
                $modInfo['displayname']
            );
        } else {
            LogUtil::registerError($this->__f('Error while creating .po-File for module %s', $modInfo['displayname']));
        }
    }

    /**
     * Compile the .po file into a .mo file
     *
     * @param string $file The .po file
     * @param string $moddesc The modules description
     * @throws Zikula_Exception_Fatal Thrown if $file is empty or the file does not exist
     * @return void
     */
    public function compilePo2Mo($file = null, $moddesc)
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
     * Post initialise: called from constructor
     *
     * @see Zikula_AbstractBase::postInitialize()
     */
    protected function postInitialize()
    {
        parent::postInitialize();
        $this->validator = new Translator_Validator_Api();
    }
}
