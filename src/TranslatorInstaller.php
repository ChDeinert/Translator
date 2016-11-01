<?php
namespace ChDeinert\Translator;

use Zikula_AbstractInstaller

/**
 * Translator installer.
 */
class TranslatorInstaller extends Zikula_AbstractInstaller
{
    public function install()
    {
        return true;
    }

    public function upgrade($oldversion)
    {
        return true;
    }

    public function uninstall()
    {
        return true;
    }
}
