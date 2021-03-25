<?php declare(strict_types=1);

namespace JTL\Media\Image;

use JTL\DB\ReturnType;
use JTL\Language\LanguageHelper;
use JTL\MagicCompatibilityTrait;
use JTL\Shop;
use stdClass;

/**
 * Class Overlay
 * @package JTL\Media\Image
 */
class Overlay
{
    use MagicCompatibilityTrait;

    /**
     * @var array
     */
    protected static $mapping = [
        'cURLKlein'           => 'URLKlein',
        'cURLNormal'          => 'URLNormal',
        'cURLGross'           => 'URLGross',
        'cURLRetina'          => 'URLRetina',
        'nPosition'           => 'Position',
        'nGroesse'            => 'Size',
        'nTransparenz'        => 'Transparance',
        'nMargin'             => 'Margin',
        'nAktiv'              => 'Active',
        'nPrio'               => 'Priority',
        'kSprache'            => 'Language',
        'kSuchspecialOverlay' => 'Type',
        'cTemplate'           => 'TemplateName',
        'cSuchspecial'        => 'Name',
        'cPfadKlein'          => 'URLKlein',
        'cPfadNormal'         => 'URLNormal',
        'cPfadGross'          => 'URLGross',
        'cPfadRetina'         => 'URLRetina',
        'cBildPfad'           => 'ImageName'
    ];

    /**
     * @var Overlay
     */
    private static $instance;

    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $pathSizes;

    /**
     * @var int
     */
    private $position;

    /**
     * @var int
     */
    private $active;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var int
     */
    private $margin;

    /**
     * @var int
     */
    private $transparence;

    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $type;

    /**
     * @var int
     */
    private $language;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $imageName;

    /**
     * @var string
     */
    private $templateName;

    /**
     * @var array
     */
    private $urlSizes;

    public const IMAGENAME_TEMPLATE = 'overlay';

    public const IMAGE_DEFAULT = [
        'name'      => 'std_kSuchspecialOverlay',
        'extension' => '.png'
    ];

    public const DEFAULT_TEMPLATE = 'default';

    public const ORIGINAL_FOLDER_NAME = 'original';

    /**
     * Overlay constructor.
     * @param int         $type
     * @param int         $language
     * @param string|null $template
     */
    public function __construct(int $type, int $language, string $template = null)
    {
        $this->setType($type)
            ->setLanguage($language)
            ->setTemplateName($template)
            ->setPath(\PFAD_TEMPLATES . $this->getTemplateName() . \PFAD_OVERLAY_TEMPLATE)
            ->setPathSizes();
    }

    /**
     * @param int         $type
     * @param int         $language
     * @param string|null $template
     * @param bool|null   $setFallbackPath
     * @return Overlay
     */
    public static function getInstance(
        int $type,
        int $language,
        string $template = null,
        bool $setFallbackPath = true
    ): self {
        return self::$instance ?? (new self($type, $language, $template))->loadFromDB($setFallbackPath);
    }

    /**
     * @param bool $setFallbackPath
     * @return Overlay
     */
    public function loadFromDB(bool $setFallbackPath): self
    {
        $overlay = $this->getDataForLanguage($this->getLanguage());
        // get overlay data for fallback language
        $overlay = $overlay ?? $this->getDataForLanguage(LanguageHelper::getDefaultLanguage()->kSprache);
        if (!empty($overlay)) {
            $this->setActive((int)$overlay->nAktiv)
                ->setMargin((int)$overlay->nMargin)
                ->setPosition((int)$overlay->nPosition)
                ->setPriority((int)$overlay->nPrio)
                ->setTransparence((int)$overlay->nTransparenz)
                ->setSize((int)$overlay->nGroesse)
                ->setImageName($overlay->cBildPfad)
                ->setName(isset($_SESSION['AdminAccount']) ? __($overlay->cSuchspecial) : $overlay->cSuchspecial);

            if ($setFallbackPath) {
                $this->setFallbackPath($overlay->cTemplate);
            }
            $this->setURLSizes();
        } else {
            // fallback overlay is missing
            $this->setActive(0)
                ->setMargin(0)
                ->setPosition(0)
                ->setPriority(0)
                ->setTransparence(0)
                ->setSize(0)
                ->setImageName('')
                ->setName('');
        }

        return $this;
    }

    /**
     * @param int $language
     * @return stdClass|null
     */
    private function getDataForLanguage(int $language): ?stdClass
    {
        $overlay = Shop::Container()->getDB()->queryPrepared(
            'SELECT ssos.*, sso.cSuchspecial
                 FROM tsuchspecialoverlaysprache ssos
                 LEFT JOIN tsuchspecialoverlay sso
                     ON ssos.kSuchspecialOverlay = sso.kSuchspecialOverlay
                 WHERE ssos.kSprache = :languageID
                     AND ssos.kSuchspecialOverlay = :overlayID
                     AND ssos.cTemplate IN (:templateName, :defaultTemplate)
                 ORDER BY FIELD(ssos.cTemplate, :templateName, :defaultTemplate)
                 LIMIT 1',
            [
                'languageID'      => $language,
                'overlayID'       => $this->getType(),
                'templateName'    => $this->getTemplateName(),
                'defaultTemplate' => self::DEFAULT_TEMPLATE
            ],
            ReturnType::SINGLE_OBJECT
        );

        return $overlay === false ? null : $overlay;
    }

