<?php declare(strict_types=1);

namespace JTL\GeneralDataProtection;

use JTL\Shop;

/**
 * Class IpAnonymizer
 * @package JTL\GeneralDataProtection
 * v4
 * anonymize()       : 255.255.255.34 -> 255.255.255.0
 * anonymizeLegacy() : 255.255.255.34 -> 255.255.255.*
 * v6
 * anonymize()       : 2001:0db8:85a3:08d3:1319:8a2e:0370:7347 -> 2001:db8:85a3:8d3:0:0:0:0   (also cuts leading zeros!)
 * anonymizeLegacy() : 2001:0db8:85a3:08d3:1319:8a2e:0370:7347 -> 2001:0db8:85a3:08d3:*:*:*:*
 */
class IpAnonymizer
{
    /**
     * IP-string, human readable
     *
     * @var string
     */
    private $ip;

    /**
     * binary "packed" IP
     *
     * @var string
     */
    private $rawIp;

    /**
     * current IP-anonymization-mask
     *
     * @var string
     */
    private $ipMask;

    /**
     * @var string
     */
    private $ipMaskV4;

    /**
     * @var string
     */
    private $ipMaskV6;

    /**
     * current placholder (if the given IP was invalid)
     *
     * @var string
     */
    private $placeholderIP = '0.0.0.0';

    /**
     * flag for old fashioned anonymization ("do not anonymize again")
     *
     * @var bool
     */
    private $oldFashionedAnon = false;

    /**
     * flag to get "0:0:0:0:0:0:0:0" instead of "::" ("::" is a valid IPv6-notation too!)
     *
     * @var bool
     */
    private $beautifyFlag = false;

    /**
     * @var object Monolog\Logger
     */
    private $logger;

    /**
     * @param string
     * @param bool
     */
    public function __construct(string $ip = '', bool $beautify = false)
    {
        try {
            $this->logger = Shop::Container()->getLogService();
        } catch (\Exception $e) {
            $this->logger = null;
        }

        $this->setMaskV4('255.255.0.0');
        $this->setMaskV6('ffff:ffff:ffff:ffff:0000:0000:0000:0000');

        if ($ip !== '') {
            $this->ip = $ip;
            try {
                $this->init();
            } catch (\Exception $e) {
                // The current PHP-version did not support IPv6 addresses!
                ($this->logger !== null) ?: $this->logger->log(\JTLLOG_LEVEL_NOTICE, $e->getMessage());

                return;
            }
        }
        if ($beautify !== false) {
            $this->beautifyFlag = true;
        }
    }

    /**
     * analyze the given IP and set the object-values
     *
     * @throws \RuntimeException
     */
    private function init(): void
    {
        if ($this->ip === '' || \mb_strpos($this->ip, '*') !== false) {
            // if there is an old fashioned anonymization or
            // an empty string, we do nothing (but set a flag)
            $this->oldFashionedAnon = true;

            return;
        }
        // any ':' means, we got an IPv6-address
        // ("::127.0.0.1" or "::ffff:127.0.0.3" is valid too!)
        if (\mb_strpos($this->ip, ':') !== false) {
            $this->rawIp = @\inet_pton($this->ip);
        } else {
            $this->rawIp = @\inet_pton($this->rmLeadingZero($this->ip));
        }
        if ($this->rawIp === false) {
            ($this->logger !== null) ?: $this->logger->log(
                \JTLLOG_LEVEL_WARNING,
                'Wrong IP: ' . $this->ip
            );
            $this->rawIp = '';
        }
        $this->placeholderIP = '0.0.0.0';
        $this->ipMask        = $this->getMaskV4();
        if (\strlen($this->rawIp) === 16) {
            if (\defined('AF_INET6')) {
                $this->placeholderIP = '0000:0000:0000:0000:0000:0000:0000:0000';
                $this->ipMask        = $this->getMaskV6();
            } else {
                // this should normally never happen! (wrong compile-time setting of PHP)
                throw new \RuntimeException('PHP wurde mit der Option "--disable-ipv6" compiliert!');
            }
        }
    }

    /**
     * @param string
     * @return self
     * @throws \Exception
     */
    public function setIp(string $ip = ''): self
    {
        if ($ip !== '') {
            $this->ip = $ip;
            $this->init();
        }

        return $this;
    }

    /**
     * delivers am valid IP-string,
     * (by conventions, with "0 summerized", for IPv6 addresses
     * use the "beautify-flag", during object construction, to get "0")
     *
     * @return string
     */
    public function anonymize(): string
    {
        if ((string)$this->rawIp === '') {
            return '';
        }
        if ($this->oldFashionedAnon !== false) {
            return $this->ip;
        }
        $readableIP = \inet_ntop(\inet_pton($this->ipMask) & $this->rawIp);
        if ($this->beautifyFlag === true && \mb_strpos($readableIP, '::') !== false) {
            $colonPos    = \mb_strpos($readableIP, '::');
            $strEnd      = \mb_strlen($readableIP) - 2;
            $blockCount  = \count(
                \preg_split('/:/', \str_replace('::', ':', $readableIP), -1, \PREG_SPLIT_NO_EMPTY)
            );
            $replacement = '';
            $diff        = 8 - $blockCount;
            for ($i = 0; $i < $diff; $i++) {
                ($replacement === '') ? $replacement .= '0' : $replacement .= ':0';
            }
            if (($colonPos | $strEnd) === 0) { // for pure "::"
                $readableIP = $replacement;
            } elseif ($colonPos === 0) {
                $readableIP = \str_replace('::', $replacement . ':', $readableIP);
            } elseif ($colonPos === $strEnd) {
                $readableIP = \str_replace('::', ':' . $replacement, $readableIP);
            } else {
                $readableIP = \str_replace('::', ':' . $replacement . ':', $readableIP);
            }
        }

        return $readableIP;
    }

    /**
     * delivers an IP the legacy way:
     * not optimized (zeros summerized) and with atseriscs as obvuscation
     *
     * @return string
     */
    public function anonymizeLegacy(): string
    {
        $maskParts           = \preg_split('/[\.:]/', $this->ipMask);
        $ipParts             = \preg_split('/[\.:]/', $this->ip);
        $len                 = \count($ipParts);
        ($len === 4) ? $glue = '.' : $glue = ':';
        for ($i = 0; $i < $len; $i++) {
            (\hexdec($maskParts[$i]) !== 0) ?: $ipParts[$i] = '*';
        }
        return \implode($glue, $ipParts);
    }

    /**
     * @return string
     */
    public function getMaskV4(): string
    {
        return $this->ipMaskV4;
    }

    /**
     * @return string
     */
    public function getMaskV6(): string
    {
        return $this->ipMaskV6;
    }

    /**
     * @param string
     */
    public function setMaskV4(string $mask): void
    {
        $this->ipMaskV4 = $mask;
    }

    /**
     * @param string
     */
    public function setMaskV6(string $mask): void
    {
        $this->ipMaskV6 = $mask;
    }

    /**
     * return a corresponding placeholder for "do not save any IP"
     *
     * @return string
     */
    public function getPlaceholder(): string
    {
        return $this->placeholderIP;
    }

    /**
     * remove leading zeros from the ip-string
     * (by converting each part to integer)
     *
     * @param string
     * @return string
     */
    private function rmLeadingZero(string $ipString): string
    {
        $ipParts = \preg_split('/[\.:]/', $ipString);
        $glue    = \mb_strpos($ipString, '.') !== false ? '.' : ':';

        return \implode($glue, \array_map(static function ($e) {
            return (int)$e;
        }, $ipParts));
    }
}
