<?php

namespace JTL;

/**
 * Class Emailvorlage
 * @package JTL
 * @deprecated since 5.0.0
 */
class Emailvorlage
{
    /**
     * @var int
     */
    protected $kEmailvorlage;

    /**
     * @var string
     */
    protected $cName;

    /**
     * @var string
     */
    protected $cBeschreibung;

    /**
     * @var string
     */
    protected $cMailTyp;

    /**
     * @var string
     */
    protected $cModulId;

    /**
     * @var string
     */
    protected $cDateiname;

    /**
     * @var string
     */
    protected $cAktiv;

    /**
     * @var int
     */
    protected $nAKZ;

    /**
     * @var int
     */
    protected $nAGB;

    /**
     * @var int
     */
    protected $nWRB;

    /**
     * @var int
     */
    protected $nWRBForm;

    /**
     * @var int
     */
    protected $nDSE;

    /**
     * @var int
     */
    protected $nFehlerhaft;

    /**
     * @var array
     */
    protected $oEinstellung_arr;

    /**
     * @var array
     */
    protected $oEinstellungAssoc_arr;

    /**
     * Constructor
     *
     * @param int  $id
     * @param bool $plugin
     */
    public function __construct(int $id = 0, bool $plugin = false)
    {
        \trigger_error(__CLASS__. ' is deprecated.', \E_USER_DEPRECATED);
        if ($id > 0) {
            $this->loadFromDB($id, $plugin);
        }
    }

    /**
     * Loads database member into class member
     *
     * @param int  $id
     * @param bool $plugin
     * @return $this
     */
    private function loadFromDB(int $id, bool $plugin): self
    {
        $table = $plugin ? 'tpluginemailvorlageeinstellungen' : 'temailvorlageeinstellungen';
        $data  = Shop::Container()->getDB()->select('temailvorlage', 'kEmailvorlage', $id);

        if (isset($data->kEmailvorlage) && $data->kEmailvorlage > 0) {
            foreach (\array_keys(\get_object_vars($data)) as $member) {
                $this->$member = $data->$member;
            }
            // Settings
            $this->oEinstellung_arr = Shop::Container()->getDB()->selectAll(
                $table,
                'kEmailvorlage',
                $this->kEmailvorlage
            );
            // Assoc bauen
            if (\is_array($this->oEinstellung_arr) && \count($this->oEinstellung_arr) > 0) {
                $this->oEinstellungAssoc_arr = [];
                foreach ($this->oEinstellung_arr as $conf) {
                    $this->oEinstellungAssoc_arr[$conf->cKey] = $conf->cValue;
                }
            }
        }

        return $this;
    }

    /**
     * @param int
     * @return $this
     */
    public function setEmailvorlage(int $kEmailvorlage): self
    {
        $this->kEmailvorlage = $kEmailvorlage;

        return $this;
    }

    /**
     * @param string
     * @return $this
     */
    public function setName($name): self
    {
        $this->cName = $name;

        return $this;
    }

    /**
     * @param string
     * @return $this
     */
    public function setBeschreibung($cBeschreibung): self
    {
        $this->cBeschreibung = $cBeschreibung;

        return $this;
    }

    /**
     * @param string
     * @return $this
     */
    public function setMailTyp($cMailTyp): self
    {
        $this->cMailTyp = $cMailTyp;

        return $this;
    }

    /**
     * @param string
     * @return $this
     */
    public function setModulId($cModulId): self
    {
        $this->cModulId = $cModulId;

        return $this;
    }

    /**
     * @param string
     * @return $this
     */
    public function setDateiname($cDateiname): self
    {
        $this->cDateiname = $cDateiname;

        return $this;
    }

    /**
     * @param string
     * @return $this
     */
    public function setAktiv($cAktiv): self
    {
        $this->cAktiv = $cAktiv;

        return $this;
    }

    /**
     * @param int
     * @return $this
     */
    public function setAKZ(int $nAKZ): self
    {
        $this->nAKZ = $nAKZ;

        return $this;
    }

    /**
     * @param int
     * @return $this
     */
    public function setAGB(int $nAGB): self
    {
        $this->nAGB = $nAGB;

        return $this;
    }

    /**
     * @param int
     * @return $this
     */
    public function setWRB(int $nWRB): self
    {
        $this->nWRB = $nWRB;

        return $this;
    }

    /**
     * @param int
     * @return $this
     */
    public function setDSE(int $nDSE): self
    {
        $this->nDSE = $nDSE;

        return $this;
    }

    /**
     * @param int
     * @return $this
     */
    public function setWRBForm(int $nWRBForm): self
    {
        $this->nWRBForm = $nWRBForm;

        return $this;
    }

    /**
     * @param int
     * @return $this
     */
    public function setFehlerhaft(int $nFehlerhaft): self
    {
        $this->nFehlerhaft = $nFehlerhaft;

        return $this;
    }

    /**
     * @return int
     */
    public function getEmailvorlage(): int
    {
        return (int)$this->kEmailvorlage;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->cName;
    }

    /**
     * @return string|null
     */
    public function getBeschreibung(): ?string
    {
        return $this->cBeschreibung;
    }

    /**
     * @return string|null
     */
    public function getMailTyp(): ?string
    {
        return $this->cMailTyp;
    }

    /**
     * @return string|null
     */
    public function getModulId(): ?string
    {
        return $this->cModulId;
    }

    /**
     * @return string|null
     */
    public function getDateiname(): ?string
    {
        return $this->cDateiname;
    }

    /**
     * @return string|null
     */
    public function getAktiv(): ?string
    {
        return $this->cAktiv;
    }

    /**
     * @return int|null
     */
    public function getAKZ(): ?int
    {
        return $this->nAKZ;
    }

    /**
     * @return int|null
     */
    public function getAGB(): ?int
    {
        return $this->nAGB;
    }

    /**
     * @return int|null
     */
    public function getWRB(): ?int
    {
        return $this->nWRB;
    }

    /**
     * @return int|null
     */
    public function getWRBForm(): ?int
    {
        return $this->nWRBForm;
    }

    /**
     * @return int|null
     */
    public function getDSE(): ?int
    {
        return $this->nDSE;
    }

    /**
     * @return int|null
     */
    public function getFehlerhaft(): ?int
    {
        return $this->nFehlerhaft;
    }

    /**
     * @param string $modulId
     * @param bool   $isPlugin
     * @return Emailvorlage|null
     */
    public static function load(string $modulId, $isPlugin = false): ?self
    {
        \trigger_error(__CLASS__. ' is deprecated.', \E_USER_DEPRECATED);
        $obj = Shop::Container()->getDB()->select(
            'temailvorlage',
            'cModulId',
            $modulId,
            null,
            null,
            null,
            null,
            false,
            'kEmailvorlage'
        );

        return ($obj !== null && isset($obj->kEmailvorlage) && (int)$obj->kEmailvorlage > 0)
            ? new self((int)$obj->kEmailvorlage, $isPlugin)
            : null;
    }
}
