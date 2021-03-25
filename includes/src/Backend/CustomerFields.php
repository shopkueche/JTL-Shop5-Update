<?php

namespace JTL\Backend;

use JTL\DB\ReturnType;
use JTL\Helpers\GeneralObject;
use JTL\Shop;
use stdClass;

/**
 * Class CustomerFields
 * @package JTL\Backend
 */
class CustomerFields
{
    /**
     * @var static[]
     */
    private static $instances;

    /**
     * @var object[]
     */
    protected $customerFields = [];

    /**
     * @var int
     */
    protected $langID;

    /**
     * CustomerFields constructor.
     *
     * @param int $langID
     */
    public function __construct(int $langID)
    {
        $this->langID = $langID;
        $this->loadFields($langID);
    }

    /**
     * @param int|null $langID
     * @return CustomerFields
     */
    public static function getInstance(int $langID = null): self
    {
        if ($langID === null || $langID === 0) {
            $langID = (int)$_SESSION['kSprache'];
        }

        if (!isset(self::$instances[$langID])) {
            self::$instances[$langID] = new static($langID);
        }

        return self::$instances[$langID];
    }

    /**
     * @param int $langID
     */
    protected function loadFields(int $langID): void
    {
        $this->customerFields = Shop::Container()->getDB()->queryPrepared(
            'SELECT * FROM tkundenfeld
                WHERE kSprache = :lid
                ORDER BY nSort ASC',
            ['lid' => $langID],
            ReturnType::COLLECTION
        )->map([$this, 'prepare'])->keyBy('kKundenfeld')->toArray();
    }

    /**
     * @param object $customerField
     * @return object
     */
    public function prepare($customerField)
    {
        $customerField->kKundenfeld = (int)$customerField->kKundenfeld;
        $customerField->kSprache    = (int)$customerField->kSprache;
        $customerField->nSort       = (int)$customerField->nSort;
        $customerField->nPflicht    = (int)$customerField->nPflicht > 0 ? 1 : 0;
        $customerField->nEditierbar = (int)$customerField->nEditierbar > 0 ? 1 : 0;

        return $customerField;
    }

    /**
     * @return object[]
     */
    public function getCustomerFields(): array
    {
        return GeneralObject::deepCopy($this->customerFields);
    }

    /**
     * @param int $kCustomerField
     * @return null|object
     */
    public function getCustomerField(int $kCustomerField)
    {
        return $this->customerFields[$kCustomerField] ?? null;
    }

    /**
     * @param object $customerField
     * @return null|object[]
     */
    public function getCustomerFieldValues($customerField): ?array
    {
        $this->prepare($customerField);

        if ($customerField->cTyp === 'auswahl') {
            return Shop::Container()->getDB()->selectAll(
                'tkundenfeldwert',
                'kKundenfeld',
                $customerField->kKundenfeld,
                '*',
                'nSort, kKundenfeldWert ASC'
            );
        }

        return null;
    }

    /**
     * @param int $kCustomerField
     * @return bool
     */
    public function delete(int $kCustomerField): bool
    {
        if ($kCustomerField !== 0) {
            $ret = Shop::Container()->getDB()->delete('tkundenattribut', 'kKundenfeld', $kCustomerField) >= 0
                && Shop::Container()->getDB()->delete('tkundenfeldwert', 'kKundenfeld', $kCustomerField) >= 0
                && Shop::Container()->getDB()->delete('tkundenfeld', 'kKundenfeld', $kCustomerField) >= 0;

            if ($ret) {
                unset($this->customerFields[$kCustomerField]);
            } else {
                $this->loadFields($this->langID);
            }

            return $ret;
        }

        return false;
    }

