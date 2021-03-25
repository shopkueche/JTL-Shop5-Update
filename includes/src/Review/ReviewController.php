<?php declare(strict_types=1);

namespace JTL\Review;

use Exception;
use JTL\Alert\Alert;
use JTL\Cache\JTLCacheInterface;
use JTL\Catalog\Product\Artikel;
use JTL\Customer\Customer;
use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Helpers\Form;
use JTL\Helpers\Request;
use JTL\Helpers\Text;
use JTL\Services\JTL\AlertServiceInterface;
use JTL\Session\Frontend;
use JTL\Shop;
use JTL\Smarty\JTLSmarty;

/**
 * Class ReviewController
 * @package JTL\Review
 */
class ReviewController extends BaseController
{
    /**
     * ReviewController constructor.
     * @param DbInterface                $db
     * @param JTLCacheInterface          $cache
     * @param AlertServiceInterface|null $alertService
     * @param JTLSmarty|null             $smarty
     */
    public function __construct(
        DbInterface $db,
        JTLCacheInterface $cache,
        ?AlertServiceInterface $alertService = null,
        ?JTLSmarty $smarty = null
    ) {
        $this->db           = $db;
        $this->smarty       = $smarty;
        $this->config       = Shop::getSettings([\CONF_GLOBAL, \CONF_RSS, \CONF_BEWERTUNG]);
        $this->cache        = $cache;
        $this->alertService = $alertService;
    }

    /**
     * @return bool|void
     */
    public function handleRequest()
    {
        if (!Form::validateToken()) {
            Shop::Container()->getAlertService()->addAlert(
                Alert::TYPE_WARNING,
                Shop::Lang()->get('invalidToken'),
                'invalidToken',
                [
                    'saveInSession' => true,
                ]
            );

            return false;
        }
        $this->checkRedirect();
        $params   = Shop::getParameters();
        $customer = Frontend::getCustomer();
        if (Request::postInt('bfh') === 1) {
            $message = $this->save(
                $params['kArtikel'],
                $customer->getID(),
                Shop::getLanguageID(),
                Request::verifyGPDataString('cTitel'),
                Request::verifyGPDataString('cText'),
                $params['nSterne']
            );
            \header('Location: ' . $message . '#alert-list', true, 303);
            exit;
        }
        if (Request::postInt('bhjn') === 1) {
            $this->updateWasHelpful(
                $params['kArtikel'],
                $customer->getID(),
                Request::verifyGPCDataInt('btgseite'),
                Request::verifyGPCDataInt('btgsterne')
            );
        }
        if (Request::verifyGPCDataInt('bfa') === 1) {
            return $this->reviewPreCheck($customer, $params);
        }
    }

    /**
     *
     */
    private function checkRedirect(): void
    {
        if (!isset($_POST['bfh']) && !isset($_POST['bhjn']) && Request::verifyGPCDataInt('bfa') !== 1) {
            \header('Location: ' . Shop::getURL() . '/', true, 303);
            exit;
        }
    }

    /**
     * @param int $productID
     * @return string
     */
    private function getProductURL(int $productID): string
    {
        $product = new Artikel();
        $product->fuelleArtikel($productID, Artikel::getDefaultOptions());
        if (!empty($product->cURLFull)) {
            return \mb_strpos($product->cURLFull, '?') === false
                ? $product->cURLFull . '?'
                : $product->cURLFull . '&';
        }

        return Shop::getURL() . '/?a=' . $productID . '&';
    }

