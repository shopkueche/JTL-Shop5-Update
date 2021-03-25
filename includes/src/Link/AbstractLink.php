<?php

namespace JTL\Link;

use JTL\Language\LanguageHelper;
use JTL\MagicCompatibilityTrait;

/**
 * Class AbstractLink
 * @package JTL\Link
 */
abstract class AbstractLink implements LinkInterface
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    protected static $mapping = [
        'cNoFollow'          => 'NoFollowCompat',
        'cURL'               => 'URL',
        'cURLFull'           => 'URL',
        'cURLFullSSL'        => 'URL',
        'cLocalizedName'     => 'NamesCompat',
        'cLocalizedTitle'    => 'Title',
        'kLink'              => 'ID',
        'kSprache'           => 'LanguageID',
        'cName'              => 'Name',
        'kPlugin'            => 'PluginID',
        'kVaterLink'         => 'Parent',
        'kLinkgruppe'        => 'LinkGroupID',
        'cKundengruppen'     => 'CustomerGroupsCompat',
        'cSichtbarNachLogin' => 'VisibleLoggedInOnlyCompat',
        'nSort'              => 'Sort',
        'bSSL'               => 'SSL',
        'bIsFluid'           => 'IsFluid',
        'cIdentifier'        => 'Identifier',
        'bIsActive'          => 'IsActive',
        'aktiv'              => 'IsActive',
        'cISO'               => 'LanguageCode',
        'cLocalizedSeo'      => 'URL',
        'cSeo'               => 'URL',
        'nHTTPRedirectCode'  => 'RedirectCode',
        'nPluginStatus'      => 'PluginEnabled',
        'Sprache'            => 'LangCompat',
        'cContent'           => 'Content',
        'cTitle'             => 'Title',
        'cMetaTitle'         => 'MetaTitle',
        'cMetaKeywords'      => 'MetaKeyword',
        'cMetaDescription'   => 'MetaDescription',
        'cDruckButton'       => 'PrintButtonCompat',
        'nLinkart'           => 'LinkType',
        'level'              => 'Level',
    ];

    /**
     * @param string|array $ssk
     * @return array
     */
    protected static function parseSSKAdvanced($ssk): array
    {
        return \is_string($ssk) && \mb_convert_case($ssk, \MB_CASE_LOWER) !== 'null'
            ? \array_map('\intval', \array_map('\trim', \array_filter(\explode(';', $ssk))))
            : [];
    }

    /**
     * @return $this
     */
    public function getLangCompat(): LinkInterface
    {
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCustomerGroupsCompat(): ?string
    {
        $groups = $this->getCustomerGroups();

        return \is_array($groups) && \count($groups) > 0
            ? \implode(';', $groups) . ';'
            : null;
    }

    /**
     * @param string|array $value
     */
    public function setCustomerGroupsCompat($value): void
    {
        $this->setCustomerGroups(!\is_array($value) ? self::parseSSKAdvanced($value) : $value);
    }

    /**
     * @return string
     */
    public function getPrintButtonCompat(): string
    {
        return $this->hasPrintButton() === true ? 'Y' : 'N';
    }

    /**
     * @param string|bool $value
     */
    public function setPrintButtonCompat($value): void
    {
        $this->setPrintButton($value === 'Y' || $value === true);
    }

    /**
     * @return string
     */
    public function getNoFollowCompat(): string
    {
        return $this->getNoFollow() === true ? 'Y' : 'N';
    }

    /**
     * @param string|bool $value
     */
    public function setNoFollowCompat($value): void
    {
        $this->setNoFollow($value === 'Y' || $value === true);
    }

    /**
     * @return string
     */
    public function getVisibleLoggedInOnlyCompat(): string
    {
        return $this->getVisibleLoggedInOnly() === true ? 'Y' : 'N';
    }

    /**
     * @param string|bool $value
     */
    public function setVisibleLoggedInOnlyCompat($value): void
    {
        $this->setVisibleLoggedInOnly($value === 'Y' || $value === true);
    }

    /**
     * @return array
     */
    public function getNamesCompat(): array
    {
        $byCode    = [];
        $languages = LanguageHelper::getAllLanguages(1);
        foreach ($this->getNames() as $langID => $name) {
            $byCode[$languages[$langID]->cISO] = $name;
        }

        return $byCode;
    }
}
