# Translator

A *[Zikula Application Framework](http://www.zikula.org)* Module Providing a Web-based way to translate Zikula-modules.


## Feature Overview

- Searching for new Strings to translate
- Importing existing translation strings out of the .pot file of your Project
- Importing existing translations out of the .po file of your Project
- Translate multiple languages in on step
- Export Translationstrings into a .pot file
- Export (.po) and Compile (.mo) Translations

## Requirements

- An active installation of the _Zikula Application Framework_ with version **&ge; 1.3.5** running on PHP5
- A _Zikula_ Module to translate.


## Installation

### via Github
1. Go into the module path of your _Zikula_ installation
2. Run following command to get the module

		git clone https://github.com/ChDeinert/Translator.git Translator
3. Initialize the **LDAPAuth** module in the _Zikula_ Adminstration area

### via Download
1. Download the module [here](https://github.com/ChDeinert/Translator/archive/v1.0.3.zip) and extract the contents into a folder called **Translator**
2. Copy or move the folder into the module path of your _Zikula_ installation directory
3. Initialize the **Translator** module in the _Zikula_ Administration area


## Usage

Any function of the **Translator** module can be found in the _Zikula_ Administration area.

### 1. Setting languages to translate

To choose the language you want to translate into, you have to go into the *translation language* configuration. 
Here you check the language you want and save this settings.

### 2. Setting the modules to translate

To choose the module you want to create the translations for, you have to go into the *translation module* configuration.
Here you check the module you want and save the settings.

### 3. Add strings to translate

In **Translator** there are different ways to add the strings you want to translate.

1. You can import existing translations from a *pot-file*. This will add only the strings to translate.
2. You can import existing translations from a *po-file*. This will add the strings to translate and the existing translation for the language the file is belonging to.
3. You can search the configured modules for new strings to translate. This will add the strings to translate.

### 4. Create translations

You can create or change translations under *'edit available translations'*. Here you can add and/or change existing translations by writing them into the textfields.

Don't forget to save the translations, before you change view settings or go to another page.

### 5. Export translations

When you created your translations, you have different options to export.

1. You can export your strings to translate into a *pot-file*. It will be created in the modules 'locale' path and you can use it as catalogue in gettext editors like [Poedit](http://www.poedit.net).
2. You can export your translations into a *po-file* that contains the conplete translations. This will be automatically compiled for gettext usage. 

After you exported the *po-file* and got succesfully compiled _Zikula_ will immediately recognize it and use the translations.

## License

Translator is open-source Software licensed under the [GNU General Public License (GPL) 3.0](http://www.gnu.org/licenses/gpl-3.0)
