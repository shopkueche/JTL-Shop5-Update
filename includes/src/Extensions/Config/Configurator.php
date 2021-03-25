<?php

namespace JTL\Extensions\Config;

use JTL\Cart\CartHelper;
use JTL\Catalog\Product\Artikel;
use JTL\Catalog\Product\Preise;
use JTL\DB\ReturnType;
use JTL\Nice;
use JTL\Shop;
use function Functional\some;

/**
 * Class Configurator
 * @package JTL\Extensions\Config
 */
class Configurator
{
    /**
     * @var array
     */
    private static $groups = [];

    /**
     * @return bool
     */
    public static function checkLicense(): bool
    {
        return Nice::getInstance()->checkErweiterung(\SHOP_ERWEITERUNG_KONFIGURATOR);
    }

    /**
     * @return array[]
     */
    public static function getGroups(): array
    {
        return self::$groups;
    }

    /**
     * @param int $productID
     * @param int $languageID
     * @return Group[]
     */
    public static function getKonfig(int $productID, int $languageID = 0): array
    {
        $groups = [];
        $data   = Shop::Container()->getDB()->selectAll(
            'tartikelkonfiggruppe',
            'kArtikel',
            $productID,
            'kKonfigGruppe',
            'nSort ASC'
        );
        if (!\is_array($data) || \count($data) === 0 || !self::checkLicense()) {
            return [];
        }
        $languageID = $languageID ?: Shop::getLanguageID();
        if (!isset(self::$groups[$languageID])) {
            self::$groups[$languageID] = [];
        }
        foreach ($data as $item) {
            $id    = (int)$item->kKonfigGruppe;
            $group = self::$groups[$languageID][$id] ?? new Group($id, $languageID);
            if (\count($group->oItem_arr) > 0) {
                $groups[]                       = $group;
                self::$groups[$languageID][$id] = $group;
            }
        }

        return $groups;
    }

    /**
     * @param int $productID
     * @return bool
     */
    public static function hasKonfig(int $productID): bool
    {
        if (!self::checkLicense()) {
            return false;
        }

        return Shop::Container()->getDB()->queryPrepared(
            'SELECT tartikelkonfiggruppe.kKonfiggruppe
                 FROM tartikelkonfiggruppe
                 JOIN tkonfigitem
                    ON tkonfigitem.kKonfiggruppe = tartikelkonfiggruppe.kKonfiggruppe
                        AND tartikelkonfiggruppe.kArtikel = :pid',
            ['pid' => $productID],
            ReturnType::SINGLE_OBJECT
        ) !== false;
    }

    /**
     * @param int $productID
     * @return bool
     */
    public static function validateKonfig($productID): bool
    {
        /* Vorvalidierung deaktiviert */
        return true;
    }

