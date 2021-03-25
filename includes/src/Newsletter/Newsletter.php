<?php declare(strict_types=1);

namespace JTL\Newsletter;

use Exception;
use JTL\Campaign;
use JTL\Catalog\Category\Kategorie;
use JTL\Catalog\Hersteller;
use JTL\Catalog\Product\Artikel;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\ContextType;
use JTL\Smarty\JTLSmarty;
use JTL\Smarty\SmartyResourceNiceDB;
use stdClass;

/**
 * Class Newsletter
 * @package JTL\Newsletter
 */
class Newsletter
{
    /**
     * @var DbInterface
     */
    private $db;

    /**
     * @var array
     */
    private $config;

    /**
     * @var JTLSmarty
     */
    private $smarty;

    /**
     * Newsletter constructor.
     * @param DbInterface $db
     * @param array       $config
     */
    public function __construct(DbInterface $db, array $config)
    {
        $this->db     = $db;
        $this->config = $config;
    }

    /**
     * @return JTLSmarty
     * @throws \SmartyException
     */
    public function initSmarty(): JTLSmarty
    {
        $this->smarty = new JTLSmarty(true, ContextType::NEWSLETTER);
        $this->smarty->setCaching(0)
            ->setDebugging(0)
            ->setCompileDir(\PFAD_ROOT . \PFAD_COMPILEDIR)
            ->registerResource('db', new SmartyResourceNiceDB($this->db, ContextType::NEWSLETTER))
            ->assign('Firma', $this->db->query(
                'SELECT *  FROM tfirma',
                ReturnType::SINGLE_OBJECT
            ))
            ->assign('URL_SHOP', Shop::getURL())
            ->assign('Einstellungen', $this->config);
        if (\NEWSLETTER_USE_SECURITY) {
            $this->smarty->activateBackendSecurityMode();
        }

        return $this->smarty;
    }

    /**
     * @param object          $newsletter
     * @param array           $products
     * @param array           $manufacturers
     * @param array           $categories
     * @param string          $campaign
     * @param stdClass|string $recipient
     * @param stdClass|string $customer
     * @return string
     */
    public function getStaticHtml(
        $newsletter,
        $products = [],
        $manufacturers = [],
        $categories = [],
        $campaign = '',
        $recipient = '',
        $customer = ''
    ): string {
        $this->smarty->assign('Emailempfaenger', $recipient)
            ->assign('Kunde', $customer)
            ->assign('Artikelliste', $products)
            ->assign('Herstellerliste', $manufacturers)
            ->assign('Kategorieliste', $categories)
            ->assign('Kampagne', $campaign);

        $cTyp = 'VL';
        $nKey = $newsletter->kNewsletterVorlage ?? null;
        if ($newsletter->kNewsletter > 0) {
            $cTyp = 'NL';
            $nKey = $newsletter->kNewsletter;
        }

        return $this->smarty->fetch('db:' . $cTyp . '_' . $nKey . '_html');
    }

    /**
     * @param int $newsletterID
     * @return stdClass
     */
    public function getRecipients(int $newsletterID): stdClass
    {
        if ($newsletterID <= 0) {
            return new stdClass();
        }
        $data      = $this->db->select('tnewsletter', 'kNewsletter', $newsletterID);
        $tmpGroups = \explode(';', $data->cKundengruppe);
        $cSQL      = '';
        if (\count($tmpGroups) > 0) {
            $groupIDs = \array_map('\intval', $tmpGroups);
            $noGroup  = \in_array(0, $groupIDs, true);
            if ($noGroup === false || \count($groupIDs) > 1) {
                $cSQL = 'AND ((tkunde.kKundengruppe IN (' . \implode(',', $groupIDs) . ')';
                if ($noGroup === true) {
                    $cSQL .= ' OR tkunde.kKundengruppe IS NULL)';
                }
                $cSQL .= ')';
            } elseif ($noGroup === true) {
                $cSQL .= ' AND tkunde.kKundengruppe IS NULL';
            }
        }

        $recipients = $this->db->query(
            'SELECT COUNT(*) AS nAnzahl
            FROM tnewsletterempfaenger
            LEFT JOIN tsprache
                ON tsprache.kSprache = tnewsletterempfaenger.kSprache
            LEFT JOIN tkunde
                ON tkunde.kKunde = tnewsletterempfaenger.kKunde
            WHERE tnewsletterempfaenger.kSprache = ' . (int)$data->kSprache . '
                AND tnewsletterempfaenger.nAktiv = 1 ' . $cSQL,
            ReturnType::SINGLE_OBJECT
        );
        if ($this->db->getErrorCode() !== 0) {
            $recipients = new stdClass();
        }

        $recipients->cKundengruppe_arr = $tmpGroups;

        return $recipients;
    }

