<?php declare(strict_types=1);

namespace Systemcheck\Platform;

/**
 * Class Hosting
 * @package Systemcheck\Platform
 */
class Hosting
{
    /**
     * PROVIDER_1UND1
     */
    public const PROVIDER_1UND1 = '1und1';

    /**
     * PROVIDER_STRATO
     */
    public const PROVIDER_STRATO = 'strato';

    /**
     * PROVIDER_HOSTEUROPE
     */
    public const PROVIDER_HOSTEUROPE = 'hosteurope';

    /**
     * PROVIDER_ALFAHOSTING
     */
    public const PROVIDER_ALFAHOSTING = 'alfahosting';

    /**
     * PROVIDER_JTL
     */
    public const PROVIDER_JTL = 'jtl';

    /**
     * PROVIDER_HETZNER
     */
    public const PROVIDER_HETZNER = 'hetzner';

    /**
     * hostname
     * @var string
     */
    protected $hostname;

    /**
     * @var string
     */
    protected $documentRoot;

    /**
     * @var string
     */
    protected $provider;

    /**
     * Hosting constructor.
     */
    public function __construct()
    {
        $this->documentRoot = $_SERVER['DOCUMENT_ROOT'];
        $this->detect();
    }

    /**
     * @return string
     */
    public function getHostname(): ?string
    {
        return $this->hostname;
    }

    /**
     * @return string|null
     */
    public function getDocumentRoot(): ?string
    {
        return $this->documentRoot;
    }

    /**
     * @return string|null
     */
    public function getProvider(): ?string
    {
        return $this->provider;
    }

    /**
     * @return string
     */
    public function getPhpVersion(): string
    {
        return \PHP_VERSION;
    }

    /**
     *
     */
    private function detect(): void
    {
        $hostname = \gethostbyaddr($_SERVER['SERVER_ADDR']);

        if (\preg_match('/jtl-software\.de$/', $hostname)) {
            $this->provider = self::PROVIDER_JTL;
        } elseif (\preg_match('/hosteurope\.de$/', $hostname)) {
            $this->provider = self::PROVIDER_HOSTEUROPE;
        } elseif (\preg_match('/your-server\.de$/', $hostname)) {
            $this->provider = self::PROVIDER_HETZNER;
        } elseif (\preg_match('/kundenserver\.de$/', $hostname)) {
            $this->provider = self::PROVIDER_1UND1;
        } elseif (\preg_match('/stratoserver\.net$/', $hostname)) {
            $this->provider = self::PROVIDER_STRATO;
        } elseif (\preg_match('/alfahosting-server\.de$/', $hostname)) {
            $this->provider = self::PROVIDER_ALFAHOSTING;
        }

        $this->hostname = $hostname;
    }

}
