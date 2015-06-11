<?php
/**
 * Translator module for Zikula
 *
 * @author Christian Deinert
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License (GPL) 3.0
 * @package Translator
 * @subpackage Controller
 */

/**
 * Administrative UI-oriented operations.
 */
class Translator_Controller_Admin extends Translator_AbstractController
{
    /**
     * The default entrypoint.
     *
     * This function redirects the user to the function 'view'.
     *
     * @return void
     */
    public function main()
    {
        $this->redirect(ModUtil::url($this->name, 'admin', 'configLanguages'));
    }

    /**
     * Configuration of the Languages to translate.
     *
     * Parameters passed to the Template
     * -----------------------------------
     * * array allLanguages All possible languages
     *
     * @return string The rendered template output
     */
    public function configLanguages()
    {
        $translationLanguages = $this->getVar('translationLanguages');
        $allLanguages = ZLanguage::languageMap();

        foreach ($allLanguages as $key => $val) {
            if (strlen($key) == 2) {
                $allLanguages[$key] = array(
                    'desc'     => $val,
                    'selected' => false,
                );

                if (!empty($translationLanguages)) {
                    if (in_array($key, $translationLanguages)) {
                        $allLanguages[$key]['selected'] = true;
                    }
                }
            } else {
                unset($allLanguages[$key]);
            }
        }

        return $this->view
            ->assign('allLanguages', $allLanguages)
            ->fetch('admin/configLanguages.tpl');
    }

    /**
     * Processes the changes on the Language configuration.
     *
     * Redirects to configLanguages-Controller.
     *
     * Parameters passed via POST
     * ---------------------------------
     * * array translationLanguages All languages to translate into
     *
     * @return void
     */
    public function storeLanguages()
    {
        $data = ['translationLanguages' => null];
        $this->getPost($data);
        $this->validator->checkNotNull($data, ['translationLanguages']);

        $translationLanguages = $this->request->request->get('translationLanguages');

        $this->delVar('translationLanguages');
        $this->setVar('translationLanguages', $translationLanguages);

        LogUtil::registerStatus($this->__('Configuration successfully updated'));

        $this->redirect(ModUtil::url($this->name, 'admin', 'configLanguages'));
    }

    /**
     * Post initialise: called from constructor
     *
     * @see Translator_AbstractController::postInitialize()
     */
    protected function postInitialize()
    {
        parent::postInitialize();
        $this->validator = new Translator_Validator_Controller();

        if (!SecurityUtil::checkPermission('Translator::', '::', ACCESS_ADMIN)) {
            throw new Zikula_Exception_Forbidden();
        }
    }
}
