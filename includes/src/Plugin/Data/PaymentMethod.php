<?php declare(strict_types=1);

namespace JTL\Plugin\Data;

use JTL\MagicCompatibilityTrait;
use JTL\Plugin\PluginInterface;
use stdClass;

/**
 * Class PaymentMethod
 * @package JTL\Plugin\Data
 */
class PaymentMethod
{
    use MagicCompatibilityTrait;

    /**
     * @var int
     */
    private $methodID = 0;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $moduleID = '';

    /**
     * @var array
     */
    private $customerGroups = [];

    /**
     * @var string
     */
    private $template = '';

    /**
     * @var string
     */
    private $templateFilePath = '';

    /**
     * @var string
     */
    private $additionalTemplate = '';

    /**
     * @var string
     */
    private $image = '';

    /**
     * @var int
     */
    private $sort = 0;

    /**
     * @var bool
     */
    private $sendMail = false;

    /**
     * @var bool
     */
    private $active = false;

    /**
     * @var string
     */
    private $provider = '';

    /**
     * @var string
     */
    private $tsCode = '';

    /**
     * @var bool
     */
    private $duringOrder = false;

    /**
     * @var bool
     */
    private $useCurl = false;

    /**
     * @var bool
     */
    private $useSoap = false;

    /**
     * @var bool
     */
    private $useSockets = false;

    /**
     * @var bool
     */
    private $usable = false;

    /**
     * @var int
     */
    private $pluginID = 0;

    /**
     * @var string
     */
    private $classFile = '';

    /**
     * @var string
     */
    private $className = '';

    /**
     * @var string
     */
    private $templatePath = '';

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var array
     */
    private $localization = [];

    /**
     * @var string
     */
    private $classFilePath = '';

    /**
     * @var array
     */
    public static $mapping = [
        'kZahlungsart'                    => 'MethodID',
        'cName'                           => 'Name',
        'cModulId'                        => 'ModuleID',
        'cPluginTemplate'                 => 'Template',
        'cZusatzschrittTemplate'          => 'AdditionalTemplate',
        'cBild'                           => 'Image',
        'nSort'                           => 'Sort',
        'nMailSenden'                     => 'SendMail',
        'nActive'                         => 'Active',
        'cAnbieter'                       => 'Provider',
        'cTSCode'                         => 'TsCode',
        'nWaehrendBestellung'             => 'DuringOrder',
        'nCURL'                           => 'UseCurl',
        'nSOAP'                           => 'UseSoap',
        'nSOCKETS'                        => 'UseSockets',
        'nNutzbar'                        => 'Usable',
        'kPlugin'                         => 'PluginID',
        'cClassPfad'                      => 'ClassFile',
        'cClassName'                      => 'ClassName',
        'cTemplatePfad'                   => 'TemplatePath',
        'oZahlungsmethodeEinstellung_arr' => 'Config',
        'oZahlungsmethodeSprache_arr'     => 'Localization',
        'cTemplateFileURL'                => 'TemplateFilePath',
    ];

    /**
     * PaymentMethod constructor.
     * @param stdClass|null        $data
     * @param PluginInterface|null $plugin
     */
    public function __construct(stdClass $data = null, PluginInterface $plugin = null)
    {
        if ($data !== null && \SAFE_MODE === false) {
            $this->mapData($data, $plugin);
        }
    }

    /**
     * @param stdClass        $data
     * @param PluginInterface $plugin
     */
    public function mapData(stdClass $data, PluginInterface $plugin): void
    {
        foreach (\get_object_vars($data) as $item => $value) {
            $method = self::$mapping[$item] ?? null;
            if ($method === null) {
                continue;
            }
            $method = 'set' . $method;
            $this->$method($value);
        }
        $this->classFilePath = $plugin->getPaths()->getVersionedPath() . \PFAD_PLUGIN_PAYMENTMETHOD . $this->classFile;
        if (\file_exists($this->classFilePath)) {
            global $oPlugin;
            $oPlugin = $plugin;
            require_once $this->classFilePath;
            if (!\class_exists($this->className)) {
                $class = \sprintf(
                    'Plugin\\%s\\%s\\%s',
                    $plugin->getPluginID(),
                    \rtrim(\PFAD_PLUGIN_PAYMENTMETHOD, '/'),
                    $this->className
                );
                if (\class_exists($class)) {
                    $this->className = $class;
                }
            }
        }
    }

    /**
     * @return int
     */
    public function getMethodID(): int
    {
        return $this->methodID;
    }

