<?php
/**
 * Translator module for Zikula
 *
 * @author Christian Deinert, Marco Hörenz
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License (GPL) 3.0
 * @package Translator
 * @subpackage Api
 */

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
            'join_method'       => 'INNER JOIN',
            'join_table'        => 'translator_modtrans',
            'join_field'        => ['transmod_id'],
            'object_field_name' => ['transmod_id'],
            'join_where'        => " tbl.trans_id=a.trans_id and a.mod_id='{$args['mod_id']}' ",
        ];
        $joinInfo[] = [
            'join_method'       => 'LEFT JOIN',
            'join_table'        => 'translator_translations_lang',
            'join_field'        => ['targetstring'],
            'object_field_name' => ['targetstring'],
            'join_where'        => " tbl.trans_id = b.trans_id and language = '{$args['translation_language']}' ",
        ];
        $where = '';
        $items = DBUtil::selectExpandedObjectArray('translator_translations', $joinInfo, $where);

        foreach ($items as $key => $val) {
            $items[$key]['sourcestring'] = str_replace("####", "'", str_replace("++++", "\\", $val['sourcestring']));
            $items[$key]['targetstring'] = str_replace("####", "'", str_replace("++++", "\\", $val['targetstring']));
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
        $joinInfo[] = [
            'join_method'       => 'INNER JOIN',
            'join_table'        => 'translator_modtrans',
            'join_field'        => [],
            'object_field_name' => [],
            'join_where'        => " tbl.trans_id=a.trans_id and a.mod_id='{$args['mod_id']}' ",
        ];
        $where = '';

        return DBUtil::selectExpandedObjectCount('translator_translations', $joinInfo, $where);
    }

    /**
     * Updates a Translation
     *
     * @param array $args
     */
    public function update(array $args)
    {
        $this->validator->hasValues($args, ['mod_id', 'translation_language']);

        $this->save([
            'language' => $args['translation_language'],
            'trans_id' => $args['trans_id'],
            'targetstring' => $args['trans_val'],
        ]);
    }

    /**
     * Prepares the database for the search
     *
     * @param array $args
     */
    public function prepSearch(array $args)
    {
        $this->validator->hasValues($args, ['mod_id']);

        DBUtil::executeSQL("update translator_modtrans set in_use = 0 where mod_id = ".$args['mod_id']." ");
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
            $existing = $this->checkIfTranslationExists($key, $val);

            if ($existing !== false) {
                unset($this->translatorStrings[$key]);
            }
        }

        $this->addNewStrings2DB();

        $items = array();

        foreach ($this->translatorStrings as $key => $val) {
            $items[] = $key;
        }

        return $items;
    }

    /**
     * Last steps in db after the scan
     *
     * @param array $args
     */
    public function finalizeSearch(array $args)
    {
        $this->validator->hasValues($args, ['mod_id']);

        DBUtil::deleteWhere('translator_modtrans', "mod_id = {$args['mod']} and in_use = 0");
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
     * Get or Count the available Translations
     *
     * @param boolean $countonly Indicates whether to get or to count
     * @param string $searchfor Optional; String to search for
     * @param string $searchby Optional; Database field to search in
     * @param string $mod Optional; The module id
     * @param int $startnum Optional; The offset
     * @param int $itemsperpage Optional; How many items to get
     * @param string $sort Optional; The sort field
     * @param string $sortdir Optional; The sort direction
     * @return array|int
     */
    protected function getOrCountAll(
        $countonly,
        $searchfor = null,
        $searchby = 'sourcestring',
        $mod = '',
        $startnum = -1,
        $itemsperpage = 20,
        $sort = 'trans_id',
        $sortdir = 'asc'
    ) {
        $where = '';

        if ($searchfor !== null) {
            $where .= " $searchby REGEXP '$searchfor' ";
        }

        $joinInfo = array();

        if ($mod != '' && !$countonly) {
            $joinInfo[] = array(
                'join_method'       => 'INNER JOIN',
                'join_table'        => 'translator_modtrans',
                'join_field'        => array('transmod_id'),
                'object_field_name' => array('transmod_id'),
                'join_where'        => " tbl.trans_id=a.trans_id and a.mod_id='$mod' ",
            );
        } elseif ($mod != '' && $countonly) {
            $joinInfo[] = array(
                'join_method'       => 'INNER JOIN',
                'join_table'        => 'translator_modtrans',
                'join_field'        => array(),
                'object_field_name' => array(),
                'join_where'        => " tbl.trans_id=a.trans_id and a.mod_id='$mod' ",
            );
        }

        if ($countonly) {
            $items = DBUtil::selectExpandedObjectCount('translator_translations', $joinInfo, $where);
        } else {
            $items = DBUtil::selectExpandedObjectArray(
                'translator_translations',
                $joinInfo,
                $where,
                $sort.' '.$sortdir,
                $startnum - 1,
                $itemsperpage
            );

            foreach ($items as $key => $item) {
                $items[$key]['sourcestring'] = str_replace("####", "'", str_replace("++++", "\\",$item['sourcestring']));
                $languages = $this->getVar('translationLanguages');

                if (!empty($languages)) {
                    foreach ($languages as $language) {
                        $where = " trans_id = ".$item['trans_id']." and `language`='".$language."' ";
                        $transObj = DBUtil::selectExpandedObject('translator_translations_lang', array(), $where);

                        if ($transObj == false) {
                            $items[$key]['translationAvailable'][$language] = false;
                            $items[$key]['translations'][$language] = '';
                        } else {
                            $items[$key]['translationAvailable'][$language] = true;
                            $items[$key]['translations'][$language] = str_replace("####", "'", str_replace("++++", "\\", $transObj['targetstring']));
                        }
                    }
                }
            }
        }

        return $items;
    }

    /**
     * Get a list of available translations
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * * string searchfor       optional    String to search for
     * * string searchby        optional    DB field to search in
     * * string mod             optional    The module id
     * * int    startnum        optional    The offset
     * * int    itemsperpage    optional    How many items to get
     * * string sort            optional    The sort field
     * * string sortdir         optional    The sort direction
     *
     * @uses Translator_Api_Translation::getOrCountAll()
     * @param array $args All arguments passed to this function
     * @return array The list of available translations
     */
    public function getAll($args)
    {
        $this->validator->checkGetAllParams($args);

        return $this->getOrCountAll(
            false,
            $args['searchfor'],
            $args['searchby'],
            $args['mod'],
            $args['startnum'],
            $args['itemsperpage'],
            $args['sort'],
            $args['sortdir']
        );
    }

    /**
     * Get the count of available translations
     *
     * Parameters passed in the $args array:
     * -------------------------------------
     * * string searchfor   optional    String to search for
     * * string searchby    optional    DB field to search in
     * * string mod         optional    The module id
     *
     * @uses Translator_Api_Translation::getOrCountAll()
     * @param array $args All arguments passed to this function
     * @return int The list of available translations
     */
    public function countAll($args)
    {
        $this->validator->checkCountAllParams($args);

        return $this->getOrCountAll(true, $args['searchfor'], $args['searchby'], $args['mod']);
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
                'lang_id' => $langObj['lang_id'],
                'targetstring' => str_replace("'", "####", str_replace("\\", "++",$args['targetstring'])),
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
                'trans_id' => $args['trans_id'],
                'language' => $args['language'],
                'targetstring' => str_replace("'", "####", str_replace("\\", "++++",$args['targetstring'])),
            );

            $newObj = DBUtil::insertObject($newObj, 'translator_translations_lang', 'lang_id');

            if ($newObj === false) {
                return LogUtil::registerError($this->__f("Could not insert Translation '%s'", $args['targetstring']));
            }
        }
    }

    /**
     * Searches and adds new msgids
     *
     * @uses Translator_Api_Translation::processDirectory()
     * @uses Translator_Api_Translation::checkIfTranslationExists()
     * @uses Translator_Api_Translation::addNewStrings2DB()
     * @return array The newly added msgids
     */
    public function addStrings2Translate($args)
    {
        $this->validator->hasValues($args, array('mod'));

        $modules2process = $this->getVar('translatorModules');

        if (!is_array($modules2process) && !in_array($args['mod'], $modules2process)) {
            throw new Zikula_Exception_Fatal();
        }

        $currentModule_info = ModUtil::apiFunc('Extensions', 'admin', 'modify', array('id' => $args['mod']));
        $modulepath = 'modules/'.$currentModule_info['directory'];
        DBUtil::executeSQL("update translator_modtrans set in_use = 0 where mod_id = ".$args['mod']." ");
        $modtransObj = DBUtil::selectExpandedObjectArray('translator_modtrans', array(), 'mod_id='.$args['mod']);

        if (!empty($modtransObj)) {
            foreach ($modtransObj as $modtrans) {
                DBUtil::deleteWhere('translator_translations_occurrences', 'transmod_id='.$modtrans['transmod_id']);
            }
        }

        $this->processDirectory($args['mod'], $modulepath);
        DBUtil::deleteWhere('translator_modtrans', 'mod_id='.$args['mod'].' and in_use=0');

        // Check if the Strings are allready in the database
        foreach ($this->translatorStrings as $key => $val) {
            $existing = $this->checkIfTranslationExists($key, $val);

            if ($existing !== false) {
                unset($this->translatorStrings[$key]);
            }
        }

        $this->addNewStrings2DB();
        $items = array();

        foreach ($this->translatorStrings as $key => $val) {
            $items[] = $key;
        }

        return $items;
    }

    /**
     * Writes new msgids into the database
     *
     * @return void
     */
    protected function addNewStrings2DB()
    {
        foreach ($this->translatorStrings as $string => $fileArray) {
            $newObj = array('sourcestring' => str_replace("'", "####", str_replace("\\", "++++",$string)));
            $newObj = DBUtil::insertObject($newObj, 'translator_translations', 'trans_id');

            if ($newObj == false) {
                continue;
            }

            foreach ($fileArray as $sourceFile => $lineArray) {
                foreach ($lineArray as $sourceLine => $moduleArray) {
                    $where = ' trans_id='.$newObj['trans_id'].' and mod_id='.$moduleArray['mod_id'];
                    $modtransObj = DBUtil::selectExpandedObject('translator_modtrans', array(), $where);

                    if (empty($modtransObj)) {
                        $modtransObj = array(
                            'trans_id' => $newObj['trans_id'],
                            'mod_id' => $moduleArray['mod_id'],
                        );
                        $modtransObj = DBUtil::insertObject($modtransObj, 'translator_modtrans', 'transmod_id');
                    }

                    if (!empty($modtransObj)) {
                        DBUtil::executeSQL("insert into translator_translations_occurrences values ".
                            "(".$modtransObj['transmod_id'].", '$sourceFile', $sourceLine)");
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
                                    'file' => $file,
                                    'line' => $i,
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
        $items = array();

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
    protected function checkIfTranslationExists($translationkey, $translationArray)
    {
        $translationkey = str_replace("'", "####", str_replace("\\", "++++",$translationkey));
        $where = "sourcestring= BINARY '".$translationkey."' ";
        $translationObj = DBUtil::selectExpandedObject('translator_translations',array(),$where);

        if ($translationObj == false) {
            return false;
        } else {
            foreach ($translationArray as $transFile => $transFileArray) {
                foreach ($transFileArray as $transFileLine => $msgArray) {

                    // Check module-reference
                    $where = "trans_id=".$translationObj['trans_id']." and mod_id=".$msgArray['mod_id'];
                    $modcheck = DBUtil::selectExpandedObject('translator_modtrans',array(),$where);

                    if ($modcheck != false) {
                        DBUtil::executeSQL("update translator_modtrans set in_use = 1 where transmod_id=".$modcheck['transmod_id']);

                        // Check occurences
                        $occurence_check = DBUtil::selectExpandedObject(
                            'translator_translations_occurrences',
                            array(),
                            "transmod_id=".$modcheck['transmod_id']." and file='".$msgArray['file']."' ".
                                "and line=".$msgArray['line']
                        );

                        if ($occurence_check != false) {
                            unset($translationArray[$transFile][$transFileLine]);
                        } else {
                            $insobj = array(
                                'transmod_id' => $modcheck['transmod_id'],
                                'file' => $msgArray['file'],
                                'line' => $msgArray['line'],
                            );
                            $insobj = DBUtil::insertObject($insobj, 'translator_translations_occurrences', 'transocc_id');
                        }
                    } else {
                        $insobj = array(
                            'trans_id' => $translationObj['trans_id'],
                            'mod_id' => $msgArray['mod_id'],
                            'in_use' => 1,
                        );

                        $insobj = DBUtil::insertObject($insobj, 'translator_modtrans', 'transmod_id');
                        $insobj2 = array(
                            'transmod_id' => $insobj['transmod_id'],
                            'file' => $msgArray['file'],
                            'line' => $msgArray['line'],
                        );
                        $insobj2 = DBUtil::insertObject($insobj2, 'translator_translations_occurrences', 'transocc_id');
                        unset($translationArray[$transFile][$transFileLine]);
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
