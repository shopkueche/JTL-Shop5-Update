<?php
/**
 * Remove cron type tpl
 *
 * @author fm
 * @created Thu, 19 Mar 2020 16:25:00 +0100
 */

use JTL\DB\ReturnType;
use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200420130000
 */
class Migration_20200420130000 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add youtube consent item';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $id = $this->__execute(
            "INSERT INTO `tconsent`
                (`itemID`, `company`, `pluginID`, `active`)
                VALUES ('youtube', 'Google Inc.', 0, 1)",
            ReturnType::LAST_INSERTED_ID
        );
        $this->execute(
            "INSERT INTO `tconsentlocalization` 
                (`consentID`,`languageID`,`privacyPolicy`,`description`,`purpose`,`name`)
             VALUES (" . $id . ",1,'https://policies.google.com/privacy?hl=de',
             'Um Inhalte von YouTube auf dieser Seite zu entsperren, ist Ihre Zustimmung zur Datenweitergabe und 
             Speicherung von Drittanbieter-Cookies des Anbieters YouTube (Google) erforderlich.\nDies erlaubt uns, 
             unser Angebot sowie das Nutzererlebnis für Sie zu verbessern und interessanter auszugestalten.\nOhne 
             Ihre Zustimmung findet keine Datenweitergabe an YouTube statt, jedoch können die Funktionen von YouTube 
             dann auch nicht auf dieser Seite verwendet werden. ',
             'Einbetten von Videos',
             'YouTube')
         ");
        $this->execute(
            "INSERT INTO `tconsentlocalization` 
                (`consentID`,`languageID`,`privacyPolicy`,`description`,`purpose`,`name`) 
                VALUES (" . $id . ",2,
                'https://google.com/privacy-policy','To view YouTube contents on this website, 
                you need to consent to the transfer of data and storage of third-party cookies by 
                YouTube (Google).\n\nThis allows us to improve your user experience and to make our 
                website better and more interesting.\n\nWithout your consent, no data will be transferred to YouTube. 
                However, you will also not be able to use the YouTube services on this website. ',
                'Embedding videos',
                'YoutTube'
            );
        ");
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute("DELETE FROM tconsent WHERE itemID = 'youtube'");
    }
}
