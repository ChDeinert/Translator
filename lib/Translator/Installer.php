<?php
/**
 * Translator module for Zikula
 *
 * @author Christian Deinert
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License (GPL) 3.0
 * @package Translator
 */

/**
 * Translator module installer.
 */
class Translator_Installer extends Zikula_AbstractInstaller
{
    /**
     * Provides an array containing default values for module variables (settings).
     *
     * @return array An array indexed by variable name containing the default values for those variables.
     */
    protected function getDefaultModVars()
    {
        return [
            'itemsperpage'         => 50,
            'translationLanguages' => ZLanguage::getInstalledLanguages(),
        ];
    }

    /**
     * Install the Translator module.
     *
     * @return boolean True on success or false on failure.
     */
    public function install()
    {
        if (!DBUtil::createTable('translator_moduletranslations')) {
            return false;
        }
        /*
        if (!DBUtil::createTable('translator_modtrans')) {
            return false;
        }
        if (!DBUtil::createTable('translator_translations')) {
            return false;
        }
        */
        if (!DBUtil::createTable('translator_translations_lang')) {
            return false;
        }
        if (!DBUtil::createTable('translator_translations_occurrences')) {
            return false;
        }

        $this->setVars($this->getDefaultModVars());

        // Installation successful
        return true;
    }

    /**
     * Upgrade the Translator module from an old version.
     *
     * @param string $oldversion The version from which the upgrade is beginning (the currently installed version);
     * @return boolean True on success or false on failure.
     */
    public function upgrade($oldversion)
    {
        switch ($oldversion) {
            case '1.0.0':
            case '1.0.1':
            case '1.0.2':
            case '1.0.3':
                if (!$this->to110()) {
                    return false;
                }
        }

        // Update successful
        return true;
    }

    /**
     * Delete the Translator module.
     *
     * @return boolean True on success or false on failure.
     */
    public function uninstall()
    {
        if (!DBUtil::dropTable('translator_moduletranslations')) {
            return false;
        }
        /*
        if (!DBUtil::dropTable('translator_modtrans')) {
            return false;
        }
        if (!DBUtil::dropTable('translator_translations')) {
            return false;
        }
        */
        if (!DBUtil::dropTable('translator_translations_lang')) {
            return false;
        }
        if (!DBUtil::dropTable('translator_translations_occurrences')) {
            return false;
        }

        // Delete any module variables
        $this->delVars();

        // Deletion successful
        return true;
    }

    /**
     * Upgrade routine to Version 1.1.0
     *
     * @return boolean
     */
    private function to110()
    {
        if (!DBUtil::createTable('translator_moduletranslations')) {
            return false;
        }

        DBUtil::executeSQL("ALTER TABLE translator_moduletranslations
            ADD COLUMN transmod_id_old INT UNSIGNED NULL AFTER ignore_msgid,
            ADD COLUMN trans_id_old INT UNSIGNED NULL AFTER transmod_id_old");
        DBUtil::executeSQL("ALTER TABLE translator_translations_occurrences RENAME TO translator_occurrences_old");
        DBUtil::createTable('translator_translations_occurrences');
        DBUtil::executeSQL("ALTER TABLE translator_translations_lang RENAME TO translator_translations_lang_old");
        DBUtil::createTable('translator_translations_lang');
        $res = DBUtil::executeSQL("select a.transmod_id, a.trans_id, a.mod_id, b.sourcestring
            from translator_modtrans a
                left join translator_translations b
                    on a.trans_id = b.trans_id
            where a.in_use = 1");
        $rows = $res->fetchAll(Doctrine::FETCH_ASSOC);

        foreach ($rows as $row) {
            DBUtil::executeSQL("insert into translator_moduletranslations
                (module_id, sourcestring, in_use, ignore_msgid, transmod_id_old, trans_id_old)
                values
                ({$row['mod_id']}, '{$row['sourcestring']}', 1, 0, {$row['transmod_id']}, {$row['trans_id']})");
        }

        $res = DBUtil::executeSQL("select * from translator_moduletranslations");
        $rows = $res->fetchAll(Doctrine::FETCH_ASSOC);

        foreach ($rows as $row) {
            $res1 = DBUtil::executeSQL("select * from translator_occurrences_old where transmod_id = {$row['transmod_id_old']}");
            $rows1 = $res1->fetchAll(Doctrine::FETCH_ASSOC);
            foreach ($rows1 as $r1) {
                DBUtil::executeSQL("insert into translator_translations_occurrences
                    (transmod_id, file, line)
                    values
                    ({$row['id']}, '{$r1['file']}', {$r1['line']})");
            }
            $res2 = DBUtil::executeSQL("select * from translator_translations_lang_old where trans_id = {$row['trans_id_old']}");
            $rows2 = $res2->fetchAll(Doctrine::FETCH_ASSOC);
            foreach ($rows2 as $r2) {
                DBUtil::executeSQL("insert into translator_translations_lang
                    (trans_id, language, targetstring)
                    values
                    ({$row['id']}, '{$r2['language']}', '{$r2['targetstring']}')");
            }
        }

        DBUtil::executeSQL("ALTER TABLE translator_moduletranslations DROP COLUMN trans_id_old, DROP COLUMN transmod_id_old");
        DBUtil::executeSQL("DROP TABLE translator_occurrences_old");
        DBUtil::executeSQL("DROP TABLE translator_translations_lang_old");
        DBUtil::executeSQL("DROP TABLE translator_modtrans");
        DBUtil::executeSQL("DROP TABLE translator_translations");

        return true;
    }
}