    /**
     * @param string $dbField
     * @param string $email
     * @return string
     */
    public function createCode($dbField, $email): string
    {
        $code = \md5($email . \time() . \random_int(123, 456));
        while (!$this->isCodeUnique($dbField, $code)) {
            $code = \md5($email . \time() . \random_int(123, 456));
        }

        return $code;
    }

    /**
     * @param string     $dbField
     * @param string|int $code
     * @return bool
     */
    public function isCodeUnique($dbField, $code): bool
    {
        $res = $this->db->select('tnewsletterempfaenger', $dbField, $code);

        return !(isset($res->kNewsletterEmpfaenger) && $res->kNewsletterEmpfaenger > 0);
    }

    /**
     * @param object $template
     * @return string|bool
     */
    public function getPreview($template)
    {
        $this->initSmarty();
        $productIDs             = $this->getKeys($template->cArtikel, true);
        $manufacturerIDs        = $this->getKeys($template->cHersteller);
        $categoryIDs            = $this->getKeys($template->cKategorie);
        $campaign               = new Campaign((int)$template->kKampagne);
        $products               = $this->getProducts($productIDs, $campaign);
        $manufacturers          = $this->getManufacturers($manufacturerIDs, $campaign);
        $categories             = $this->getCategories($categoryIDs, $campaign);
        $customer               = new stdClass();
        $customer->cAnrede      = 'm';
        $customer->cVorname     = 'Max';
        $customer->cNachname    = 'Mustermann';
        $recipient              = new stdClass();
        $recipient->cEmail      = $this->config['newsletter']['newsletter_emailtest'];
        $recipient->cLoeschCode = 'dc1338521613c3cfeb1988261029fe3058';
        $recipient->cLoeschURL  = Shop::getURL() . '/?oc=' . $recipient->cLoeschCode;

        $this->smarty->assign('NewsletterEmpfaenger', $recipient)
            ->assign('Emailempfaenger', $recipient)
            ->assign('oNewsletterVorlage', $template)
            ->assign('Kunde', $customer)
            ->assign('Artikelliste', $products)
            ->assign('NettoPreise', 0)
            ->assign('Herstellerliste', $manufacturers)
            ->assign('Kategorieliste', $categories)
            ->assign('Kampagne', $campaign);

        try {
            $template->cInhaltHTML = $this->smarty->fetch('db:VL_' . $template->kNewsletterVorlage . '_html');
            $template->cInhaltText = $this->smarty->fetch('db:VL_' . $template->kNewsletterVorlage . '_text');
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * @param object   $newsletter
     * @param stdClass $recipients
     * @param array    $products
     * @param array    $manufacturers
     * @param array    $categories
     * @param string   $campaign
     * @param string   $oKunde
     * @return string|bool
     */
    public function send(
        $newsletter,
        $recipients,
        $products = [],
        $manufacturers = [],
        $categories = [],
        $campaign = '',
        $oKunde = ''
    ) {
        $this->smarty->assign('oNewsletter', $newsletter)
            ->assign('Emailempfaenger', $recipients)
            ->assign('Kunde', $oKunde)
            ->assign('Artikelliste', $products)
            ->assign('Herstellerliste', $manufacturers)
            ->assign('Kategorieliste', $categories)
            ->assign('Kampagne', $campaign)
            ->assign(
                'cNewsletterURL',
                Shop::getURL() .
                '/newsletter.php?show=' .
                ($newsletter->kNewsletter ?? '0')
            );
        $net      = 0;
        $bodyHtml = '';
        if (isset($oKunde->kKunde) && $oKunde->kKunde > 0) {
            $oKundengruppe = $this->db->query(
                'SELECT tkundengruppe.nNettoPreise
                FROM tkunde
                JOIN tkundengruppe
                    ON tkundengruppe.kKundengruppe = tkunde.kKundengruppe
                WHERE tkunde.kKunde = ' . (int)$oKunde->kKunde,
                ReturnType::SINGLE_OBJECT
            );
            if (isset($oKundengruppe->nNettoPreise)) {
                $net = $oKundengruppe->nNettoPreise;
            }
        }

        $this->smarty->assign('NettoPreise', $net);

        $cPixel = '';
        if (isset($campaign->kKampagne) && $campaign->kKampagne > 0) {
            $cPixel = '<br /><img src="' . Shop::getURL() . '/' . \PFAD_INCLUDES .
                'newslettertracker.php?kK=' . $campaign->kKampagne .
                '&kN=' . ($newsletter->kNewsletter ?? 0) . '&kNE=' .
                ($recipients->kNewsletterEmpfaenger ?? 0) . '" alt="Newsletter" />';
        }

        $cTyp = 'VL';
        $nKey = $newsletter->kNewsletterVorlage ?? 0;
        if (isset($newsletter->kNewsletter) && $newsletter->kNewsletter > 0) {
            $cTyp = 'NL';
            $nKey = $newsletter->kNewsletter;
        }
        if ($newsletter->cArt === 'text/html' || $newsletter->cArt === 'html') {
            try {
                $bodyHtml = $this->smarty->fetch('db:' . $cTyp . '_' . $nKey . '_html') . $cPixel;
            } catch (Exception $e) {
                Shop::Smarty()->assign('oSmartyError', $e->getMessage());

                return $e->getMessage();
            }
        }
        try {
            $bodyText = $this->smarty->fetch('db:' . $cTyp . '_' . $nKey . '_text');
        } catch (Exception $e) {
            Shop::Smarty()->assign('oSmartyError', $e->getMessage());

            return $e->getMessage();
        }
        $toName = ($recipients->cVorname ?? '') . ' ' . ($recipients->cNachname ?? '');
        if (isset($oKunde->kKunde) && $oKunde->kKunde > 0) {
            $toName = ($oKunde->cVorname ?? '') . ' ' . ($oKunde->cNachname ?? '');
        }
        $mailer                 = Shop::Container()->get(Mailer::class);
        $config                 = [
            'email_methode'               => $this->config['newsletter']['newsletter_emailmethode'],
            'email_sendmail_pfad'         => $this->config['newsletter']['newsletter_sendmailpfad'],
            'email_smtp_hostname'         => $this->config['newsletter']['newsletter_smtp_host'],
            'email_smtp_port'             => $this->config['newsletter']['newsletter_smtp_port'],
            'email_smtp_auth'             => $this->config['newsletter']['newsletter_smtp_authnutzen'],
            'email_smtp_user'             => $this->config['newsletter']['newsletter_smtp_benutzer'],
            'email_smtp_pass'             => $this->config['newsletter']['newsletter_smtp_pass'],
            'email_smtp_verschluesselung' => $this->config['newsletter']['newsletter_smtp_verschluesselung']
        ];
        $mailerConfig['emails'] = $config;
        $mailer->setConfig($mailerConfig);
        $mailNL = (new Mail())
            ->setToMail($recipients->cEmail)
            ->setToName($toName)
            ->setFromMail($this->config['newsletter']['newsletter_emailadresse'])
            ->setFromName($this->config['newsletter']['newsletter_emailabsender'])
            ->setReplyToMail($this->config['newsletter']['newsletter_emailadresse'])
            ->setReplyToName($this->config['newsletter']['newsletter_emailabsender'])
            ->setSubject($newsletter->cBetreff)
            ->setBodyText($bodyText)
            ->setBodyHTML($bodyHtml)
            ->setLanguage(Shop::Lang()->getLanguageByID((int)$newsletter->kSprache));
        $mailer->send($mailNL);

        return true;
    }

    /**
     * Braucht ein String von Keys oder Nummern und gibt ein Array mit kKeys zurueck
     * Der String muss ';' separiert sein z.b. '1;2;3'
     *
     * @param string $keyString
     * @param bool   $asProductNo
     * @return array
     */
    public function getKeys(string $keyString, bool $asProductNo = false): array
    {
        $res  = [];
        $keys = \explode(';', $keyString);
        if (!\is_array($keys) || \count($keys) === 0) {
            return $res;
        }
        $res = \array_filter($keys, static function ($e) {
            return \mb_strlen($e) > 0;
        });
        if ($asProductNo) {
            $res = \array_map(static function ($e) {
                return "'" . $e . "'";
            }, $res);
            if (\count($res) > 0) {
                $artNoData = $this->db->query(
                    'SELECT kArtikel
                FROM tartikel
                WHERE cArtNr IN (' . \implode(',', $res) . ')
                    AND kEigenschaftKombi = 0',
                    ReturnType::ARRAY_OF_OBJECTS
                );
                $res       = \array_map(static function ($e) {
                    return $e->kArtikel;
                }, $artNoData);
            }
        } else {
            $res = \array_map('\intval', $res);
        }

        return $res;
    }

    /**
     * Benoetigt ein Array von kArtikel und gibt ein Array mit Artikelobjekten zurueck
     *
     * @param array         $productIDs
     * @param string|object $campaign
     * @param int           $customerGroupID
     * @param int           $langID
     * @return Artikel[]
     */
    public function getProducts($productIDs, $campaign = '', int $customerGroupID = 0, int $langID = 0): array
    {
        if (!\is_array($productIDs) || \count($productIDs) === 0) {
            return [];
        }
        $products       = [];
        $shopURL        = Shop::getURL() . '/';
        $imageBaseURL   = Shop::getImageBaseURL();
        $defaultOptions = Artikel::getDefaultOptions();
        foreach ($productIDs as $id) {
            $id = (int)$id;
            if ($id <= 0) {
                continue;
            }
            Frontend::getCustomerGroup()->setMayViewPrices(1);
            $product = new Artikel();
            $product->fuelleArtikel($id, $defaultOptions, $customerGroupID, $langID);
            if (!($product->kArtikel > 0)) {
                Shop::Container()->getLogService()->notice(
                    'Newsletter Cron konnte den Artikel ' . $id . ' für Kundengruppe ' .
                    $customerGroupID . ' und Sprache ' . $langID . ' nicht laden (Sichtbarkeit?)'
                );
                continue;
            }
            $product->cURL = $shopURL . $product->cURL;
            if (isset($campaign->cParameter) && \mb_strlen($campaign->cParameter) > 0) {
                $product->cURL .= (\mb_strpos($product->cURL, '.php') !== false ? '&' : '?') .
                    $campaign->cParameter . '=' . $campaign->cWert;
            }
            foreach ($product->Bilder as $image) {
                $image->cPfadMini   = $imageBaseURL . $image->cPfadMini;
                $image->cPfadKlein  = $imageBaseURL . $image->cPfadKlein;
                $image->cPfadNormal = $imageBaseURL . $image->cPfadNormal;
                $image->cPfadGross  = $imageBaseURL . $image->cPfadGross;
            }
            $product->cVorschaubild = $imageBaseURL . $product->cVorschaubild;

            $products[] = $product;
        }

        return $products;
    }

    /**
     * Benoetigt ein Array von kHersteller und gibt ein Array mit Herstellerobjekten zurueck
     *
     * @param array      $manufacturerIDs
     * @param int|object $campaign
     * @param int|object $langID
     * @return array
     */
    public function getManufacturers($manufacturerIDs, $campaign = 0, int $langID = 0): array
    {
        if (!\is_array($manufacturerIDs) || \count($manufacturerIDs) === 0) {
            return [];
        }
        $manufacturers = [];
        $shopURL       = Shop::getURL() . '/';
        $imageBaseURL  = Shop::getImageBaseURL();
        foreach ($manufacturerIDs as $id) {
            $id = (int)$id;
            if ($id <= 0) {
                continue;
            }
            $manufacturer = new Hersteller($id, $langID);
            if (\mb_strpos($manufacturer->cURL, $shopURL) === false) {
                $manufacturer->cURL = $manufacturer->cURL = $shopURL . $manufacturer->cURL;
            }
            if (isset($campaign->cParameter) && \mb_strlen($campaign->cParameter) > 0) {
                $sep                 = \mb_strpos($manufacturer->cURL, '.php') !== false ? '&' : '?';
                $manufacturer->cURL .= $sep . $campaign->cParameter . '=' . $campaign->cWert;
            }
            $manufacturer->cBildpfadKlein  = $imageBaseURL . $manufacturer->cBildpfadKlein;
            $manufacturer->cBildpfadNormal = $imageBaseURL . $manufacturer->cBildpfadNormal;

            $manufacturers[] = $manufacturer;
        }

        return $manufacturers;
    }

    /**
     * Benoetigt ein Array von kKategorie und gibt ein Array mit Kategorieobjekten zurueck
     *
     * @param array      $categoryIDs
     * @param int|object $campaign
     * @return array
     */
    public function getCategories($categoryIDs, $campaign = 0): array
    {
        if (!\is_array($categoryIDs) || \count($categoryIDs) === 0) {
            return [];
        }
        $categories = [];
        $shopURL    = Shop::getURL() . '/';
        foreach ($categoryIDs as $id) {
            $id = (int)$id;
            if ($id <= 0) {
                continue;
            }
            $category = new Kategorie($id);
            if (\mb_strpos($category->cURL, $shopURL) === false) {
                $category->cURL = $shopURL . $category->cURL;
            }
            if (isset($campaign->cParameter) && \mb_strlen($campaign->cParameter) > 0) {
                $sep = '?';
                if (\strpos($category->cURL, '.php') !== false) {
                    $sep = '&';
                }
                $category->cURL .= $sep . $campaign->cParameter . '=' . $campaign->cWert;
            }
            $categories[] = $category;
        }

        return $categories;
    }
}