    /**
     * @param int $methodID
     */
    public function setMethodID(int $methodID): void
    {
        $this->methodID = $methodID;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getModuleID(): string
    {
        return $this->moduleID;
    }

    /**
     * @param string $moduleID
     */
    public function setModuleID(string $moduleID): void
    {
        $this->moduleID = $moduleID;
    }

    /**
     * @return array
     */
    public function getCustomerGroups(): array
    {
        return $this->customerGroups;
    }

    /**
     * @param array $customerGroups
     */
    public function setCustomerGroups(array $customerGroups): void
    {
        $this->customerGroups = $customerGroups;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getTemplateFilePath(): string
    {
        return $this->templateFilePath;
    }

    /**
     * @param string $templateFilePath
     */
    public function setTemplateFilePath(string $templateFilePath): void
    {
        $this->templateFilePath = $templateFilePath;
    }

    /**
     * @return string
     */
    public function getAdditionalTemplate(): string
    {
        return $this->additionalTemplate;
    }

    /**
     * @param string $additionalTemplate
     */
    public function setAdditionalTemplate(string $additionalTemplate): void
    {
        $this->additionalTemplate = $additionalTemplate;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @param string $image
     */
    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @param int $sort
     */
    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }

    /**
     * @return bool
     */
    public function getSendMail(): bool
    {
        return $this->sendMail;
    }

    /**
     * @param bool|int $sendMail
     */
    public function setSendMail($sendMail): void
    {
        $this->sendMail = (bool)$sendMail;
    }

    /**
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool|int $active
     */
    public function setActive($active): void
    {
        $this->active = (bool)$active;
    }

    /**
     * @return string
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * @param string $provider
     */
    public function setProvider(string $provider): void
    {
        $this->provider = $provider;
    }

    /**
     * @return string
     */
    public function getTsCode(): string
    {
        return $this->tsCode;
    }

    /**
     * @param string $tsCode
     */
    public function setTsCode(string $tsCode): void
    {
        $this->tsCode = $tsCode;
    }

    /**
     * @return bool
     */
    public function getDuringOrder(): bool
    {
        return $this->duringOrder;
    }

    /**
     * @param bool|int $duringOrder
     */
    public function setDuringOrder($duringOrder): void
    {
        $this->duringOrder = (bool)$duringOrder;
    }

    /**
     * @return bool
     */
    public function getUseCurl(): bool
    {
        return $this->useCurl;
    }

    /**
     * @param bool|int $useCurl
     */
    public function setUseCurl($useCurl): void
    {
        $this->useCurl = (bool)$useCurl;
    }

    /**
     * @return bool
     */
    public function getUseSoap(): bool
    {
        return $this->useSoap;
    }

    /**
     * @param bool|int $useSoap
     */
    public function setUseSoap($useSoap): void
    {
        $this->useSoap = (bool)$useSoap;
    }

    /**
     * @return bool
     */
    public function getUseSockets(): bool
    {
        return $this->useSockets;
    }

    /**
     * @param bool|int $useSockets
     */
    public function setUseSockets($useSockets): void
    {
        $this->useSockets = (bool)$useSockets;
    }

    /**
     * @return bool
     */
    public function getUsable(): bool
    {
        return $this->usable;
    }

    /**
     * @param bool|int $usable
     */
    public function setUsable($usable): void
    {
        $this->usable = (bool)$usable;
    }

    /**
     * @return int
     */
    public function getPluginID(): int
    {
        return $this->pluginID;
    }

    /**
     * @param int $pluginID
     */
    public function setPluginID(int $pluginID): void
    {
        $this->pluginID = $pluginID;
    }

    /**
     * @return string
     */
    public function getClassFile(): string
    {
        return $this->classFile;
    }

    /**
     * @param string $classFile
     */
    public function setClassFile(string $classFile): void
    {
        $this->classFile = $classFile;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @param string $className
     */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    /**
     * @param string $templatePath
     */
    public function setTemplatePath(string $templatePath): void
    {
        $this->templatePath = $templatePath;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getLocalization(): array
    {
        return $this->localization;
    }

    /**
     * @param array $localization
     */
    public function setLocalization(array $localization): void
    {
        $this->localization = $localization;
    }

    /**
     * @return string
     */
    public function getClassFilePath(): string
    {
        return $this->classFilePath;
    }

    /**
     * @param string $classFilePath
     */
    public function setClassFilePath(string $classFilePath): void
    {
        $this->classFilePath = $classFilePath;
    }
}
