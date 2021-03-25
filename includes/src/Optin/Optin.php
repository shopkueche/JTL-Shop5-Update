<?php declare(strict_types=1);

namespace JTL\Optin;

use JTL\Exceptions\EmptyResultSetException;
use JTL\Exceptions\InvalidInputException;
use JTL\Shop;

/**
 * Class Optin
 * @package JTL\Optin
 */
class Optin extends OptinBase
{
    /**
     * @var OptinInterface
     */
    protected $currentOptin;

    /**
     * @var string actionPrefix
     */
    protected $externalAction;

    /**
     * Optin constructor.
     *
     * @param string|null $optinClass
     * @throws EmptyResultSetException
     */
    public function __construct(string $optinClass = null)
    {
        $this->dbHandler   = Shop::Container()->getDB();
        $this->nowDataTime = new \DateTime();

        if ($optinClass !== null) {
            $this->generateOptin($optinClass);
        }
    }

    /**
     * @return OptinInterface
     */
    public function getOptinInstance(): OptinInterface
    {
        return $this->currentOptin;
    }

    /**
     * @param string $action
     * @return Optin
     */
    public function setAction(string $action): Optin
    {
        $this->externalAction = $action;

        return $this;
    }

    /**
     * return message meanings:
     * 'optinCanceled'       = cancel (a previously active) subscription
     * 'optinRemoved'        = cancel optin without the existence of a subscription
     * 'optinSucceeded'      = subscription successfully
     * 'optinSucceededAgain' = user clicked again
     *
     * @return string
     * @throws EmptyResultSetException
     * @throws InvalidInputException
     */
    public function handleOptin(): string
    {
        if ($this->optCode === '' && $this->emailAddress === '') {
            throw new InvalidInputException('missing email and/or optin-code.');
        }
        $this->loadOptin();
        if (empty($this->foundOptinTupel)) {
            throw new EmptyResultSetException('Double-Opt-in not found: ' .
                (($this->emailAddress !== '') ? $this->emailAddress : $this->optCode));
        }
        $this->generateOptin($this->refData->getOptinClass());
        if ($this->actionPrefix === self::DELETE_CODE || $this->externalAction === self::DELETE_CODE) {
            $this->deactivateOptin();
            if (!empty($this->currentOptin)) {
                $this->currentOptin->deactivateOptin();
            }

            return empty($this->foundOptinTupel->dActivated) ? 'optinRemoved' : 'optinCanceled';
        }
        if ($this->actionPrefix === self::ACTIVATE_CODE || $this->externalAction === self::ACTIVATE_CODE) {
            $this->activateOptin();
            if (!empty($this->currentOptin)) {
                $this->currentOptin->activateOptin();
            }

            return empty($this->foundOptinTupel->dActivated) ? 'optinSucceeded' : 'optinSucceededAgain';
        }
        throw new InvalidInputException('unknown action received.');
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        $this->loadOptin();

        return !empty($this->foundOptinTupel->dActivated);
    }

    /**
     * @param string $optinClass
     * @throws EmptyResultSetException
     */
    private function generateOptin(string $optinClass): void
    {
        $this->currentOptin = OptinFactory::getInstance(
            $optinClass,
            $this->dbHandler,
            $this->nowDataTime,
            $this->refData,
            $this->emailAddress,
            $this->optCode,
            $this->actionPrefix
        );
        if ($this->currentOptin === null) {
            throw new EmptyResultSetException('Optin class not found');
        }
    }
}