    /**
     * @param string $templateName
     */
    private function setFallbackPath(string $templateName): void
    {
        $fallbackPath      = false;
        $fallbackImageName = '';
        if ($templateName === self::DEFAULT_TEMPLATE
            || !\file_exists(\PFAD_ROOT . $this->getPathSize(\IMAGE_SIZE_SM) . $this->getImageName())
        ) {
            $defaultImgName = self::IMAGE_DEFAULT['name'] . '_' . $this->getLanguage() . '_'
                . $this->getType() . self::IMAGE_DEFAULT['extension'];
            $imgName        = self::IMAGE_DEFAULT['name'] . '_' .
                LanguageHelper::getDefaultLanguage()->kSprache . '_' .
                $this->getType() . self::IMAGE_DEFAULT['extension'];

            if (\file_exists(\PFAD_ROOT . \PFAD_SUCHSPECIALOVERLAY_NORMAL . $defaultImgName)) {
                // default fallback path
                $fallbackImageName = $defaultImgName;
                $fallbackPath      = true;
            } else {
                $overlayDefaultLanguage = $this->getDataForLanguage(LanguageHelper::getDefaultLanguage()->kSprache);
                if (!empty($overlayDefaultLanguage)) {
                    if ($overlayDefaultLanguage->cTemplate !== self::DEFAULT_TEMPLATE
                        && \file_exists(
                            \PFAD_ROOT . $this->getPathSize(\IMAGE_SIZE_SM) . $overlayDefaultLanguage->cBildPfad
                        )
                    ) {
                        // fallback path for default language
                        $fallbackImageName = $overlayDefaultLanguage->cBildPfad;
                    } elseif (\file_exists(\PFAD_ROOT . \PFAD_SUCHSPECIALOVERLAY_NORMAL . $imgName)) {
                        //default fallback path for default language
                        $fallbackImageName = $imgName;
                        $fallbackPath      = true;
                    }
                }
            }
        }

        if ($fallbackPath) {
            $this->setPath(\PFAD_SUCHSPECIALOVERLAY)
                ->setPathSizes(true);
        }
        if ($fallbackImageName !== '') {
            $this->setImageName($fallbackImageName);
        }
    }

    /**
     * save overlay to db
     */
    public function save(): void
    {
        $db          = Shop::Container()->getDB();
        $overlayData = (object)[
            'nAktiv'       => $this->getActive(),
            'nPrio'        => $this->getPriority(),
            'nTransparenz' => $this->getTransparance(),
            'nGroesse'     => $this->getSize(),
            'nPosition'    => $this->getPosition(),
            'cBildPfad'    => $this->getImageName(),
            'nMargin'      => 5
        ];

        $check = $db->queryPrepared(
            'SELECT * FROM tsuchspecialoverlaysprache
              WHERE kSprache = :languageID
                AND kSuchspecialOverlay = :overlayID
                AND cTemplate = :templateName',
            [
                'languageID'   => $this->getLanguage(),
                'overlayID'    => $this->getType(),
                'templateName' => $this->getTemplateName()
            ],
            ReturnType::SINGLE_OBJECT
        );
        if ($check) {
            $db->update(
                'tsuchspecialoverlaysprache',
                ['kSuchspecialOverlay', 'kSprache', 'cTemplate'],
                [$this->getType(), $this->getLanguage(), $this->getTemplateName()],
                $overlayData
            );
        } else {
            $overlayData->kSuchspecialOverlay = $this->getType();
            $overlayData->kSprache            = $this->getLanguage();
            $overlayData->cTemplate           = $this->getTemplateName();
            $db->insert('tsuchspecialoverlaysprache', $overlayData);
        }
    }

