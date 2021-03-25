<?php declare(strict_types=1);

namespace JTL\L10n;

use Gettext\Translations;
use Gettext\Translator;
use JTL\Plugin\Admin\ListingItem as PluginListingItem;
use JTL\Plugin\PluginInterface;
use JTL\Template\Admin\ListingItem as TemplateListingItem;
use JTL\Template\Model;

/**
 * Class GetText
 * @package JTL\L10n
 */
class GetText
{
    /**
     * @var string
     */
    private $langTag;

    /**
     * @var Translations[]
     */
    private $translations;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * GetText constructor.
     */
    public function __construct()
    {
        $this->langTag      = $this->getDefaultLanguage();
        $this->translations = [];
        $this->translator   = new Translator();
        $this->translator->register();
        $this->setLanguage()->loadAdminLocale('base');
    }

    /**
     * @return string
     */
    public function getDefaultLanguage(): string
    {
        return 'de-DE';
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->langTag;
    }

    /**
     * @return string
     */
    public function getAdminDir(): string
    {
        return \PFAD_ROOT . \PFAD_ADMIN;
    }

    /**
     * @param Model $template
     * @return string
     */
    public function getTemplateDir(Model $template): string
    {
        return \PFAD_ROOT . \PFAD_TEMPLATES . $template->getDir() . '/';
    }

    /**
     * @param PluginInterface $plugin
     * @return string
     */
    public function getPluginDir(PluginInterface $plugin): string
    {
        return $plugin->getPaths()->getBasePath();
    }

    /**
     * @param string $dir
     * @param string $domain
     * @return string
     */
    public function getMoPath(string $dir, string $domain): string
    {
        return $dir . 'locale/' . $this->langTag . '/' . $domain . '.mo';
    }

    /**
     * @param string $domain
     * @return string
     */
    public function getAdminMoPath(string $domain): string
    {
        return $this->getMoPath($this->getAdminDir(), $domain);
    }

    /**
     * @param string $domain
     * @param Model  $template
     * @return string
     */
    public function getTemplateMoPath(string $domain, Model $template): string
    {
        return $this->getMoPath($this->getTemplateDir($template), $domain);
    }

    /**
     * @param string          $domain
     * @param PluginInterface $plugin
     * @return string
     */
    public function getPluginMoPath(string $domain, PluginInterface $plugin): string
    {
        return $this->getMoPath($this->getPluginDir($plugin), $domain);
    }

    /**
     * @param string $path
     * @return GetText
     */
    public function loadLocaleFile(string $path): self
    {
        if (!\array_key_exists($path, $this->translations)) {
            $this->translations[$path] = null;

            if (\file_exists($path)) {
                $this->translations[$path] = Translations::fromMoFile($path);
                $this->translator->loadTranslations($this->translations[$path]);
            }
        }

        return $this;
    }

    /**
     * @param string $dir
     * @param string $domain
     * @return GetText
     */
    public function loadTranslations(string $dir, string $domain): self
    {
        return $this->loadLocaleFile($this->getMoPath($dir, $domain));
    }

    /**
     * @param string $domain
     * @return GetText
     */
    public function loadAdminLocale(string $domain): self
    {
        return $this->loadLocaleFile($this->getAdminMoPath($domain));
    }

    /**
     * @param string $domain
     * @param PluginInterface $plugin
     * @return GetText
     */
    public function loadPluginLocale(string $domain, PluginInterface $plugin): self
    {
        return $this->loadLocaleFile($this->getPluginMoPath($domain, $plugin));
    }

    /**
     * @param string $domain
     * @param Model  $template
     * @return GetText
     */
    public function loadTemplateLocale(string $domain, Model $template): self
    {
        return $this->loadLocaleFile($this->getTemplateMoPath($domain, $template));
    }

    /**
     * @param string            $domain
     * @param PluginListingItem $item
     * @return GetText
     */
    public function loadPluginItemLocale(string $domain, PluginListingItem $item): self
    {
        return $this->loadTranslations(\PFAD_ROOT . \PLUGIN_DIR . $item->getDir() . '/', $domain);
    }

