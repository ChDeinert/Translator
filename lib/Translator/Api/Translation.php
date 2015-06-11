<?php
/**
 * Translator module for Zikula
 *
 * @author Christian Deinert, Marco Hörenz
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License (GPL) 3.0
 * @package Translator
 * @subpackage Api
 */

use Translator_Helper_Translation as Helper;

/**
 * This Class provides the API for the Translation
 */
class Translator_Api_Translation extends Translator_AbstractApi
{
    /**
     * List of Translation strings to import.
     *
     * @var array
     */
    protected $translatorStrings = array();

    /**
     * Returns all Translations of a Module
     *
     * @param array $args
     * @return array
     */
    public function all(array $args)
    {
        $this->validator->hasValues($args, ['mod_id', 'translation_language']);

        $joinInfo = [];
        $joinInfo[] = [
            'join_method'       => 'LEFT JOIN',
            'join_table'        => 'translator_translations_lang',
            'join_field'        => ['targetstring'],
            'object_field_name' => ['targetstring'],
            'join_where'        => " tbl.id = a.trans_id and language = '{$args['translation_language']}' ",
        ];
        $where = 'module_id = '.$args['mod_id'];
        $items = DBUtil::selectExpandedObjectArray('translator_moduletranslations', $joinInfo, $where);

        foreach ($items as $key => $val) {
            $items[$key]['sourcestring'] = Helper::prepForDisplay($val['sourcestring']);
            $items[$key]['targetstring'] = Helper::prepForDisplay($val['targetstring']);
        }

        return $items;
    }

    /**
     * Returns the Count of all Translations of a Module
     *
     * @param array $args
     * @return array
     */
    public function count(array $args)
    {
        $this->validator->hasValues($args, ['mod_id']);

        $joinInfo = [];
        $where = 'module_id = '.$args['mod_id'];

        return DBUtil::selectExpandedObjectCount('translator_moduletranslations', $joinInfo, $where);
    }

    /**
     * Prepares the database for the search
     *
     * @param array $args
     */
    public function prepSearch(array $args)
    {
        $this->validator->hasValues($args, ['mod_id']);

        DBUtil::executeSQL("update translator_moduletranslations set in_use = 0 where module_id = {$args['mod_id']} ");
        DBUtil::deleteWhere('translator_translations_occurrences', "transmod_id = {$args['mod_id']}");
    }

    /**
     * Scans a file for translations
     *
     * @param array $args
     * @throws Zikula_Exception_Fatal
     * @return array
     */
    public function findUntranslated(array $args)
    {
        $this->validator->hasValues($args, ['mod_id', 'file']);

        if (!file_exists($args['file'])) {
            throw new Zikula_Exception_Fatal();
        }

        $this->processFile($args['mod_id'], $args['file']);

        // Check if the Strings are allready in the database
        foreach ($this->translatorStrings as $key => $val) {
            $existing = $this->checkIfTranslationExists($args['mod_id'], $key, $val);

            if ($existing !== false) {
                unset($this->translatorStrings[$key]);
            }
        }

        $this->addNewStrings2DB($args['mod_id']);

        $items = array();

        foreach ($this->translatorStrings as $key => $val) {
            $items[] = $key;
        }

        return $items;
    }

    /**
     * Returns the Language-Codes the Module has Translations for
     *
     * @param array $args
     * @return array
     */
    public function avaiableLanguages(array $args)
    {
        return $this->getVar('translationLanguages');
    }

    /**
     * Save the translation in DB
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * * int    trans_id                    The db-id of the msgid
     * * string language                    The language
     * * string targetstring    optional    The msgstr
     *
     * @param array $args All arguments passed to this function
     * @throws Zikula_Exception_Fatal Thrown if parameter 'trans_id', 'language' are not set or empty
     * @return void
     */
    public function save($args)
    {
        $this->validator->checkSaveParams($args);

        $where = " trans_id=".$args['trans_id']." and `language`='".$args['language']."' ";
        $langObj = DBUtil::selectExpandedObject('translator_translations_lang', array(), $where);

        if ($langObj != false && isset($langObj['lang_id'])) {
            $updObj = array(
                'lang_id'      => $langObj['lang_id'],
                'targetstring' => Helper::prepForStore($args['targetstring']),
            );

            $updObj = DBUtil::updateObject(
                $updObj,
                'translator_translations_lang',
                ' lang_id='.$langObj['lang_id'],
                'lang_id'
            );

            if ($updObj === false) {
                return LogUtil::registerError($this->__f("Could not update Translation '%s'", $args['targetstring']));
            }
        } else {
            $newObj = array(
                'trans_id'     => $args['trans_id'],
                'language'     => $args['language'],
                'targetstring' => Helper::prepForStore($args['targetstring']),
            );

            $newObj = DBUtil::insertObject($newObj, 'translator_translations_lang', 'lang_id');

            if ($newObj === false) {
                return LogUtil::registerError($this->__f("Could not insert Translation '%s'", $args['targetstring']));
            }
        }
    }

