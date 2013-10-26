<?php
/**
 * Translator module for Zikula
 *
 * @author Christian Deinert
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License (GPL) 3.0
 * @package Translator
 */

/**
 * Abstract API for Translator module.
 *
 * Containing Form Utils because the mess that would be created with Zikula core functionality
 */
abstract class Translator_AbstractController extends Zikula_AbstractController
{
    protected function postInitialize()
    {
        $this->view->setCaching(false);
        parent::postInitialize();
    }
    
    protected function assign2View(array &$argArray)
    {
        if (!is_array($argArray)) {
            throw new Zikula_Exception_Fatal();
        }
        
        foreach ($argArray as $key => $val) {
            $this->view->assign($key, $val);
        }
    }
    
    /**
     * Alias for Get with Collection of type 'any'
     *
     * @param array $argArray Referencial array of Arguments.
     * @see ZimpleForm_Util::get()
     * @return void
     */
    protected function getAny(array &$argArray)
    {
        $this->get($argArray, 'any');
    }
    
    /**
     * Alias for Get with Collection of type 'post'
     *
     * @param array $argArray Referencial array of Arguments.
     * @return void
     */
    protected function getPost(array &$argArray)
    {
        self::get($argArray, 'post');
    }
    
    /**
     * Alias for Get with Collection of type 'get'
     *
     * @param array $argArray Referencial array of Arguments.
     * @return void
     */
    protected function getGet(array &$argArray)
    {
        $this->get($argArray, 'get');
    }
    
    /**
     * Changes the given argArray to contain the Values it finds in the used Collection.
     *
     * @param array $argArray Referencial array of Arguments.
     * @param string $option Indicator which type of Collection should used.
     * @throws Zikula_Exception_Fatal Thrown if $argArray is not an array
     * @return void
     */
    protected function get(array &$argArray, $option = 'any')
    {
        if (!is_array($argArray)) {
            throw new Zikula_Exception_Fatal();
        }
        
        $collection = $this->getCollection($option);
        
        foreach ($argArray as $key => $val) {
            $argArray[$key] = array_key_exists($key, $collection) ? $collection[$key] : $val;
        }
    }
    
    /**
     * Returns a Collection of all Input Parameters.
     *
     * @param string $option Indicator which type of Collection should be created.
     * @return array The Collected Input as array.
     */
    protected function getCollection(string $option)
    {
        switch (strtolower($option)) {
            case 'post':
                return $_POST;
            case 'get':
                return $_GET;
            case 'any':
                return array_merge($_POST, $_GET);
            default:
                return array();
        }
    }
}
