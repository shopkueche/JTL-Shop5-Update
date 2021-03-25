<?php
/**
 * Setting for SEO handling of articles which are sold out
 *
 * @author fp
 * @created Mon, 19 Mar 2018 12:03:12 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180907143536
 */
class Migration_20180907143536 extends Migration implements IMigration
{
    protected $author      = 'fp';
    protected $description = 'Setting for SEO handling of articles which are sold out';

    /**
     * @return bool|void
     * @throws Exception
     */
    public function up()
    {
        $this->setConfig(
            'artikel_artikelanzeigefilter_seo',
            'seo',
            CONF_GLOBAL,
            'Direktaufruf ausverkaufter Artikel',
            'selectbox',
            215,
            (object)[
                'cBeschreibung' => 'Methode beim Direktaufruf (über Artikel-URL) ausverkaufter Artikel. ' .
                    '(Ist nur wirksam, wenn "Artikelanzeigefilter" aktiv ist.)',
                'inputOptions'  => [
                    '404' => 'Seite nicht gefunden (404 Not Found)',
                    'seo' => 'Artikel-Detailseite bleibt erreichbar',
                ],
            ],
            true
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeConfig('artikel_artikelanzeigefilter_seo');
    }
}
