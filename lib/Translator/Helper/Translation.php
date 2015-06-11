<?php
/**
 * Translator module for Zikula
 *
 * @author Christian Deinert
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License (GPL) 3.0
 * @package Translator
 * @subpackage Api
 */

class Translator_Helper_Translation
{
    public static function prepForStore($translationstring)
    {
        return str_replace("'", "####", str_replace("\\", "++++", $translationstring));
    }

    public static function prepForDisplay($translationstring)
    {
        return str_replace("####", "'", str_replace("++++", "\\", $translationstring));
    }
}
