<?php declare(strict_types=1);

namespace JTL\Optin;

use JTL\DB\DbInterface;
use stdClass;

/**
 * Class OptinBase
 * @package JTL\Optin
 */
abstract class OptinBase extends OptinFactory
{
    /**
     * action prefix
     */
    public const ACTIVATE_CODE = 'ac';

    /**
     * action prefix
     */
    public const DELETE_CODE = 'dc';

    /**
     * @var \DateTime
     */
    protected $nowDataTime;

    /**
     * @var DbInterface
     */
    protected $dbHandler;

    /**
     * @var string
     */
    protected $emailAddress = '';

    /**
     * @var string
     */
    protected $optCode = '';

    /**
     * @var string
     */
    protected $actionPrefix = '';

    /**
     * @var OptinRefData
     */
    protected $refData;

    /**
     * @var object stdClass
     */
    protected $foundOptinTupel;

    /**
     * @param string $mailaddress
     * @return Optin
     */
    public function setEmail(string $mailaddress): self
    {
        $this->emailAddress = $mailaddress;

        return $this;
    }

    /**
     * @param $optinCodeWithPrefix
     * @return Optin
     */
    public function setCode(string $optinCodeWithPrefix): self
    {
        $this->actionPrefix = \substr($optinCodeWithPrefix, 0, 2);
        $this->optCode      = \substr($optinCodeWithPrefix, 2);

        return $this;
    }

    /**
     * load a optin-tupel, via opt-code or email and
     * restore its reference data
     */
    protected function loadOptin(): void
    {
        if (empty($this->emailAddress)) {
            $this->foundOptinTupel = $this->dbHandler->select('toptin', 'kOptinCode', $this->optCode);
        } else {
            $this->foundOptinTupel = $this->dbHandler->select('toptin', 'cMail', $this->emailAddress);
        }
        if (!empty($this->foundOptinTupel)) {
            $this->refData = \unserialize($this->foundOptinTupel->cRefData, ['OptinRefData']);
        }
    }

    /**
     * @param string $implementationClass
     * @return array
     */
    protected function loadOptinsByImplementation(string $implementationClass): array
    {
        return $this->dbHandler->selectArray('toptin', 'kOptinClass', $implementationClass);
    }

    /**
     * @return string
     */
    protected function generateUniqOptinCode(): string
    {
        $count       = 0;
        $safetyLimit = 50;
        $Id          = function () {
            return \md5($this->refData->getEmail() . \time() . \random_int(123, 456));
        };
        do {
            $newId = $Id();
            $count++;
        } while (!empty($this->dbHandler->select('toptin', 'kOptinCode', $newId)) || $count === $safetyLimit);

        return $newId;
    }

    /**
     * @param string $optCode
     */
    protected function saveOptin(string $optCode): void
    {
        $this->refData->setOptinClass(static::class); // save the caller
        $this->optCode       = $optCode;
        $newRow              = new stdClass();
        $newRow->kOptinCode  = $this->optCode;
        $newRow->kOptinClass = static::class;
        $newRow->cMail       = $this->refData->getEmail();
        $newRow->cRefData    = \serialize($this->refData);
        $newRow->dCreated    = $this->nowDataTime->format('Y-m-d H:i:s');
        $this->dbHandler->insert('toptin', $newRow);
    }

    /**
     * @throws \Exception
     */
    public function activateOptin(): void
    {
        $rowData = new stdClass();
        if (empty($this->foundOptinTupel->dActivated)) {
            $rowData->dActivated = $this->nowDataTime->format('Y-m-d H:i:s');
            $this->dbHandler->update('toptin', 'kOptinCode', $this->optCode, $rowData);
        }
    }

    /**
     * deactivate and cleanup this optin
     */
    public function deactivateOptin(): void
    {
        $this->finishOptin();
    }

    /**
     * only move the optin-tupel to the history
     */
    public function finishOptin(): void
    {
        if (empty($this->foundOptinTupel)) {
            return;
        }
        $newRow               = new stdClass();
        $newRow->kOptinCode   = $this->foundOptinTupel->kOptinCode;
        $newRow->kOptinClass  = $this->foundOptinTupel->kOptinClass;
        $newRow->cMail        = 'anonym'; // anonymized for history
        $newRow->cRefData     = \is_a($this->refData, OptinRefData::class)
            ? \serialize($this->refData->anonymized()) // anonymized for history
            : '';
        $newRow->dCreated     = $this->foundOptinTupel->dCreated;
        $newRow->dActivated   = $this->foundOptinTupel->dActivated;
        $newRow->dDeActivated = $this->nowDataTime->format('Y-m-d H:i:s');
        foreach (\array_keys(\get_object_vars($newRow)) as $element) {
            if (empty($newRow->$element)) {
                unset($newRow->$element);
            }
        }
        $this->dbHandler->insert('toptinhistory', $newRow);
        $this->dbHandler->delete('toptin', 'kOptinCode', $this->foundOptinTupel->kOptinCode);
    }

    /**
     * '$optins' is a array of objects, where each object must contain at least one field with a opt-in code
     * (note: "bulkActivateOptins()" is dependent on the specified optin-implementation)
     *
     * @param array  $optins
     * @param string $optCodeField
     */
    public function bulkDeleteOptins(array $optins, string $optCodeField): void
    {
        foreach ($optins as $singleOptin) {
            $this->setCode($singleOptin->$optCodeField);
            $this->loadOptin();
            $this->deactivateOptin(); // "shift" to history
        }
    }
}
