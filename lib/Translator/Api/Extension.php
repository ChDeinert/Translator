<?php
/**
 * Translator module for Zikula
 *
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License (GPL) 3.0
 * @package Translator
 * @subpackage Api
 */

/**
 * This Class provides the Database actions for the installed Extensions/Modules
 */
class Translator_Api_Extension extends Translator_AbstractApi
{
    /**
     * Returns all avaiable modules in Zikula
     *
     * @return array
     */
    public function all()
    {
        $where = '';
        $order = 'name asc';
        $extensions = DBUtil::selectObjectArray('modules', $where, $order);

        return $extensions;
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
