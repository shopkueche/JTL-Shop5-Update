<?php

namespace JTL\Helpers;

use JTL\Alert\Alert;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Shop;

/**
 * Class Overlay
 * @package JTL\Helpers
 * @since 5.0.0
 */
class Overlay
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * Overlay constructor.
     * @param DbInterface $db
     */
    public function __construct(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     *  get overlays (images) from template folder (original) and create for each valid image the corresponding files
     * (sizes) and data (default settings in tsuchspecialoverlaysprache)
     * example filename: overlay_1_7.jpg | 1 -> overlay language, 7 -> overlay type
     * @param string $template
     * @return bool
     */
    public function loadOverlaysFromTemplateFolder(string $template): bool
    {
        require_once \PFAD_ROOT . \PFAD_ADMIN . \PFAD_INCLUDES . 'suchspecialoverlay_inc.php';

        $overlayPath = \PFAD_ROOT . \PFAD_TEMPLATES . $template . \PFAD_OVERLAY_TEMPLATE;
        $dir         = $overlayPath . \JTL\Media\Image\Overlay::ORIGINAL_FOLDER_NAME;
        if (!\is_dir($dir)) {
            return false;
        }
        if (!\is_writable($overlayPath)) {
            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_ERROR,
                \sprintf(__('errorOverlayWritePermissions'), \PFAD_TEMPLATES . $template . \PFAD_OVERLAY_TEMPLATE),
                'errorOverlayWritePermissions',
                ['saveInSession' => true]
            );

            return false;
        }

        foreach (\scandir($dir, \SORT_NUMERIC) as $overlay) {
            $overlayParts = \explode('_', $overlay);
            if (\count($overlayParts) === 3 && $overlayParts[0] === \JTL\Media\Image\Overlay::IMAGENAME_TEMPLATE) {
                $filePath = $dir . '/' . $overlay;
                $lang     = (int)$overlayParts[1];
                $type     = (int)\substr($overlayParts[2], 0, \strpos($overlayParts[2], '.'));
                if ($lang === 0 || $type === 0) {
                    continue;
                }
                $defaultOverlay = $this->db->queryPrepared(
                    'SELECT *
                      FROM tsuchspecialoverlaysprache
                      WHERE kSprache = :lang
                        AND kSuchspecialOverlay = :type
                        AND cTemplate IN (:templateName, :defaultName)
                      ORDER BY FIELD(cTemplate, :templateName, :defaultName)
                      LIMIT 1',
                    [
                        'lang'         => $lang,
                        'type'         => $type,
                        'templateName' => $template,
                        'defaultName'  => \JTL\Media\Image\Overlay::DEFAULT_TEMPLATE
                    ],
                    ReturnType::SINGLE_OBJECT
                );
                // use default settings for new overlays
                if (!empty($defaultOverlay) && $defaultOverlay->cTemplate !== $template) {
                    speicherEinstellung(
                        $type,
                        (array)$defaultOverlay,
                        [
                            'type'     => \mime_content_type($filePath),
                            'tmp_name' => $filePath,
                            'name'     => $overlay
                        ],
                        $lang,
                        $template
                    );
                }
            }
        }
        Shop::Container()->getCache()->flushTags([\CACHING_GROUP_ARTICLE]);

        return true;
    }
}
