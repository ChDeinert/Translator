<?php
/**
 * Translator module for Zikula
 *
 * @author Christian Deinert
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License (GPL) 3.0
 * @package Translator
 */

/**
 * Translator module version information and other metadata.
 */
class Translator_Version extends Zikula_AbstractVersion
{
    /**
     * Provides an array of standard Zikula metadata.
     *
     * @return array Zikula Extension metadata.
     */
    public function getMetaData()
    {
        return array(
            'displayname'   => $this->__('Translator'),
            'description'   => $this->__('Gettext Translation-generation'),
            'url'           => 'Translator',
            'version'       => '1.0.1',
            'core_min' => '1.3.5', // Fixed to 1.3.x range
            'core_max' => '1.3.99', // Fixed to 1.3.x range
            'securityschema'=> array('Translator::' => '::'),
        );
    }
}
