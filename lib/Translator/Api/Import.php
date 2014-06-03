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
 * This Class provides the API for the Import
 */
class Translator_Api_Import extends Zikula_AbstractApi
{
    /**
     * Imports msgids from a .pot file
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * * int    mod_id  The id of the module
     * * string file    The .pot file
     *
     * @param array $args All arguments passed to this function
     * @throws Zikula_Exception_Fatal Thrown if the parameters 'mod_id', 'file' are not set or empty
     * @return boolean True on success; otherwise false.
     */
    public function importFromPot($args)
    {
        if (!isset($args['mod_id']) || empty($args['mod_id']) || !isset($args['file']) || empty($args['file'])) {
            throw new Zikula_Exception_Fatal();
        }
        
        if (file_exists($args['file'])) {
            $translations = array();
            $filehandler = fopen($args['file'], 'r');
            $msgid = "";
            $id = false;
            $occurrences = array();
            
            while (($line = fgets($filehandler)) !== false) {
                if (substr($line, 0, 2) == '#:') {
                    $occurrence = substr($line, 2);
                    $occurrence = explode(':', $occurrence);
                    $occurrences[] = array(
                        'file' => $occurrence[0],
                        'line' => $occurrence[1],
                    );
                }
                
                if (substr($line, 0, 5) == 'msgid') {
                    $id = true;
                    $msgid = substr(trim($line), 7, -1);
                    $str = false;
                } elseif ($id && substr($line, 0, 6) != 'msgstr') {
                    $msgid .= substr(trim($line), 1, -1);
                }
                
                if (substr($line, 0, 6) == 'msgstr') {
                    $id = false;
                }
                
                if (trim($line) == '') {
                    if ($msgid != '') {
                        $translations[] = array(
                            'occurrences' => $occurrences,
                            'msgid' => $msgid,
                        );
                    }
                    
                    $msgid = "";
                    $id = false;
                    $occurrences = array();
                }
            }
            
            fclose($filehandler);
            $this->savePotStrings($args['mod_id'], $translations);
            
            return true;
        } else {
            LogUtil::registerError($this->__('Could not fild pot-File'));
            
            return false;
        }
    }
    
