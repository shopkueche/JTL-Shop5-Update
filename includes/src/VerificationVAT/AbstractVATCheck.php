<?php

namespace JTL\VerificationVAT;

use Psr\Log\LoggerInterface;

/**
 * Class AbstractVATCheck
 * @package JTL\VerificationVAT
 */
abstract class AbstractVATCheck implements VATCheckInterface
{
    /**
     * @var VATCheckDownSlots
     */
    protected $downTimes;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * VATCheckEU constructor.
     * @param VATCheckDownSlots $slots
     * @param LoggerInterface   $logger
     */
    public function __construct(VATCheckDownSlots $slots, LoggerInterface $logger)
    {
        $this->downTimes = $slots;
        $this->logger    = $logger;
    }

    /**
     * spaces can't handled by the VIES-system,
     * so we condense the ID-string here and let them out
     *
     * @param string $sourceString
     * @return string
     */
    public function condenseSpaces($sourceString): string
    {
        return \str_replace(' ', '', $sourceString);
    }
}