    /**
     * Fügt für einen bestimmten Artikel, in einer bestimmten Sprache eine Bewertung hinzu.
     *
     * @param int    $productID
     * @param int    $customerID
     * @param int    $langID
     * @param string $title
     * @param string $text
     * @param int    $stars
     * @return string
     */
    private function save(int $productID, int $customerID, int $langID, $title, $text, int $stars): string
    {
        $url = $this->getProductURL($productID);
        if ($stars < 1 || $stars > 5) {
            return $url . 'bewertung_anzeigen=1&cFehler=f05';
        }
        if ($customerID <= 0 || $this->config['bewertung']['bewertung_anzeigen'] !== 'Y') {
            return $url . 'bewertung_anzeigen=1&cFehler=f04';
        }
        $title = Text::htmlentities(Text::filterXSS($title));
        $text  = Text::htmlentities(Text::filterXSS($text));

        if ($productID <= 0 || $langID <= 0 || $title === '' || $text === '') {
            return $url . 'bewertung_anzeigen=1&cFehler=f01';
        }
        if ($this->checkProductWasPurchased($productID, Frontend::getCustomer()) === false) {
            return $url . 'bewertung_anzeigen=1&cFehler=f03';
        }
        $review = ReviewModel::loadByAttributes(
            ['productID' => $productID, 'customerID' => $customerID],
            $this->db,
            ReviewHelpfulModel::ON_NOTEXISTS_NEW
        );
        /** @var ReviewModel $review */
        $review->productID  = $productID;
        $review->customerID = $customerID;
        $review->languageID = $langID;
        $review->name       = $_SESSION['Kunde']->cVorname . ' ' . \mb_substr($_SESSION['Kunde']->cNachname, 0, 1);
        $review->title      = $title;
        $review->content    = \strip_tags($text);
        $review->helpful    = 0;
        $review->notHelpful = 0;
        $review->stars      = $stars;
        $review->active     = (int)($this->config['bewertung']['bewertung_freischalten'] === 'N');
        $review->date       = \date('Y-m-d H:i:s');

        \executeHook(\HOOK_BEWERTUNG_INC_SPEICHERBEWERTUNG, ['rating' => &$review]);

        $review->save();
        if ($this->config['bewertung']['bewertung_freischalten'] === 'N') {
            $this->updateAverage($productID, $this->config['bewertung']['bewertung_freischalten']);
            $reward = $this->addReward($review);
            $this->cache->flushTags([\CACHING_GROUP_ARTICLE . '_' . $productID]);

            return $url . (($reward > 0)
                    ? 'bewertung_anzeigen=1&fB=' . $reward . '&cHinweis=h04'
                    : 'bewertung_anzeigen=1&cHinweis=h01');
        }

        return $url . 'bewertung_anzeigen=1&cHinweis=h05';
    }

    /**
     * @param Customer $customer
     * @param array    $params
     * @return bool|void
     */
    private function reviewPreCheck(Customer $customer, array $params): bool
    {
        $reviewAllowed = true;
        if (!$customer->isLoggedIn()) {
            $helper = Shop::Container()->getLinkService();
            \header(
                'Location: ' . $helper->getStaticRoute('jtl.php') .
                '?a=' . Request::verifyGPCDataInt('a') .
                '&bfa=1&r=' . \R_LOGIN_BEWERTUNG,
                true,
                303
            );
            exit();
        }
        $product = new Artikel();
        $product->fuelleArtikel($params['kArtikel'], Artikel::getDefaultOptions());
        if (!$product->kArtikel) {
            \header('Location: ' . Shop::getURL() . '/', true, 303);
            exit;
        }
        if ($product->Bewertungen === null) {
            $product->holeBewertung(
                $this->config['bewertung']['bewertung_anzahlseite'],
                0,
                -1,
                $this->config['bewertung']['bewertung_freischalten'],
                $params['nSortierung']
            );
            $product->holehilfreichsteBewertung();
        }
        if ($this->checkProductWasPurchased($product->kArtikel, Frontend::getCustomer()) === false) {
            $this->alertService->addAlert(
                Alert::TYPE_DANGER,
                Shop::Lang()->get('productNotBuyed', 'product rating'),
                'productNotBuyed',
                ['showInAlertListTemplate' => false]
            );
            $reviewAllowed = false;
        }

        $this->smarty->assign('Artikel', $product)
            ->assign('ratingAllowed', $reviewAllowed)
            ->assign(
                'oBewertung',
                ReviewModel::loadByAttributes(
                    ['productID' => $product->kArtikel, 'customerID' => $customer->getID()],
                    $this->db,
                    ReviewHelpfulModel::ON_NOTEXISTS_NEW
                )
            );

        return true;
    }

    /**
     * @param int      $productID
     * @param Customer $customer
     * @return bool
     */
    private function checkProductWasPurchased(int $productID, Customer $customer): bool
    {
        if ($this->config['bewertung']['bewertung_artikel_gekauft'] !== 'Y') {
            return true;
        }
        $order = $this->db->queryPrepared(
            'SELECT tbestellung.kBestellung
            FROM tbestellung
            LEFT JOIN tartikel 
                ON tartikel.kVaterArtikel = :aid
            JOIN twarenkorb 
                ON twarenkorb.kWarenkorb = tbestellung.kWarenkorb
            JOIN twarenkorbpos 
                ON twarenkorbpos.kWarenkorb = twarenkorb.kWarenkorb
            WHERE tbestellung.kKunde = :cid
                AND (twarenkorbpos.kArtikel = :aid 
                OR twarenkorbpos.kArtikel = tartikel.kArtikel)',
            ['aid' => $productID, 'cid' => $customer->getID()],
            ReturnType::SINGLE_OBJECT
        );

        return isset($order->kBestellung) && $order->kBestellung > 0;
    }

