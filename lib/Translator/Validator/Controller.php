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
 * Validators for Controller Classes
 */
class Translator_Validator_Controller
{
    /**
     * Validates if the given keys in $argsArray are avaiable and not null
     *
     * @param array $argsArray Referential pointer to the Array that should be validated
     * @param array $keys Array containing the keynames to be validated
     * @throws Zikula_Exception_Fatal Thrown if a key is not in $argsArray or if the value is null
     */
    public function checkNotNull(array &$dataArray, array $keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $dataArray)) {
                if ($dataArray[$key] == null) {
                    throw new Zikula_Exception_Forbidden();
                } else {
                    return true;
                }
            } else {
                throw new Zikula_Exception_Forbidden();
            }
        }
    }
}