    /**
     * Imports msgids and msgstrs from a .po file.
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * * int    mod_id      The id of the module
     * * string file        The .po file
     * * string language    The language
     *
     * @uses Translator_Api_Import::savePotStrings()
     * @param array $args All arguments passed to this function
     * @throws Zikula_Exception_Fatal Thrown if the parameters 'mod_id', 'file', 'language' are not set or empty
     * @return boolean True on success; otherwise false;
     */
    public function importFromPo($args)
    {
        if (
            !isset($args['mod_id'])
            || empty($args['mod_id'])
            || !isset($args['file'])
            || empty($args['file'])
            || !isset($args['language'])
            || empty($args['language'])
        ) {
            throw new Zikula_Exception_Fatal();
        }
        
        if (file_exists($args['file'])) {
            $translations = array();
            $filehandler = fopen($args['file'], 'r');
            $msgid = "";
            $id = false;
            $msgstr = "";
            $str = false;
            $occurrences = array();
            
            while (($line = fgets($filehandler)) !== false) {
                if (substr($line, 0, 2) == '#:') {
                    $occurrence = substr($line, 2);
                    // Remove whitespace and new line characters.
                    $occurrence = trim($occurrence);
                    if (substr_count($occurrence, ':') > 1) {
                        // Example:
                        // lib/EventManager/Util.php:382 templates/Admin/FilterUsers.tpl:6
                        $occurrence = preg_split('#:|(\d+)#', $occurrence, -1,  PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
                        for ($i = 0; $i < count($occurrence) / 2; $i++) {
                            $occurrences[] = array(
                                'file' => $occurrence[$i * 2],
                                'line' => $occurrence[$i * 2 + 1],
                            );
                        }
                    } else {
                        // Example:
                        // lib/EventManager/Util.php:280
                        $occurrence = explode(':', $occurrence);
                        $occurrences[] = array(
                            'file' => $occurrence[0],
                            'line' => $occurrence[1],
                        );
                    }
                }
                
                if (substr($line, 0, 5) == 'msgid') {
                    $id = true;
                    $msgid = substr(trim($line), 7, -1);
                    $str = false;
                } elseif ($id && substr($line, 0, 6) != 'msgstr') {
                    $msgid .= substr(trim($line), 1, -1);
                }
                
                if (substr($line, 0, 6) == 'msgstr') {
                    $id = false;
                    $msgstr = substr(trim($line), 8, -1);
                    $str = true;
                } elseif ($str && trim($line) !== '') {
                    $msgstr .= substr(trim($line), 1, -1);
                }
                
                if (trim($line) == '') {
                    if ($msgid != '') {
                        $translations[] = array(
                            'occurrences' => $occurrences,
                            'msgid' => $msgid,
                            'msgstr' => $msgstr,
                        );
                    }
                    
                    $msgid = "";
                    $id = false;
                    $msgstr = "";
                    $str = false;
                    $occurrences = array();
                }
            }
            
            fclose($filehandler);
            
            $this->savePoStrings($args['mod_id'], $args['language'], $translations);
            
            return true;
        } else {
            LogUtil::registerError($this->__('Could not fild pot-File'));
            
            return false;
        }
    }
    
    /**
     * Writes msgids into the Database.
     *
     * @param int $mod_id The id of the module
     * @param array $translations All msgids to write into database
     * @throws Zikula_Exception_Fatal Thrown if $mod_id, $translations are empty
     * @return void
     */
    public function savePotStrings($mod_id, $translations)
    {
        if (!isset($mod_id) || !isset($translations) || !is_array($translations)) {
            throw new Zikula_Exception_Fatal();
        }
        
        foreach ($translations as $translation) {
            $transObj = DBUtil::selectExpandedObject(
                'translator_translations',
                array(),
                " sourcestring='".str_replace("'", "####", str_replace("\\", "++++", $translation['msgid']))."'"
            );
            
            if (empty($transObj)) {
                $newObj = array(
                    'sourcestring' => str_replace("'", "####", str_replace("\\", "++++",$translation['msgid'])),
                );
                $newObj = DBUtil::insertObject($newObj, 'translator_translations', 'trans_id');
                $trans_id = $newObj['trans_id'];
            } else {
                $trans_id = $transObj['trans_id'];
            }
            
            $modtransObj = DBUtil::selectExpandedObject(
                'translator_modtrans',
                array(),
                ' mod_id='.$mod_id.' and trans_id='.$trans_id
            );
            
            if (empty($modtransObj)) {
                $newObj = array(
                    'trans_id' => $trans_id,
                    'mod_id' => $mod_id,
                    'in_use' => 1,
                );
                $newObj = DBUtil::insertObject($newObj, 'translator_modtrans', 'transmod_id');
                $transmod_id = $newObj['transmod_id'];
            } else {
                $transmod_id = $modtransObj['transmod_id'];
            }
            
            foreach ($translation['occurrences'] as $occurrence) {
                $occObj = DBUtil::selectExpandedObject(
                    'translator_translations_occurrences',
                    array(),
                    "transmod_id=$transmod_id and file='".$occurrence['file']."' and line=".$occurrence['line']
                );
                
                if (empty($occObj)) {
                    DBUtil::executeSQL("insert into translator_translations_occurrences values ".
                        "($transmod_id, '".$occurrence['file']."', ".$occurrence['line'].")");
                }
            }
        }
    }
    
    /**
     * Writes msgids and msgstrs into the Database.
     *
     * @param int $mod_id The id of the module
     * @param string $language The language of the translation
     * @param array $translations All msgids and msgstrs to write into database
     * @throws Zikula_Exception_Fatal Throen if $mod_id, $language, $translations are empty
     * @return void
     */
    public function savePoStrings($mod_id, $language, $translations)
    {
        if (!isset($mod_id) || !isset($language) || !isset($translations) || !is_array($translations)) {
            throw new Zikula_Exception_Fatal();
        }
        
        foreach ($translations as $translation) {
            $transObj = DBUtil::selectExpandedObject(
                'translator_translations',
                array(),
                " sourcestring='".str_replace("'", "####", str_replace("\\", "++++",$translation['msgid']))."'"
            );
            
            if (empty($transObj)) {
                $newObj = array(
                    'sourcestring' => str_replace("'", "####", str_replace("\\", "++++",$translation['msgid'])),
                );
                $newObj = DBUtil::insertObject($newObj, 'translator_translations', 'trans_id');
                $trans_id = $newObj['trans_id'];
            } else {
                $trans_id = $transObj['trans_id'];
            }
            
            $langObj = DBUtil::selectExpandedObject(
                'translator_translations_lang',
                array(),
                " trans_id=".$trans_id." and `language`='".$language."' "
            );
            
            if (empty($langObj)) {
                $newObj = array(
                    'trans_id' => $trans_id,
                    'language' => $language,
                    'targetstring' => str_replace("'", "####", str_replace("\\", "++++",$translation['msgstr'])),
                );
                $newObj = DBUtil::insertObject($newObj, 'translator_translations_lang', 'lang_id');
            }
            
            $modtransObj = DBUtil::selectExpandedObject(
                'translator_modtrans',
                array(),
                ' mod_id='.$mod_id.' and trans_id='.$trans_id
            );
            
            if (empty($modtransObj)) {
                $newObj = array(
                    'trans_id' => $trans_id,
                    'mod_id' => $mod_id,
                    'in_use' => 1,
                );
                $newObj = DBUtil::insertObject($newObj, 'translator_modtrans', 'transmod_id');
                $transmod_id = $newObj['transmod_id'];
            } else {
                $transmod_id = $modtransObj['transmod_id'];
            }
            
            foreach ($translation['occurrences'] as $occurrence) {
                $occObj = DBUtil::selectExpandedObject(
                    'translator_translations_occurrences',
                    array(),
                    "transmod_id=$transmod_id and file='".$occurrence['file']."' and line=".$occurrence['line']
                );
                
                if (empty($occObj)) {
                    DBUtil::executeSQL("insert into translator_translations_occurrences values ".
                        "($transmod_id, '".$occurrence['file']."', ".$occurrence['line'].")");
                }
            }
        }
    }
}
