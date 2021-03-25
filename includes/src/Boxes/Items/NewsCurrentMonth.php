<?php declare(strict_types=1);

namespace JTL\Boxes\Items;

use JTL\DB\ReturnType;
use JTL\Helpers\URL;
use JTL\Shop;

/**
 * Class NewsCurrentMonth
 * @package JTL\Boxes\Items
 */
final class NewsCurrentMonth extends AbstractBox
{
    /**
     * NewsCurrentMonth constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->addMapping('oNewsMonatsUebersicht_arr', 'Items');
        $langID       = Shop::getLanguageID();
        $sql          = (int)$config['news']['news_anzahl_box'] > 0
            ? ' LIMIT ' . (int)$config['news']['news_anzahl_box']
            : '';
        $newsOverview = Shop::Container()->getDB()->queryPrepared(
            "SELECT tseo.cSeo, tnewsmonatsuebersicht.cName, tnewsmonatsuebersicht.kNewsMonatsUebersicht, 
                MONTH(tnews.dGueltigVon) AS nMonat, YEAR( tnews.dGueltigVon ) AS nJahr, COUNT(*) AS nAnzahl
                FROM tnews
                JOIN tnewsmonatsuebersicht 
                    ON tnewsmonatsuebersicht.nMonat = MONTH(tnews.dGueltigVon)
                    AND tnewsmonatsuebersicht.nJahr = YEAR(tnews.dGueltigVon)
                    AND tnewsmonatsuebersicht.kSprache = :lid
                JOIN tnewssprache t 
                    ON tnews.kNews = t.kNews
                LEFT JOIN tseo 
                    ON cKey = 'kNewsMonatsUebersicht'
                    AND kKey = tnewsmonatsuebersicht.kNewsMonatsUebersicht
                    AND tseo.kSprache = :lid
                WHERE tnews.dGueltigVon < NOW()
                    AND tnews.nAktiv = 1
                    AND t.languageID = :lid
                GROUP BY YEAR(tnews.dGueltigVon) , MONTH(tnews.dGueltigVon)
                ORDER BY tnews.dGueltigVon DESC" . $sql,
            ['lid' => $langID],
            ReturnType::ARRAY_OF_OBJECTS
        );
        foreach ($newsOverview as $item) {
            $item->cURL     = URL::buildURL($item, \URLART_NEWSMONAT);
            $item->cURLFull = URL::buildURL($item, \URLART_NEWSMONAT, true);
        }
        $this->setShow(\count($newsOverview) > 0);
        $this->setItems($newsOverview);

        \executeHook(\HOOK_BOXEN_INC_NEWS);
    }
}
