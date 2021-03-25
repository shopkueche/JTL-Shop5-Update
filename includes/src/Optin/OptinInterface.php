<?php declare(strict_types=1);

namespace JTL\Optin;

/**
 * Interface OptinInterface
 * @package JTL\Optin
 */
interface OptinInterface
{
    /**
     * @param OptinRefData $refData
     * @return OptinInterface
     */
    public function createOptin(OptinRefData $refData): OptinInterface;

    /**
     * @return mixed
     */
    public function activateOptin();

    /**
     * @return mixed
     */
    public function deactivateOptin();

    /**
     * @return mixed
     */
    public function sendActivationMail();
}