    /**
     * @param object $cart
     * @deprecated since 5.0.0
     */
    public static function postcheckBasket($cart): void
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        self::postcheckCart($cart);
    }

    /**
     * @param object $cart
     */
    public static function postcheckCart($cart): void
    {
        if (!\is_array($cart->PositionenArr) || \count($cart->PositionenArr) === 0 || !self::checkLicense()) {
            return;
        }
        $deletedItems = [];
        foreach ($cart->PositionenArr as $index => $item) {
            if ($item->nPosTyp !== \C_WARENKORBPOS_TYP_ARTIKEL) {
                continue;
            }
            $deleted = false;
            if ($item->cUnique && (int)$item->kKonfigitem === 0) {
                $configItems = [];
                foreach ($cart->PositionenArr as $child) {
                    if ($child->cUnique && $child->cUnique === $item->cUnique && $child->kKonfigitem > 0) {
                        $configItems[] = new Item($child->kKonfigitem);
                    }
                }
                // Konfiguration validieren
                if (self::validateCart($item->kArtikel, $configItems) !== true) {
                    $deleted        = true;
                    $deletedItems[] = $index;
                }
            } elseif (!$item->cUnique) {
                // Konfiguration vorhanden -> löschen
                if (self::hasKonfig($item->kArtikel)) {
                    $deleted        = true;
                    $deletedItems[] = $index;
                }
            }
            if ($deleted) {
                Shop::Container()->getLogService()->error(
                    'Validierung der Konfiguration fehlgeschlagen - Warenkorbposition wurde entfernt: ' .
                    $item->cName[$_SESSION['cISOSprache']] . '(' . $item->kArtikel . ')'
                );
            }
        }
        CartHelper::deleteCartItems($deletedItems, false);
    }

    /**
     * @param int   $productID
     * @param array $configItems
     * @return array|bool
     * @deprecated since 5.0.0
     */
    public static function validateBasket(int $productID, $configItems)
    {
        \trigger_error(__METHOD__ . ' is deprecated.', \E_USER_DEPRECATED);
        return self::validateCart($productID, $configItems);
    }

    /**
     * @param int    $productID
     * @param Item[] $configItems
     * @return array|bool
     */
    public static function validateCart(int $productID, array $configItems)
    {
        if ($productID === 0) {
            Shop::Container()->getLogService()->error('Validierung der Konfiguration fehlgeschlagen - Ungültige Daten');

            return false;
        }
        $total   = 0.0;
        $product = new Artikel();
        $product->fuelleArtikel($productID, Artikel::getDefaultOptions());
        // Grundpreis
        if ($product && (int)$product->kArtikel > 0) {
            $total = $product->Preise->fVKNetto;
        }
        $total  = self::getTotal($total, $configItems);
        $errors = self::getErrors($productID, $configItems);
        if ($total < 0.0) {
            $error = \sprintf(
                "Negative Konfigurationssumme für Artikel '%s' (Art.Nr.: %s, Netto: %s) - Vorgang abgebrochen",
                $product->cName,
                $product->cArtNr,
                Preise::getLocalizedPriceString($total)
            );
            Shop::Container()->getLogService()->error($error);

            return false;
        }

        return \count($errors) === 0 ? true : $errors;
    }

    /**
     * @param float $total
     * @param array $configItems
     * @return float|int
     */
    private static function getTotal(float $total, array $configItems)
    {
        foreach ($configItems as $configItem) {
            if (!isset($configItem->fAnzahl)
                || $configItem->fAnzahl < $configItem->getMin()
                || $configItem->fAnzahl > $configItem->getMax()
            ) {
                $configItem->fAnzahl = $configItem->getInitial();
            }
            $total += $configItem->getPreis(true) * $configItem->fAnzahl;
        }

        return $total;
    }

    /**
     * @param int   $productID
     * @param array $configItems
     * @return array
     */
    private static function getErrors(int $productID, array $configItems): array
    {
        $errors = [];
        foreach (self::getKonfig($productID) as $group) {
            $itemCount = 0;
            $groupID   = $group->getKonfiggruppe();
            $min       = $group->getMin();
            $max       = $group->getMax();
            foreach ($configItems as $configItem) {
                if ($configItem->getKonfiggruppe() === $groupID) {
                    $itemCount++;
                }
            }
            if ($itemCount < $min && $min > 0) {
                if ($min === $max) {
                    $errors[$groupID] = Shop::Lang()->get('configChooseNComponents', 'productDetails', $min);
                } else {
                    $errors[$groupID] = Shop::Lang()->get('configChooseMinComponents', 'productDetails', $min);
                }
                $errors[$groupID] .= self::langComponent($min > 1);
            } elseif ($itemCount > $max && $max > 0) {
                if ($min === $max) {
                    $errors[$groupID] = Shop::Lang()->get('configChooseNComponents', 'productDetails', $min) .
                        self::langComponent($min > 1);
                } else {
                    $errors[$groupID] = Shop::Lang()->get('configChooseMaxComponents', 'productDetails', $max) .
                        self::langComponent($max > 1);
                }
            }
        }

        return $errors;
    }

    /**
     * @param bool $plurar
     * @param bool $space
     * @return string
     */
    private static function langComponent(bool $plurar = false, bool $space = true): string
    {
        $component = $space ? ' ' : '';

        return $component . Shop::Lang()->get($plurar ? 'configComponents' : 'configComponent', 'productDetails');
    }

    /**
     * @param Group[] $confGroups
     * @return bool
     */
    public static function hasUnavailableGroup(array $confGroups): bool
    {
        return some($confGroups, static function (Group $group) {
            return !$group->minItemsInStock();
        });
    }
}