    /**
     * @param int   $customerFieldID
     * @param array $customerFieldValues
     */
    protected function updateCustomerFieldValues(int $customerFieldID, array $customerFieldValues): void
    {
        $db = Shop::Container()->getDB();
        $db->delete('tkundenfeldwert', 'kKundenfeld', $customerFieldID);

        foreach ($customerFieldValues as $customerFieldValue) {
            $entitie              = new stdClass();
            $entitie->kKundenfeld = $customerFieldID;
            $entitie->cWert       = $customerFieldValue['cWert'];
            $entitie->nSort       = (int)$customerFieldValue['nSort'];

            $db->insert('tkundenfeldwert', $entitie);
        }

        // Delete all customer values that are not in value list
        $db->executeQueryPrepared(
            "DELETE tkundenattribut
                    FROM tkundenattribut
                    INNER JOIN tkundenfeld ON tkundenfeld.kKundenfeld = tkundenattribut.kKundenfeld
                    WHERE tkundenfeld.cTyp = 'auswahl'
                        AND tkundenfeld.kKundenfeld = :kKundenfeld
                        AND NOT EXISTS (
                            SELECT 1
                            FROM tkundenfeldwert
                            WHERE tkundenfeldwert.kKundenfeld = tkundenattribut.kKundenfeld
                                AND tkundenfeldwert.cWert = tkundenattribut.cWert
                        )",
            ['kKundenfeld' => $customerFieldID],
            ReturnType::DEFAULT
        );
    }

    /**
     * @param object     $customerField
     * @param null|array $customerFieldValues
     * @return bool
     */
    public function save($customerField, $customerFieldValues = null): bool
    {
        $this->prepare($customerField);
        $key = $customerField->kKundenfeld ?? null;
        $ret = false;

        if ($key !== null && isset($this->customerFields[$key])) {
            // update...
            $oldType                    = $this->customerFields[$key]->cTyp;
            $this->customerFields[$key] = clone $customerField;
            // this entities are not changeable
            unset($customerField->kKundenfeld, $customerField->kSprache, $customerField->cWawi);

            $ret = Shop::Container()->getDB()->update('tkundenfeld', 'kKundenfeld', $key, $customerField) >= 0;

            if ($oldType !== $customerField->cTyp) {
                // cTyp has been changed
                if ($oldType === 'auswahl') {
                    // cTyp changed from "auswahl" to something else - delete values for the customer field
                    Shop::Container()->getDB()->delete('tkundenfeldwert', 'kKundenfeld', $key);
                }
                switch ($customerField->cTyp) {
                    case 'zahl':
                        // all customer values will be changed to numbers if possible
                        Shop::Container()->getDB()->executeQueryPrepared(
                            'UPDATE tkundenattribut SET
                                cWert =	CAST(CAST(cWert AS DOUBLE) AS CHAR)
                                WHERE tkundenattribut.kKundenfeld = :kKundenfeld',
                            ['kKundenfeld' => $key],
                            ReturnType::AFFECTED_ROWS
                        );
                        break;
                    case 'datum':
                        // all customer values will be changed to date if possible
                        Shop::Container()->getDB()->executeQueryPrepared(
                            "UPDATE tkundenattribut SET
                                cWert =	DATE_FORMAT(STR_TO_DATE(cWert, '%d.%m.%Y'), '%d.%m.%Y')
                                WHERE tkundenattribut.kKundenfeld = :kKundenfeld",
                            ['kKundenfeld' => $key],
                            ReturnType::AFFECTED_ROWS
                        );
                        break;
                    case 'text':
                    default:
                        // changed to text - nothing to do...
                        break;
                }
            }
        } else {
            $key = Shop::Container()->getDB()->insert('tkundenfeld', $customerField);

            if ($key > 0) {
                $customerField->kKundenfeld = $key;
                $this->customerFields[$key] = $customerField;

                $ret = true;
            }
        }

        if ($ret) {
            if ($customerField->cTyp === 'auswahl' && \is_array($customerFieldValues)) {
                $this->updateCustomerFieldValues($key, $customerFieldValues);
            }
        } else {
            $this->loadFields($this->langID);
        }

        return $ret;
    }
}
