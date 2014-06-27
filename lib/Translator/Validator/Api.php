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
 * Validator for Api Classes
 */
class Translator_Validator_Api
{
    /**
     * Validates if the given keys in $argsArray are set and not empty
     *
     * @param array $argsArray Referential pointer to the Array that should be validated
     * @param array $keys Array containing the keynames to be validated
     */
    public function hasValues(array &$argsArray, array $keys)
    {
        $this->checkIsset($argsArray, $keys);
        $this->checkNotEmpty($argsArray, $keys);
    }

    /**
     * Validates if the given keys are set in $argsArray
     *
     * @param array $argsArray Referential pointer to the Array that should be validated
     * @param array $keys Array containing the keynames to be validated
     * @throws Zikula_Exception_Fatal Thrown if a key is missing in $argsArray
     */
    private function checkIsset(array &$argsArray, array $keys)
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $argsArray) || !isset($argsArray[$key])) {
                throw new Zikula_Exception_Fatal();
            }
        }
    }

    /**
     * Validates if the keys' value in $argsArray is not empty
     *
     * @param array $argsArray Referential pointer to the Array that should be validated
     * @param array $keys Array containing the keynames to be validated
     * @throws Zikula_Exception_Fatal Thrown if a value is empty
     */
    private function checkNotEmpty(array &$argsArray, array $keys)
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $argsArray) || empty($argsArray[$key])) {
                throw new Zikula_Exception_Fatal();
            }
        }
    }
}
