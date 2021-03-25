<?php

namespace JTL\Services;

use JTL\Backend\AdminAccount;
use JTL\Boxes\FactoryInterface;
use JTL\Cache\JTLCacheInterface;
use JTL\Consent\ManagerInterface;
use JTL\DB\DbInterface;
use JTL\DB\Services\GcServiceInterface;
use JTL\Debug\JTLDebugBar;
use JTL\Exceptions\CircularReferenceException;
use JTL\Exceptions\ServiceNotFoundException;
use JTL\L10n\GetText;
use JTL\OPC\DB;
use JTL\OPC\Locker;
use JTL\OPC\PageDB;
use JTL\OPC\PageService;
use JTL\OPC\Service;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Services\JTL\BoxServiceInterface;
use JTL\Services\JTL\CaptchaServiceInterface;
use JTL\Services\JTL\CountryServiceInterface;
use JTL\Services\JTL\CryptoServiceInterface;
use JTL\Services\JTL\LinkServiceInterface;
use JTL\Services\JTL\NewsServiceInterface;
use JTL\Services\JTL\PasswordServiceInterface;
use JTL\Template\TemplateServiceInterface;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Interface DefaultServicesInterface
 *
 * This interface provides default services, that are provided by JTL-Shop core. Those Services are provided through a
 * separate interface for improving IntelliSense support for external and internal developers
 *
 * @package JTL\Services
 */
interface DefaultServicesInterface extends ContainerInterface
{
    /**
     * @return DbInterface
     */
    public function getDB(): DbInterface;

    /**
     * @return PasswordServiceInterface
     */
    public function getPasswordService(): PasswordServiceInterface;

    /**
     * @return CryptoServiceInterface
     */
    public function getCryptoService(): CryptoServiceInterface;

    /**
     * @return GcServiceInterface
     */
    public function getDBServiceGC(): GcServiceInterface;

    /**
     * @return JTLCacheInterface
     */
    public function getCache(): JTLCacheInterface;

    /**
     * @return LoggerInterface
     * @throws ServiceNotFoundException
     * @throws CircularReferenceException
     */
    public function getBackendLogService(): LoggerInterface;

    /**
     * @return Service
     */
    public function getOPC(): Service;

    /**
     * @return PageService
     */
    public function getOPCPageService(): PageService;

    /**
     * @return DB
     */
    public function getOPCDB(): DB;

    /**
     * @return PageDB
     */
    public function getOPCPageDB(): PageDB;

    /**
     * @return Locker
     */
    public function getOPCLocker(): Locker;

    /**
     * @return Logger
     * @throws ServiceNotFoundException
     * @throws CircularReferenceException
     */
    public function getLogService(): Logger;

    /**
     * @return LinkServiceInterface
     */
    public function getLinkService(): LinkServiceInterface;

    /**
     * @return FactoryInterface
     */
    public function getBoxFactory(): FactoryInterface;

    /**
     * @return BoxServiceInterface
     */
    public function getBoxService(): BoxServiceInterface;

    /**
     * @return CaptchaServiceInterface
     */
    public function getCaptchaService(): CaptchaServiceInterface;

    /**
     * @return NewsServiceInterface
     */
    public function getNewsService(): NewsServiceInterface;

    /**
     * @return AlertServiceInterface
     */
    public function getAlertService(): AlertServiceInterface;

    /**
     * @return ManagerInterface
     */
    public function getConsentManager(): ManagerInterface;

    /**
     * @return GetText
     */
    public function getGetText(): GetText;

    /**
     * @return AdminAccount
     */
    public function getAdminAccount(): AdminAccount;

    /**
     * @return JTLDebugBar
     */
    public function getDebugBar(): JTLDebugBar;

    /**
     * @return CountryServiceInterface
     */
    public function getCountryService(): CountryServiceInterface;

    /**
     * @return TemplateServiceInterface
     */
    public function getTemplateService(): TemplateServiceInterface;
}
