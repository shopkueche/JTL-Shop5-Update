<?php declare(strict_types=1);

namespace JTL\Mail\Hydrator;

use JTL\Customer\Customer;
use JTL\Customer\CustomerGroup;
use JTL\DB\DbInterface;
use JTL\Firma;
use JTL\Helpers\GeneralObject;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\JTLSmarty;
use stdClass;

/**
 * Class DefaultsHydrator
 * @package JTL\Mail\Hydrator
 */
class DefaultsHydrator implements HydratorInterface
{
    /**
     * @var JTLSmarty
     */
    protected $smarty;

    /**
     * @var DbInterface
     */
    protected $db;

    /**
     * @var Shopsetting
     */
    protected $settings;

    /**
     * DefaultsHydrator constructor.
     * @param JTLSmarty   $smarty
     * @param DbInterface $db
     * @param Shopsetting $settings
     */
    public function __construct(JTLSmarty $smarty, DbInterface $db, Shopsetting $settings)
    {
        $this->smarty   = $smarty;
        $this->db       = $db;
        $this->settings = $settings;
    }

    /**
     * @inheritdoc
     */
    public function add(string $variable, $content): void
    {
        $this->smarty->assign($variable, $content);
    }

    /**
     * @inheritdoc
     */
    public function hydrate(?object $data, object $language): void
    {
        $data         = $data ?? new stdClass();
        $data->tkunde = $data->tkunde ?? new Customer();

        if (!isset($data->tkunde->kKundengruppe) || !$data->tkunde->kKundengruppe) {
            $data->tkunde->kKundengruppe = CustomerGroup::getDefaultGroupID();
        }
        $data->tfirma        = new Firma();
        $data->tkundengruppe = new CustomerGroup($data->tkunde->kKundengruppe);
        $customer            = $data->tkunde instanceof Customer
            ? $data->tkunde->localize($language)
            : $this->localizeCustomer($language, $data->tkunde);

        $this->smarty->assign('int_lang', $language)
            ->assign('Firma', $data->tfirma)
            ->assign('Kunde', $customer)
            ->assign('Kundengruppe', $data->tkundengruppe)
            ->assign('NettoPreise', $data->tkundengruppe->isMerchant())
            ->assign('ShopLogoURL', Shop::getLogo(true))
            ->assign('ShopURL', Shop::getURL())
            ->assign('Einstellungen', $this->settings)
            ->assign('IP', Text::htmlentities(Text::filterXSS(Request::getRealIP())));
    }

    /**
     * @inheritdoc
     */
    public function getSmarty(): JTLSmarty
    {
        return $this->smarty;
    }

    /**
     * @inheritdoc
     */
    public function setSmarty(JTLSmarty $smarty): void
    {
        $this->smarty = $smarty;
    }

    /**
     * @inheritdoc
     */
    public function getDB(): DbInterface
    {
        return $this->db;
    }

    /**
     * @inheritdoc
     */
    public function setDB(DbInterface $db): void
    {
        $this->db = $db;
    }

    /**
     * @inheritdoc
     */
    public function getSettings(): Shopsetting
    {
        return $this->settings;
    }

    /**
     * @inheritdoc
     */
    public function setSettings(Shopsetting $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * @param object            $lang
     * @param stdClass|Customer $customer
     * @return mixed
     */
    private function localizeCustomer($lang, $customer)
    {
        $language = Shop::Lang();
        if ($language->gibISO() !== $lang->cISO) {
            $language->setzeSprache($lang->cISO);
            $language->autoload();
        }
        if (isset($customer->cAnrede)) {
            if ($customer->cAnrede === 'w') {
                $customer->cAnredeLocalized = Shop::Lang()->get('salutationW');
            } elseif ($customer->cAnrede === 'm') {
                $customer->cAnredeLocalized = Shop::Lang()->get('salutationM');
            } else {
                $customer->cAnredeLocalized = Shop::Lang()->get('salutationGeneral');
            }
        }
        $customer = GeneralObject::deepCopy($customer);
        if (isset($customer->cLand)) {
            $iso = $customer->cLand;
            $col = 'cDeutsch';
            if (\mb_convert_case($lang->cISO, \MB_CASE_LOWER) !== 'ger') {
                $col = 'cEnglisch';
            }
            $country = $this->db->select(
                'tland',
                'cISO',
                $customer->cLand,
                null,
                null,
                null,
                null,
                false,
                $col . ' AS cName, cISO'
            );
            if (isset($country->cName)) {
                $customer->cLand = $country->cName;
            }
        }
        if (isset($_SESSION['Kunde'], $iso)) {
            $_SESSION['Kunde']->cLand = $iso;
        }

        return $customer;
    }
}
