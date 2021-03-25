<?php declare(strict_types=1);

namespace JTL\Backend\Settings;

use JTL\DB\DbInterface;
use JTL\Smarty\JTLSmarty;

/**
 * Class SettingSection
 * @package Backend\Settings
 */
class Manager
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
     * @var Manager[]
     */
    private $instances = [];

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
     * @param int $sectionID
     * @return static
     */
    public function getInstance(int $sectionID)
    {
        if (!isset($this->instances[$sectionID])) {
            $section = $this->db->select('teinstellungensektion', 'kEinstellungenSektion', $sectionID);
            if (isset($section->kEinstellungenSektion)) {
                $className = 'JTL\Backend\Settings\Sections\\' . \preg_replace(
                    ['([üäöÜÄÖ])', '/[^a-zA-Z_]/'],
                    ['$1e', ''],
                    $section->cName
                );
                if (\class_exists($className)) {
                    $this->instances[$sectionID] = new $className($this->db, $this->smarty);

                    return $this->instances[$sectionID];
                }
            }
            $this->instances[$sectionID] = new self($this->db, $this->smarty);
        }

        return $this->instances[$sectionID];
    }

    /**
     * @param object $conf
     * @param object $confValue
     * @return bool
     */
    public function validate($conf, &$confValue): bool
    {
        return true;
    }

    /**
     * @param object $conf
     * @param mixed  $value
     * @return static
     */
    public function setValue(&$conf, $value): self
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getSectionMarkup(): string
    {
        return '';
    }

    /**
     * @param object $conf
     * @return string
     */
    public function getValueMarkup($conf): string
    {
        return '';
    }
}
