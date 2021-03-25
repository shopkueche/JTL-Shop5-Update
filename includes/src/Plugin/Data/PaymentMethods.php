<?php declare(strict_types=1);

namespace JTL\Plugin\Data;

use JTL\Plugin\PluginInterface;
use stdClass;
use function Functional\first;
use function Functional\reindex;

/**
 * Class PaymentMethods
 * @package JTL\Plugin\Data
 */
class PaymentMethods
{
    /**
     * @var array
     */
    private $methods = [];

    /**
     * @var array
     */
    private $classes = [];

    /**
     * @param array $data
     * @param PluginInterface $plugin
     * @return PaymentMethods
     */
    public function load(array $data, PluginInterface $plugin): self
    {
        $path          = $plugin->getPaths()->getVersionedPath();
        $this->methods = [];
        foreach ($data as $method) {
            $method->kZahlungsart           = (int)$method->kZahlungsart;
            $method->nSort                  = (int)$method->nSort;
            $method->nMailSenden            = (int)$method->nMailSenden;
            $method->nActive                = (int)$method->nActive;
            $method->nCURL                  = (int)$method->nCURL;
            $method->nSOAP                  = (int)$method->nSOAP;
            $method->nSOCKETS               = (int)$method->nSOCKETS;
            $method->nNutzbar               = (int)$method->nNutzbar;
            $method->kPlugin                = (int)$method->kPlugin;
            $method->cZusatzschrittTemplate = \mb_strlen($method->cZusatzschrittTemplate)
                ? $path . \PFAD_PLUGIN_PAYMENTMETHOD . $method->cZusatzschrittTemplate
                : '';
            $method->cTemplateFileURL       = \mb_strlen($method->cPluginTemplate)
                ? $path . \PFAD_PLUGIN_PAYMENTMETHOD . $method->cPluginTemplate
                : '';
            foreach ($method->oZahlungsmethodeEinstellung_arr as $conf) {
                $conf->kPluginEinstellungenConf = (int)$conf->kPluginEinstellungenConf;
                $conf->kPlugin                  = (int)$conf->kPlugin;
                $conf->kPluginAdminMenu         = (int)$conf->kPluginAdminMenu;
                $conf->nSort                    = (int)$conf->nSort;
            }
            foreach ($method->oZahlungsmethodeSprache_arr as $loc) {
                $loc->kZahlungsart = (int)$loc->kZahlungsart;
            }
            $class                           = new stdClass();
            $class->cModulId                 = $method->cModulId;
            $class->kPlugin                  = $method->kPlugin;
            $class->cClassPfad               = $method->cClassPfad;
            $class->cClassName               = $method->cClassName;
            $class->cTemplatePfad            = $method->cTemplatePfad;
            $class->cZusatzschrittTemplate   = $method->cZusatzschrittTemplate;
            $this->classes[$class->cModulId] = $class;

            $this->methods[] = new PaymentMethod($method, $plugin);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getMethodsAssoc(): array
    {
        return reindex($this->methods, static function (PaymentMethod $e) {
            return $e->getModuleID();
        });
    }

    /**
     * @return PaymentMethod[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @param string $id
     * @return stdClass|null
     */
    public function getMethodByID(string $id): ?PaymentMethod
    {
        return first($this->methods, static function (PaymentMethod $method) use ($id) {
            return $method->getModuleID() === $id;
        });
    }

    /**
     * @param PaymentMethod[] $methods
     */
    public function setMethods(array $methods): void
    {
        $this->methods = $methods;
    }

    /**
     * @return array
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * @param array $classes
     */
    public function setClasses(array $classes): void
    {
        $this->classes = $classes;
    }
}