    /**
     * Speichert für eine bestimmte Bewertung und bestimmten Kunden ab, ob sie hilfreich oder nicht hilfreich war.
     *
     * @param int $productID
     * @param int $customerID
     * @param int $page
     * @param int $stars
     */
    private function updateWasHelpful(int $productID, int $customerID, int $page = 1, int $stars = 0): void
    {
        $helpful  = 0;
        $reviewID = 0;
        foreach (\array_keys($_POST) as $key) {
            \preg_match('/^(nichthilfreich_)(\d*)/', $key, $hits);
            if (\count($hits) === 3) {
                $reviewID = (int)$hits[2];
                break;
            }
            \preg_match('/^(hilfreich_)(\d*)/', $key, $hits);
            if (\count($hits) === 3) {
                $reviewID = (int)$hits[2];
                $helpful  = 1;
                break;
            }
        }
        if ($customerID <= 0
            || $reviewID === 0
            || $this->config['bewertung']['bewertung_anzeigen'] !== 'Y'
            || $this->config['bewertung']['bewertung_hilfreich_anzeigen'] !== 'Y'
        ) {
            return;
        }
        try {
            $review = ReviewModel::load(['id' => $reviewID], $this->db, ReviewModel::ON_NOTEXISTS_FAIL);
        } catch (Exception $e) {
            return;
        }
        if ($review->getCustomerID() === $customerID) {
            return;
        }
        $helpfulReview = ReviewHelpfulModel::loadByAttributes(
            ['reviewID' => $reviewID, 'customerID' => $customerID],
            $this->db,
            ReviewHelpfulModel::ON_NOTEXISTS_NEW
        );
        /** @var ReviewHelpfulModel $helpfulReview */
        $baseURL = $this->getProductURL($productID) . 'bewertung_anzeigen=1&btgseite=' . $page . '&btgsterne=' . $stars;
        // Hat der Kunde für diese Bewertung noch keine hilfreich flag gesetzt?
        if ($helpfulReview->getId() === 0) {
            $helpfulReview->setReviewID($reviewID);
            $helpfulReview->setCustomerID($customerID);
            $helpfulReview->setRating(0);
            // Wenn Hilfreich neu für eine Bewertung eingetragen wird und diese positiv ist
            if ($helpful === 1) {
                $helpfulReview->setRating(1);
                ++$review->helpful;
                $review->save(['helpful']);
            } else {
                // Wenn Hilfreich neu für eine Bewertung eingetragen wird und diese negativ ist
                ++$review->notHelpful;
                $review->save(['notHelpful']);
            }

            \executeHook(\HOOK_BEWERTUNG_INC_SPEICHERBEWERTUNGHILFREICH, ['rating' => &$helpfulReview]);

            $helpfulReview->save();
            $this->cache->flushTags([\CACHING_GROUP_ARTICLE . '_' . $review->getProductID()]);
            if (!Request::isAjaxRequest()) {
                \header('Location: ' . $baseURL . '&cHinweis=h02', true, 303);
                exit;
            }
        }
        // Wenn Hilfreich nicht neu (wechsel) für eine Bewertung eingetragen wird und diese positiv ist
        if ($helpful === 1 && $helpfulReview->getRating() !== $helpful) {
            ++$review->helpful;
            --$review->notHelpful;
            $review->save(['helpful', 'notHelpful']);
        } elseif ($helpful === 0 && $helpfulReview->getRating() !== $helpful) {
            // Wenn Hilfreich neu für (wechsel) eine Bewertung eingetragen wird und diese negativ ist
            --$review->helpful;
            ++$review->notHelpful;
            $review->save(['helpful', 'notHelpful']);
        }
        $helpfulReview->rating     = $helpful;
        $helpfulReview->reviewID   = $reviewID;
        $helpfulReview->customerID = $customerID;
        $helpfulReview->save();
        $this->cache->flushTags([\CACHING_GROUP_ARTICLE . '_' . $review->getProductID()]);
        if (!Request::isAjaxRequest()) {
            \header('Location: ' . $baseURL . '&cHinweis=h03', true, 303);
            exit;
        }
    }
}
