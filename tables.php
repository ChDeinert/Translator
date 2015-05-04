<?php
/**
 * Translator module for Zikula
 *
 * @author Christian Deinert
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License (GPL) 3.0
 * @package Translator
 */

/**
 * Gets table information for the Translator module.
 *
 * Returns (legacy) table information for the Translator module.
 *
 * @return array
 */
function Translator_tables()
{
    // Initialise table array
    $dbtable = array();

    // Table containing the Translations of the Modules
    $dbtable['translator_moduletranslations'] = 'translator_moduletranslations';
    $dbtable['translator_moduletranslations_column'] = [
        'id'           => 'id',
        'module_id'    => 'module_id',
        'sourcestring' => 'sourcestring',
        'in_use'       => 'in_use',
        'ignore_msgid' => 'ignore_msgid',
    ];
    $dbtable['translator_moduletranslations_column_def'] = [
        'id'           => "I UNSIGNED AUTO PRIMARY",
        'module_id'    => "I UNSIGNED NOTNULL DEFAULT 0",
        'sourcestring' => "X",
        'in_use'       => "I(1) DEFAULT 1",
        'ignore_msgid' => "I(1) DEFAULT 0",
    ];

    // Table containing Module-Translation connections
    $dbtable['translator_modtrans'] = 'translator_modtrans';
    $dbtable['translator_modtrans_column'] = [
        'transmod_id'  => 'transmod_id',
        'trans_id'     => 'trans_id',
        'mod_id'       => 'mod_id',
        'in_use'       => 'in_use',
    ];
    $dbtable['translator_modtrans_column_def'] = [
        'transmod_id'  => 'I UNSIGNED AUTO PRIMARY',
        'trans_id'     => 'I UNSIGNED NOTNULL DEFAULT 0',
        'mod_id'       => 'I UNSIGNED NOTNULL DEFAULT 0',
        'in_use'       => 'I(1) DEFAULT 1',
    ];

    // Table containing the Gettext MsgIDs
    $dbtable['translator_translations'] = 'translator_translations';
    $dbtable['translator_translations_column'] = [
        'trans_id'     => 'trans_id',
        'sourcestring' => 'sourcestring',
    ];
    $dbtable['translator_translations_column_def'] = [
        'trans_id'     => 'I UNSIGNED AUTO PRIMARY',
        'sourcestring' => 'X',
    ];

    // Table containing the Gettext MsgStrs for each configured language
    $dbtable['translator_translations_lang'] = 'translator_translations_lang';
    $dbtable['translator_translations_lang_column'] = [
        'lang_id'      => 'lang_id',
        'trans_id'     => 'trans_id',
        'language'     => 'language',
        'targetstring' => 'targetstring',
    ];
    $dbtable['translator_translations_lang_column_def'] = [
        'lang_id'      => 'I UNSIGNED AUTO PRIMARY',
        'trans_id'     => 'I UNSIGNED NOTNULL',
        'language'     => 'C(10)',
        'targetstring' => 'X',
    ];
    $dbtable['translator_translations_lang_column_idx'] = [
        'Index1'       => ['trans_id', 'language'],
    ];

    // Table containing the Occurrences of the MsgIDs in translator_translations Table
    $dbtable['translator_translations_occurrences'] = 'translator_translations_occurrences';
    $dbtable['translator_translations_occurrences_column'] = [
        'transmod_id'  => 'transmod_id',
        'file'         => 'file',
        'line'         => 'line',
    ];
    $dbtable['translator_translations_occurrences_column_def'] = [
        'transmod_id'  => "I UNSIGNED NOTNULL",
        'file'         => "C(255) NOTNULL",
        'line'         => "I UNSIGNED NOTNULL",
    ];

    return $dbtable;
}
