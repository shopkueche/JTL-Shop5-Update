<?php declare(strict_types=1);

namespace JTL\Template\Admin;

use Exception;
use InvalidArgumentException;
use JTL\Alert\Alert;
use JTL\Cache\JTLCacheInterface;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Overlay;
use JTL\Helpers\Request;
use JTL\Plugin\Admin\Installation\InstallationResponse;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;
use JTL\Template\Admin\Validation\TemplateValidator;
use JTL\Template\BootChecker;
use JTL\Template\Config;
use JTL\Template\Model;
use JTL\Template\XMLReader;
use JTLShop\SemVer\Version;
use stdClass;
use function Functional\first;

/**
 * Class Controller
 * @package JTL\Template\Admin
 */
class Controller
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var JTLCacheInterface
     */
    private $cache;

    /**
     * @var AlertServiceInterface
     */
    private $alertService;

    /**
     * @var string|null
     */
    private $currentTemplateDir;

    /**
     * @var JTLSmarty
     */
    private $smarty;

    /**
     * @var Config
     */
    private $config;

    /**
     * Controller constructor.
     * @param DbInterface           $db
     * @param JTLCacheInterface     $cache
     * @param AlertServiceInterface $alertService
     * @param JTLSmarty             $smarty
     */
    public function __construct(
        DbInterface $db,
        JTLCacheInterface $cache,
        AlertServiceInterface $alertService,
        JTLSmarty $smarty
    ) {
        $this->db           = $db;
        $this->cache        = $cache;
        $this->alertService = $alertService;
        $this->smarty       = $smarty;
    }

    public function handleAction(): void
    {
        $action                   = Request::verifyGPDataString('action');
        $valid                    = Form::validateToken();
        $this->currentTemplateDir = \basename(Request::verifyGPDataString('dir'));
        if (!\is_dir(\PFAD_ROOT . \PFAD_TEMPLATES . $this->currentTemplateDir)) {
            $this->currentTemplateDir = null;
            $valid                    = false;
        }
        $this->config = new Config($this->currentTemplateDir, $this->db);
        if (!empty($_FILES['template-install-upload'])) {
            $action = 'upload';
            if (!$valid) {
                $this->failResponse();
                return;
            }
        }
        if (!$valid) {
            $this->displayOverview();
            return;
        }
        switch ($action) {
            case 'config':
                $this->displayTemplateSettings();
                break;
            case 'switch':
                $this->switch();
                if (Request::verifyGPCDataInt('config') === 1) {
                    $this->displayTemplateSettings();
                } else {
                    $this->displayOverview();
                }
                break;
            case 'save-config':
                $this->saveConfig();
                $this->displayOverview();
                break;
            case 'upload':
                $this->upload($_FILES['template-install-upload']);
                break;
            default:
                $this->displayOverview();
                break;
        }
    }

    private function failResponse(): void
    {
        $response = new InstallationResponse();
        $response->setStatus(InstallationResponse::STATUS_FAILED);
        $response->setError(__('errorCSRF'));
        die($response->toJson());
    }

    /**
     * @param array $files
     * @throws \SmartyException
     */
    private function upload(array $files): void
    {
        $extractor = new Extractor();
        $response  = $extractor->extractTemplate($files['tmp_name']);
        if ($response->getStatus() === InstallationResponse::STATUS_OK
            && $response->getDirName()
            && ($bootstrapper = BootChecker::bootstrap(\rtrim($response->getDirName(), '/'))) !== null
        ) {
            $bootstrapper->installed();
        }
        $lstng         = new Listing($this->db, new TemplateValidator($this->db));
        $html          = new stdClass();
        $html->id      = '#shoptemplate-overview';
        $html->content = $this->smarty->assign('listingItems', $lstng->getAll())
            ->assign('shopVersion', Version::parse(\APPLICATION_VERSION))
            ->fetch('tpl_inc/shoptemplate_overview.tpl');
        $response->setHtml($html);
        die($response->toJson());
    }

    /**
     * @param string $dir
     * @param string $type
     * @return bool
     * @throws Exception
     */
    private function setActiveTemplate(string $dir, string $type = 'standard'): bool
    {
        $this->db->delete('ttemplate', 'eTyp', $type);
        $this->db->delete('ttemplate', 'cTemplate', $dir);
        $reader    = new XMLReader();
        $tplConfig = $reader->getXML($dir);
        if ($tplConfig !== null && !empty($tplConfig->Parent)) {
            if (!\is_dir(\PFAD_ROOT . \PFAD_TEMPLATES . $tplConfig->Parent)) {
                return false;
            }
            $parent       = (string)$tplConfig->Parent;
            $parentConfig = $reader->getXML($parent);
        } else {
            $parentConfig = false;
        }
        $model = new Model($this->db);
        if (isset($tplConfig->ExsID)) {
            $model->setExsID((string)$tplConfig->ExsID);
        }
        $model->setCTemplate($dir);
        $model->setType($type);
        if (!empty($tplConfig->Parent)) {
            $model->setParent((string)$tplConfig->Parent);
        }
        $model->setName((string)$tplConfig->Name);
        $model->setAuthor((string)$tplConfig->Author);
        $model->setUrl((string)$tplConfig->URL);
        $model->setPreview((string)$tplConfig->Preview);
        $version = empty($tplConfig->Version) && $parentConfig
            ? (string)$parentConfig->Version
            : (string)$tplConfig->Version;
        $model->setVersion($version);
        if (!empty($tplConfig->Framework)) {
            $model->setFramework((string)$tplConfig->Framework);
        }
        $model->setBootstrap((int)\file_exists(\PFAD_ROOT . \PFAD_TEMPLATES . $dir . '/Bootstrap.php'));
        $save = $model->save();
        if ($save === true) {
            if (!$dh = \opendir(\PFAD_ROOT . \PFAD_COMPILEDIR)) {
                return false;
            }
            while (($obj = \readdir($dh)) !== false) {
                if (\mb_strpos($obj, '.') === 0) {
                    continue;
                }
                if (!\is_dir(\PFAD_ROOT . \PFAD_COMPILEDIR . $obj)) {
                    \unlink(\PFAD_ROOT . \PFAD_COMPILEDIR . $obj);
                }
            }
        }
        $this->cache->flushTags([\CACHING_GROUP_OPTION, \CACHING_GROUP_TEMPLATE]);

        return $save;
    }

    private function saveConfig(): void
    {
        $parentFolder = null;
        $reader       = new XMLReader();
        $tplXML       = $reader->getXML($this->currentTemplateDir);
        if ($tplXML !== null && !empty($tplXML->Parent)) {
            $parentFolder = (string)$tplXML->Parent;
        }
        $tplConfXML = $this->config->getConfigXML($reader, $parentFolder);
        foreach ($tplConfXML as $config) {
            foreach ($config->settings as $setting) {
                if ($setting->cType === 'checkbox') {
                    $value = isset($_POST[$setting->elementID]) ? '1' : '0';
                } else {
                    $value = $_POST[$setting->elementID] ?? null;
                }
                if ($value === null) {
                    continue;
                }
                if (\is_array($value)) {
                    $value = first($value);
                }
                // for uploads, the value of an input field is the $_FILES index of the uploaded file
                if ($setting->cType === 'upload') {
                    try {
                        $value = $this->handleUpload($tplConfXML, $value, $setting->key);
                    } catch (InvalidArgumentException $e) {
                        continue;
                    }
                }
                $this->config->updateConfigInDB($setting->section, $setting->key, $value);
                $this->cache->flushTags([\CACHING_GROUP_OPTION, \CACHING_GROUP_TEMPLATE]);
            }
        }
        $check = $this->setActiveTemplate($this->currentTemplateDir);
        if ($check) {
            $this->alertService->addAlert(Alert::TYPE_SUCCESS, __('successTemplateSave'), 'successTemplateSave');
        } else {
            $this->alertService->addAlert(Alert::TYPE_ERROR, __('errorTemplateSave'), 'errorTemplateSave');
        }
        if (Request::verifyGPCDataInt('activate') === 1) {
            $overlayHelper = new Overlay($this->db);
            $overlayHelper->loadOverlaysFromTemplateFolder($this->currentTemplateDir);
        }
        $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()', ReturnType::DEFAULT);
    }

    /**
     * @param array  $tplConfXML
     * @param string $value
     * @param string $name
     * @return string
     */
    private function handleUpload(array $tplConfXML, string $value, string $name): string
    {
        if (empty($_FILES[$value]['name']) || $_FILES[$value]['error'] !== \UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('No file provided or upload error');
        }
        $file  = $_FILES[$value];
        $value = \basename($_FILES[$value]['name']);
        foreach ($tplConfXML as $section) {
            if (!isset($section->settings)) {
                continue;
            }
            foreach ($section->settings as $setting) {
                if (!isset($setting->key, $setting->rawAttributes['target']) || $setting->key !== $name) {
                    continue;
                }
                $templatePath = \PFAD_TEMPLATES . $this->currentTemplateDir . '/' . $setting->rawAttributes['target'];
                $base         = \PFAD_ROOT . $templatePath;
                // optional target file name + extension
                if (isset($setting->rawAttributes['targetFileName'])) {
                    $value = $setting->rawAttributes['targetFileName'];
                }
                $targetFile = $base . $value;
                if (!\is_writable($base)) {
                    $this->alertService->addAlert(
                        Alert::TYPE_ERROR,
                        \sprintf(__('errorFileUpload'), $templatePath),
                        'errorFileUpload',
                        ['saveInSession' => true]
                    );
                } elseif (!\move_uploaded_file($file['tmp_name'], $targetFile)) {
                    $this->alertService->addAlert(
                        Alert::TYPE_ERROR,
                        __('errorFileUploadGeneral'),
                        'errorFileUploadGeneral',
                        ['saveInSession' => true]
                    );
                }

                return $value;
            }
        }

        return $value;
    }

    private function displayOverview(): void
    {
        $lstng = new Listing($this->db, new TemplateValidator($this->db));
        $this->smarty->assign('listingItems', $lstng->getAll())
            ->assign('shopVersion', Version::parse(\APPLICATION_VERSION))
            ->display('shoptemplate.tpl');
    }

    /**
     * @return string|null
     */
    private function getPreviousTemplate(): ?string
    {
        return $this->db->select('ttemplate', 'eTyp', 'standard')->cTemplate ?? null;
    }

    private function switch(): void
    {
        if (($bootstrapper = BootChecker::bootstrap($this->getPreviousTemplate())) !== null) {
            $bootstrapper->disabled();
        }
        if ($this->setActiveTemplate($this->currentTemplateDir)) {
            if (($bootstrapper = BootChecker::bootstrap($this->currentTemplateDir)) !== null) {
                $bootstrapper->enabled();
            }
            $this->alertService->addAlert(Alert::TYPE_SUCCESS, __('successTemplateSave'), 'successTemplateSave');
        } else {
            $this->alertService->addAlert(Alert::TYPE_ERROR, __('errorTemplateSave'), 'errorTemplateSave');
        }
        $this->db->query('UPDATE tglobals SET dLetzteAenderung = NOW()', ReturnType::DEFAULT);
        $this->cache->flushTags([\CACHING_GROUP_LICENSES]);
    }

    private function displayTemplateSettings(): void
    {
        $reader = new XMLReader();
        $tplXML = $reader->getXML($this->currentTemplateDir);
        if ($tplXML === null) {
            throw new InvalidArgumentException('Cannot display template settings');
        }
        $service      = Shop::Container()->getTemplateService();
        $current      = $service->loadFull(['cTemplate' => $this->currentTemplateDir]);
        $parentFolder = null;
        Shop::Container()->getGetText()->loadTemplateLocale('base', $current);
        if (!empty($tplXML->Parent)) {
            $parentFolder = (string)$tplXML->Parent;
        }
        $templateConfig = $this->config->getConfigXML($reader, $parentFolder);
        $preview        = $this->getPreview($templateConfig);

        $this->smarty->assign('template', $current)
            ->assign('themePreviews', (\count($preview) > 0) ? $preview : null)
            ->assign('themePreviewsJSON', \json_encode($preview))
            ->assign('templateConfig', $templateConfig)
            ->display('shoptemplate.tpl');
    }

    /**
     * @param array $tplConfXML
     * @return array
     */
    private function getPreview(array $tplConfXML): array
    {
        $shopURL = Shop::getURL() . '/';
        $preview = [];
        $tplBase = \PFAD_ROOT . \PFAD_TEMPLATES;
        $tplPath = $tplBase . $this->currentTemplateDir . '/';
        foreach ($tplConfXML as $_conf) {
            // iterate over each "Setting" in this "Section"
            foreach ($_conf->settings as $_setting) {
                if ($_setting->cType === 'upload'
                    && isset($_setting->rawAttributes['target'], $_setting->rawAttributes['targetFileName'])
                    && !\file_exists($tplPath . $_setting->rawAttributes['target']
                        . $_setting->rawAttributes['targetFileName'])
                ) {
                    $_setting->value = null;
                }
            }
            if (isset($_conf->key, $_conf->settings)
                && $_conf->key === 'theme'
                && \count($_conf->settings) > 0
            ) {
                foreach ($_conf->settings as $_themeConf) {
                    if (isset($_themeConf->key, $_themeConf->options)
                        && $_themeConf->key === 'theme_default'
                        && \count($_themeConf->options) > 0
                    ) {
                        foreach ($_themeConf->options as $_theme) {
                            $previewImage = isset($_theme->dir)
                                ? $tplBase . $_theme->dir . '/themes/' .
                                $_theme->value . '/preview.png'
                                : $tplBase . $this->currentTemplateDir . '/themes/' . $_theme->value . '/preview.png';
                            if (\file_exists($previewImage)) {
                                $base                    = $shopURL . \PFAD_TEMPLATES;
                                $preview[$_theme->value] = isset($_theme->dir)
                                    ? $base . $_theme->dir . '/themes/' . $_theme->value . '/preview.png'
                                    : $base . $this->currentTemplateDir . '/themes/' . $_theme->value . '/preview.png';
                            }
                        }
                        break;
                    }
                }
            }
        }

        return $preview;
    }
}
