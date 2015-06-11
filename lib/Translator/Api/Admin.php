<?php
/**
 * Translator module for Zikula
 *
 * @author Christian Deinert
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License (GPL) 3.0
 * @package Translator
 * @subpackage Api
 */

/**
 * Administrative API functions for the Translator module.
 */
class Translator_Api_Admin extends Translator_AbstractApi
{
    /**
     * Gets available admin panel links.
     *
     * Returns an Array with available admin panel links
     *
     * @return array
     */
    public function getlinks()
    {
        $links = [];
        $links[] = [
            'url'   => ModUtil::url('Translator', 'user', 'main'),
            'text'  => $this->__('Available Translations'),
            'class' => 'z-icon-es-view',
        ];
        $links[] = [
            'url'   => ModUtil::url('Translator', 'admin', 'configLanguages'),
            'text'  => $this->__('Configure translation languages'),
            'class' => 'z-icon-es-locale',
        ];

        return $links;
    }
}