    /**
     * Writes new msgids into the database
     *
     * @return void
     */
    protected function addNewStrings2DB($module_id)
    {
        foreach ($this->translatorStrings as $string => $fileArray) {
            $newObj = [
                'module_id' => $module_id,
                'sourcestring' => Helper::prepForStore($string),
                'in_use' => 1,
                'ignore_msgid' => 0,
            ];
            $newObj = DBUtil::insertObject($newObj, 'translator_moduletranslations', 'id');

            if ($newObj == false) {
                continue;
            }

            foreach ($fileArray as $sourceFile => $lineArray) {
                foreach ($lineArray as $sourceLine => $moduleArray) {

                    // Check occurences
                    $occurence_check = DBUtil::selectExpandedObject(
                        'translator_translations_occurrences',
                        [],
                        "transmod_id = {$newObj['id']}
                         and file = '{$sourceFile}'
                         and line = {$sourceLine}"
                    );

                    if ($occurence_check !== false) {
                        DBUtil::executeSQL("insert into translator_translations_occurrences values
                            ({$newObj['id']}, '{$sourceFile}', {$sourceLine})");
                    }
                }
            }
        }
    }

    /**
     * Processes the Module directory
     *
     * @uses Translator_Api_Translation::processFile()
     * @param id $mod_id The module id
     * @param string $directory The directory to process
     */
    protected function processDirectory($mod_id, $directory)
    {
        if (is_dir($directory)) { // It is a directory
            if ($dirhandler = opendir($directory)) {
                while (($file = readdir($dirhandler)) !== false) {
                    if ($file !== '.' && $file !== '..') {
                        $this->processDirectory($mod_id, $directory.'/'.$file);
                    }
                }
            }
        } else { // Either it is an file or it doesn't exist
            $this->processFile($mod_id, $directory);
        }
    }

    /**
     * Searches for msgids in the Module files
     *
     * @uses Translator_Api_Translation::parseString()
     * @param int $mod_id The module id
     * @param string $file The file
     * @return void
     */
    protected function processFile($mod_id, $file)
    {
        if (file_exists($file)) { // It is a file
            $fileExtension = substr($file, strrpos($file, '.') + 1);

            if (!empty($fileExtension)) {
                $searchstring = "";

                switch (strtolower($fileExtension)) {
                    case 'php':
                    case 'js':
                        $searchstring = '/_gettext\((.*?)\)|_ngettext\((.*?)\)|_dgettext\((.*?)\)|_dngettext\((.*?)\)'.
                            '|_pgettext\((.*?)\)|_dpgettext\((.*?)\)|_npgettext\((.*?)\)|_dnpgettext\((.*?)\)'.
                            '|__\((.*?)\)|_n\((.*?)\)|__f\((.*?)\)|_fn\((.*?)\)|no__\((.*?)\)|__p\((.*?)\)'.
                            '|_np\((.*?)\)|__fp\((.*?)\)|_fnp\((.*?)\)/';
                        break;
                    case 'tpl':
                        $searchstring = '/(<\!--\[|\{)gt{0,}[a-zA-Z0-9](.*?)(\}|\]-->)|(<\!--\[|\{)'.
                            '(.*?)__[a-zA-Z0-9](.*?)(\}|\]-->)/';
                        break;
                }

                if (! empty($searchstring)) {
                    $handle = fopen($file, 'r');
                    $i = 1;

                    while (($line = fgets($handle)) !== false) {
                        $out = array();
                        preg_match_all($searchstring, $line, $out);

                        foreach ($out[0] as $found) {
                            $occurrences = $this->parseString($fileExtension, $found);

                            foreach ($occurrences as $occurrence) {
                                $this->translatorStrings[$occurrence][$file][$i] = array(
                                    'mod_id' => $mod_id,
                                    'file'   => $file,
                                    'line'   => $i,
                                );
                            }
                        }

                        $i++;
                    }

                    fclose($handle);
                }
            }
        }
    }

    /**
     * Searching the occurences
     *
     * @param string $fileExtension The extension to decide how to search for occurrences
     * @param string $string The string to search in
     * @return array The occurrences
     */
    protected function parseString($fileExtension, $string)
    {
        $items = [];

        switch (strtolower($fileExtension)) {
            case 'php':
            case 'js':
                $pos1 = strpos($string, '(');

                if ($pos1 !== false) {
                    $string = substr($string, $pos1 + 1);
                    $strwrapper = substr($string, 0, 1);

                    if ($strwrapper !== '$') {
                        $string = substr($string, 1);
                        $string = str_replace('\\'.$strwrapper, '####', $string);
                        $pos2 = strpos($string, $strwrapper);

                        if ($pos2 !== false) {
                            $string = substr($string, 0, $pos2);

                            if (!empty($string) && !array_key_exists($string, $items)) {
                                $string = str_replace('####', '\\'.$strwrapper, $string);
                                $items[$string] = $string;
                            }
                        }
                    }
                }
                break;
            case 'tpl':
                $string = substr($string, 1, -1);

                // First searching for __???
                $found = strpos($string, '__');

                // Thanks to Marco Hörenz for help with the following part
                if ($found !== false) {
                    $search = array();
                    preg_match_all("/__[a-z-A-Z0-9](.*?)=/", $string, $search);

                    foreach ($search[0] as $tmpString) {
                        $pos1 = strpos($string, $tmpString);
                        $laenge = strlen($tmpString);
                        $reststring = trim(substr($string, $pos1 + $laenge));
                        $steuerzeichen = substr($reststring, 0, 1);
                        $pos2 = strpos(substr($reststring, 1), $steuerzeichen);
                        $uebersetzungsstring = substr($reststring, 1, $pos2);

                        if (! empty($uebersetzungsstring) && ! array_key_exists($uebersetzungsstring, $items)) {
                            $items[$uebersetzungsstring] = $uebersetzungsstring;
                        }
                    }
                }

                // Jetzt die gt text= sachen
                $found = strpos($string, 'gt');

                if ($found !== false) {
                    $tmpString = $string;
                    $pos1 = strpos($tmpString, '=');

                    if ($pos1 !== false) {
                        $tmpString = substr($tmpString, $pos1 + 1);
                        $tmpstrwrapper = substr($tmpString, 0, 1);
                        $tmpString = substr($tmpString, 1);
                        $pos2 = strpos($tmpString, $tmpstrwrapper);

                        if ($pos2 !== false) {
                            $tmpString = substr($tmpString, 0, $pos2);

                            if (!empty($tmpString) && !array_key_exists($tmpString, $items)) {
                                $items[$tmpString] = $tmpString;
                            }
                        }
                    }
                }
                break;
        }

        return $items;
    }

    /**
     * Checks if translations are already available
     *
     * @param string $translationkey
     * @param string $translationArray
     * @return boolean|array
     */
    protected function checkIfTranslationExists($module_id, $translationkey, $translationArray)
    {
        $translationkey = Helper::prepForStore($translationkey);
        $where = "module_id = {$module_id} and sourcestring = BINARY '{$translationkey}' ";
        $translationObj = DBUtil::selectExpandedObject('translator_moduletranslations', [], $where);

        if ($translationObj == false) {
            return false;
        } else {
            foreach ($translationArray as $transFile => $transFileArray) {
                foreach ($transFileArray as $transFileLine => $msgArray) {

                    DBUtil::executeSQL("update translator_moduletranslations set in_use = 1 where id = {$translationObj['id']}");

                    // Check occurences
                    $occurence_check = DBUtil::selectExpandedObject(
                        'translator_translations_occurrences',
                        [],
                        "transmod_id = {$translationObj['id']}
                         and file = '{$msgArray['file']}'
                         and line = {$msgArray['line']}"
                     );

                    if ($occurence_check !== false) {
                        unset($translationArray[$transFile][$transFileLine]);
                    } else {
                        DBUtil::executeSQL("insert into translator_translations_occurrences values
                            ({$translationObj['id']}, '{$msgArray['file']}', {$msgArray['line']})");
                    }

                    if (count($translationArray[$transFile]) == 0) {
                        unset($translationArray[$transFile]);
                    }
                }
            }
        }

        return $translationArray;
    }

    /**
     * Post initialise: called from constructor
     *
     * @see Zikula_AbstractBase::postInitialize()
     */
    protected function postInitialize()
    {
        parent::postInitialize();
        $this->validator = new Translator_Validator_ApiTranslation();
    }
}