    /**
     * @param string $imageName
     * @return Overlay
     */
    public function setImageName(string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * @return string
     */
    public function getImageName(): string
    {
        return $this->imageName;
    }

    /**
     * @param string $name
     * @return Overlay
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string|null $template
     * @return Overlay
     */
    public function setTemplateName(string $template = null): self
    {
        $this->templateName = $template
            ?: Shop::Container()->getTemplateService()->getActiveTemplate()->getName();

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    /**
     * @return array
     */
    public function getPathSizes(): array
    {
        return $this->pathSizes;
    }

    /**
     * @param string $size
     * @return null|string
     */
    public function getPathSize(string $size): ?string
    {
        return $this->pathSizes[$size] ?? null;
    }

    /**
     * @param bool $default
     * @return Overlay
     */
    public function setPathSizes(bool $default = false): self
    {
        $this->pathSizes = [
            \IMAGE_SIZE_XS => $default ? \PFAD_SUCHSPECIALOVERLAY_KLEIN : $this->getPath() . \IMAGE_SIZE_XS . '/',
            \IMAGE_SIZE_SM => $default ? \PFAD_SUCHSPECIALOVERLAY_NORMAL : $this->getPath() . \IMAGE_SIZE_SM . '/',
            \IMAGE_SIZE_MD => $default ? \PFAD_SUCHSPECIALOVERLAY_GROSS : $this->getPath() . \IMAGE_SIZE_MD . '/',
            \IMAGE_SIZE_LG => $default ? \PFAD_SUCHSPECIALOVERLAY_RETINA : $this->getPath() . \IMAGE_SIZE_LG . '/'
        ];

        return $this;
    }

    /**
     * @param string $size
     * @return null|string
     */
    public function getURL(string $size): ?string
    {
        return $this->urlSizes[$size] ?? null;
    }

    /**
     * @return Overlay
     */
    public function setURLSizes(): self
    {
        $shopURL        = Shop::getURL() . '/';
        $this->urlSizes = [
            \IMAGE_SIZE_XS => $shopURL . $this->getPathSize(\IMAGE_SIZE_XS) . $this->getImageName(),
            \IMAGE_SIZE_SM => $shopURL . $this->getPathSize(\IMAGE_SIZE_SM) . $this->getImageName(),
            \IMAGE_SIZE_MD => $shopURL . $this->getPathSize(\IMAGE_SIZE_MD) . $this->getImageName(),
            \IMAGE_SIZE_LG => $shopURL . $this->getPathSize(\IMAGE_SIZE_LG) . $this->getImageName()
        ];

        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return Overlay
     */
    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @param int $type
     * @return Overlay
     */
    private function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param int $language
     * @return Overlay
     */
    private function setLanguage(int $language): self
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @param int $position
     * @return Overlay
     */
    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @param int $active
     * @return Overlay
     */
    public function setActive(int $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @param int $priority
     * @return Overlay
     */
    public function setPriority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @param int $margin
     * @return Overlay
     */
    public function setMargin(int $margin): self
    {
        $this->margin = $margin;

        return $this;
    }

    /**
     * @param int $transparance
     * @return Overlay
     */
    public function setTransparence(int $transparance): self
    {
        $this->transparence = $transparance;

        return $this;
    }

    /**
     * @param int $size
     * @return Overlay
     */
    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getLanguage(): int
    {
        return $this->language;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getTransparance(): int
    {
        return $this->transparence;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @return int
     */
    public function getActive(): int
    {
        return $this->active;
    }

    /**
     * @return int
     */
    public function getMargin(): int
    {
        return $this->margin;
    }

    /**
     * @return string
     * @deprecated since 5.0.0
     */
    public function getURLKlein(): string
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return $this->getURL(\IMAGE_SIZE_XS);
    }

    /**
     * @return string
     * @deprecated since 5.0.0
     */
    public function getURLNormal(): string
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return $this->getURL(\IMAGE_SIZE_SM);
    }

    /**
     * @return string
     * @deprecated since 5.0.0
     */
    public function getURLGross(): string
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return $this->getURL(\IMAGE_SIZE_MD);
    }

    /**
     * @return string
     * @deprecated since 5.0.0
     */
    public function getURLRetina(): string
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return $this->getURL(\IMAGE_SIZE_LG);
    }

    /**
     * @param string $path
     */
    public function setPathKlein(string $path): void
    {
        \trigger_error(__CLASS__ . ': setting pathklein here is not possible anymore.', \E_USER_DEPRECATED);
    }

    /**
     * @param string $path
     */
    public function setPathNormal(string $path): void
    {
        \trigger_error(__CLASS__ . ': setting pathnormal here is not possible anymore.', \E_USER_DEPRECATED);
    }

    /**
     * @param string $path
     */
    public function setPathGross(string $path): void
    {
        \trigger_error(__CLASS__ . ': setting pathgross here is not possible anymore.', \E_USER_DEPRECATED);
    }

    /**
     * @param string $path
     */
    public function setPathRetina(string $path): void
    {
        \trigger_error(__CLASS__ . ': setting pathretina here is not possible anymore.', \E_USER_DEPRECATED);
    }

    /**
     * @param string $url
     */
    public function setURLKlein(string $url): void
    {
        \trigger_error(__CLASS__ . ': setting urlklein here is not possible anymore.', \E_USER_DEPRECATED);
    }

    /**
     * @param string $url
     */
    public function setURLNormal(string $url): void
    {
        \trigger_error(__CLASS__ . ': setting urlnormal here is not possible anymore.', \E_USER_DEPRECATED);
    }

    /**
     * @param string $url
     */
    public function setURLGross(string $url): void
    {
        \trigger_error(__CLASS__ . ': setting urlgross here is not possible anymore.', \E_USER_DEPRECATED);
    }

    /**
     * @param string $url
     */
    public function setURLRetina(string $url): void
    {
        \trigger_error(__CLASS__ . ': setting urlretina here is not possible anymore.', \E_USER_DEPRECATED);
    }
}
