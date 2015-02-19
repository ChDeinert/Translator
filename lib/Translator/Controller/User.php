<?php
/**
 * Translator module for Zikula
 *
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License (GPL) 3.0
 * @package Translator
 * @subpackage Controller
 */

/**
 * User UI-oriented operations.
 */
class Translator_Controller_User extends Translator_AbstractController
{
    /**
     * Zikula main landing point
     * Redirects to User Controller's viewModules
     */
    public function main()
    {
        $this->redirect(ModUtil::url($this->name, 'User', 'viewModules'));
    }

    /**
     * Selection of the module to translate
     *
     * @return string
     */
    public function viewModules()
    {
        $available_modules = ModUtil::apiFunc('Extensions', 'Admin', 'listmodules');

        return $this->view
            ->assign('available_modules', $available_modules)
            ->fetch('user/viewModules.tpl');
    }

    /**
     * Scanning of a module for new untranslated Strings
     */
    public function searchTranslations()
    {
        $data = ['mod_id' => null];
        $this->getGet($data);
        $this->validator->checkNotNull($data, ['mod_id']);

        $modinformations = ModUtil::getInfo($data['mod_id']);
        $files_to_search_in = ModUtil::apiFunc($this->name, 'User', 'filesInModule', $data);
        ModUtil::apiFunc($this->name, 'Translation', 'prepSearch', $data);

        $this->assign2View($data);
        return $this->view
            ->assign('modinformations', $modinformations)
            ->assign('files_to_search_in', json_encode($files_to_search_in))
            ->fetch('user/searchTranslations.tpl');
    }

    /**
     * Edit of the module's Translations
     *
     * @return string
     */
    public function editTranslations()
    {
        $data = [
            'mod_id'               => null,
            'translation_language' => ZLanguage::getLanguageCode(),
        ];
        $this->getGet($data);
        $this->validator->checkNotNull($data, ['mod_id', 'translation_language']);

        $modinformations   = ModUtil::getInfo($data['mod_id']);
        $translations      = ModUtil::apiFunc($this->name, 'Translation', 'all', $data);
        $translation_count = ModUtil::apiFunc($this->name, 'Translation', 'count', $data);
        $languages         = ModUtil::apiFunc($this->name, 'Translation', 'avaiableLanguages', $data);

        $this->assign2View($data);
        return $this->view
            ->assign('modinformations', $modinformations)
            ->assign('translations', $translations)
            ->assign('translation_count', $translation_count)
            ->assign('languages', $languages)
            ->fetch('user/editTranslations.tpl');
    }

    /**
     * Saves the module's Translations
     * Redirects back to User Controller editTranslations
     */
    public function saveTranslations()
    {
        $data = [
            'mod_id'               => null,
            'translation_language' => ZLanguage::getLanguageCode(),
            'translations'         => [],
        ];
        $this->getPost($data);
        $this->validator->checkNotNull($data, ['mod_id']);

        foreach ($data['translations'] as $key => $val) {
            ModUtil::apiFunc($this->name, 'Translation', 'update', [
                'mod_id'               => $data['mod_id'],
                'translation_language' => $data['translation_language'],
                'trans_id'             => $key,
                'trans_val'            => $val,
            ]);
        }

        $this->redirect(ModUtil::url($this->name, 'User', 'editTranslations', ['mod_id' => $data['mod_id']]));
    }

    /**
     * Exports the Translations into Po-Files and compiled Mo-Files
     * Redirects back to User Controller editTranslations
     */
    public function exportTranslation()
    {
        $data = [
            'mod_id'               => null,
            'translation_language' => null,
        ];
        $this->getGet($data);
        $this->validator->checkNotNull($data, ['mod_id']);

        ModUtil::apiFunc($this->name, 'Export', 'toPo', $data);

        $this->redirect(ModUtil::url($this->name, 'User', 'editTranslations', $data));
    }

    /**
     * Exports the Translation Template
     * Redirects back to User Controller editTranslations
     */
    public function exportTranslationTemplate()
    {
        $data = ['mod_id' => null];
        $this->getGet($data);
        $this->validator->checkNotNull($data, ['mod_id']);

        ModUtil::apiFunc($this->name, 'Export', 'toPot', $data);

        $this->redirect(ModUtil::url($this->name, 'User', 'editTranslations', $data));
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

        if (!SecurityUtil::checkPermission('Translator::', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }
    }
}
