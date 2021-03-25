<?php declare(strict_types=1);

namespace JTL\Backend\Settings\Sections;

use JTL\DB\DbInterface;
use JTL\Smarty\JTLSmarty;

/**
 * Class Base
 * @package Backend\Settings
 */
class Base implements Section
{
    /**
     * @var bool
     */
    public $hasSectionMarkup = false;

    /**
     * @var bool
     */
    public $hasValueMarkup = false;

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var JTLSmarty
     */
    protected $smarty;

    /**
     * SettingSection constructor.
     * @param DbInterface $db
     * @param JTLSmarty   $smarty
     */
    public function __construct(DbInterface $db, JTLSmarty $smarty)
    {
        $this->db     = $db;
        $this->smarty = $smarty;
    }

    /**
     * @inheritdoc
     */
    public function validate($conf, &$confValue): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function setValue(&$conf, $value): void
    {
    }

    /**
     * @inheritdoc
     */
    public function getSectionMarkup(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getValueMarkup($conf): string
    {
        return '';
    }
}