    /**
     * @param string              $domain
     * @param TemplateListingItem $item
     * @return GetText
     */
    public function loadTemplateItemLocale(string $domain, TemplateListingItem $item): self
    {
        return $this->loadTranslations(\PFAD_ROOT . \PFAD_TEMPLATES . $item->getDir() . '/', $domain);
    }

    /**
     * @param string $dir
     * @param string $domain
     * @return Translations
     */
    public function getTranslations(string $dir, string $domain): Translations
    {
        $path = $this->getMoPath($dir, $domain);
        $this->loadLocaleFile($path);

        return $this->translations[$path];
    }

    /**
     * @param string $domain
     * @return Translations
     */
    public function getAdminTranslations(string $domain): Translations
    {
        $path = $this->getAdminMoPath($domain);
        $this->loadLocaleFile($path);

        return $this->translations[$path];
    }

    /**
     * @param string|null $langTag
     * @return GetText
     */
    public function setLanguage(?string $langTag = null): self
    {
        $langTag = $langTag
            ?? $_SESSION['AdminAccount']->language
            ?? $this->langTag;

        if ($this->langTag !== $langTag) {
            $oldLangTag         = $this->langTag;
            $oldTranslations    = $this->translations;
            $this->langTag      = $langTag;
            $this->translations = [];
            $this->translator   = new Translator();
            $this->translator->register();

            if (!empty($oldLangTag)) {
                foreach ($oldTranslations as $path => $trans) {
                    $newPath = \str_replace('/' . $oldLangTag . '/', '/' . $langTag . '/', $path);
                    $this->loadLocaleFile($newPath);
                }
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getAdminLanguages(): array
    {
        $languages  = [];
        $localeDirs = \scandir(\PFAD_ROOT . \PFAD_ADMIN . 'locale/', \SCANDIR_SORT_ASCENDING);

        foreach ($localeDirs as $dir) {
            if ($dir !== '.' && $dir !== '..') {
                $languages[$dir] = \Locale::getDisplayLanguage($dir, $dir);
            }
        }

        return $languages;
    }

    /**
     * @param bool $withGroups
     * @param bool $withSections
     */
    public function loadConfigLocales(bool $withGroups = false, bool $withSections = false): void
    {
        $this->loadAdminLocale('configs/configs')
             ->loadAdminLocale('configs/values')
             ->loadAdminLocale('configs/groups');

        if ($withGroups) {
            $this->loadAdminLocale('configs/groups');
        }

        if ($withSections) {
            $this->loadAdminLocale('configs/sections');
        }
    }

    /**
     * @param object $config
     */
    public function localizeConfig($config): void
    {
        if ($config->cConf === 'Y') {
            $config->cName         = __($config->cWertName . '_name');
            $config->cBeschreibung = __($config->cWertName . '_desc');

            if ($config->cBeschreibung === $config->cWertName . '_desc') {
                $config->cBeschreibung = '';
            }
        } elseif ($config->cConf === 'N') {
            $config->cName = __($config->cWertName);
        }
    }

    /**
     * @param object[] $configs
     */
    public function localizeConfigs(array $configs): void
    {
        foreach ($configs as $config) {
            $this->localizeConfig($config);
        }
    }

    /**
     * @param object $config
     * @param object $value
     */
    public function localizeConfigValue($config, $value): void
    {
        $value->cName = __($config->cWertName . '_value(' . $value->cWert . ')');
    }

    /**
     * @param object $config
     * @param object[] $values
     */
    public function localizeConfigValues($config, $values): void
    {
        foreach ($values as $value) {
            $this->localizeConfigValue($config, $value);
        }
    }

    /**
     * @param object $section
     */
    public function localizeConfigSection($section): void
    {
        $section->cName = __('configsection_' . $section->kEinstellungenSektion);
    }

    /**
     * @param object[] $sections
     */
    public function localizeConfigSections($sections): void
    {
        foreach ($sections as $section) {
            $this->localizeConfigSection($section);
        }
    }
}
