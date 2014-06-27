<?php
/**
 * Translator module for Zikula
 *
 * @author Christian Deinert
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License (GPL) 3.0
 * @package Translator
 * @subpackage Validator
 */

/**
 * Individual validator for Translation Api Class
 */
class Translator_Validator_ApiTranslation extends Translator_Validator_Api
{
    public function checkCountAllParams(array &$argsArray)
    {
        if (!isset($args['searchfor'])) {
            $args['searchfor'] = null;
        }
        if (!isset($args['searchby']) || empty($args['searchby'])) {
            $args['searchby'] = 'sourcestring';
        }
        if (!isset($args['mod']) || empty($args['mod'])) {
            $args['module'] = '';
        }
    }
    
    public function checkGetAllParams(array &$argsArray)
    {
        $this->checkCountAllParams($argsArray);
        
        if (!isset($argsArray['startnum'])) {
            $argsArray['startnum'] = -1;
        }
        if (!isset($argsArray['itemsperpage'])) {
            $argsArray['itemsperpage'] = ModUtil::getVar('Translator', 'itemsperpage', 20);
        }
        if (!isset($argsArray['sort'])) {
            $argsArray['sort'] = 'trans_id';
        }
        if (!isset($argsArray['sortdir'])) {
            $argsArray['sortdir'] = 'asc';
        }
    }
    
    public function checkSaveParams(array &$argsArray)
    {
        $this->hasValues($argsArray, array('trans_id', 'language'));
        
        if (!isset($args['targetstring']) || $args['targetstring'] == null) {
            $args['targetstring'] = '';
        }
    }
}
