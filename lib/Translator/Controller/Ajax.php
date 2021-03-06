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
 * Ajax operations.
 */
class Translator_Controller_Ajax extends Zikula_Controller_AbstractAjax
{
    /**
     * Starts the search for new msgids in the passed module and returns the found msgids
     *
     * Parameters passed via POST
     * -----------------------------------
     * * int    mod     The ID of the module to search in
     *
     * @return Zikula_Response_Ajax Containing an array with the msgids found in the current module
     */
    public function searchTranslations()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Translator::', '::', ACCESS_ADMIN));
        $mod = $this->request->request->get('mod', null);
        
        if ($mod == null) {
            $result = false;
        } else {
            $result = ModUtil::apiFunc($this->name, 'Translation', 'addStrings2Translate', array('mod' => $mod));
        }
        
        return new Zikula_Response_Ajax(array('result' => $result));
    }
}
