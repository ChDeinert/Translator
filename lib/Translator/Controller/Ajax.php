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
     * Scanning of a Module's file for msgids
     *
     * @return Zikula_Response_Ajax
     */
    public function scanFile()
    {
        $this->checkAjaxToken();
        $this->throwForbiddenUnless(SecurityUtil::checkPermission('Translator::', '::', ACCESS_EDIT));

        $mod_id = $this->request->request->get('mod_id', null);
        $file   = $this->request->request->get('file', null);

        if ($mod_id == null || $file == null) {
            $result = false;
            $msg = $this->__('Missing parameters!');
        } else {
            $result = ModUtil::apiFunc($this->name, 'Translation', 'findUntranslated', [
                'mod_id' => $mod_id,
                'file'   => $file,
            ]);
            $msg = '';
        }

        return new Zikula_Response_Ajax(['result' => $result, 'msg' => $msg]);
    }
}
