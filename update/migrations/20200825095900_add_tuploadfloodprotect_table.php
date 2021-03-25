<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200825095900
 */
class Migration_20200825095900 extends Migration implements IMigration
{
    protected $author      = 'je';
    protected $description = 'Add tfloodprotect table';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->execute(
            "CREATE TABLE IF NOT EXISTS tfloodprotect (
                kFloodProtect int(10) unsigned NOT NULL AUTO_INCREMENT,
                cIP varchar(255) NULL COMMENT 'the user ip',
                cTyp varchar(255) NULL COMMENT 'defines where the protection was used',
                dErstellt datetime NULL COMMENT 'the request date',
                PRIMARY KEY (kFloodProtect),
                KEY cIP (cTyp, cIP)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");

        $this->setConfig(
            'upload_modul_limit',
            '10',
            CONF_ARTIKELDETAILS,
            'Erlaubte Datei-Uploads pro Stunde',
            'number',
            499,
            (object)[
                'cBeschreibung' => 'Hier legen Sie fest, wie viele Dateien ein Benutzer bei aktiviertem Uploadmodul pro Stunde maximal hochladen darf.'
            ]
        );

        $this->setLocalization('ger', 'global', 'uploadErrorReachedLimitPerHour', 'Das Uploadlimit pro Stunde wurde erreicht. Bitte versuchen Sie es später noch einmal.');
        $this->setLocalization('eng', 'global', 'uploadErrorReachedLimitPerHour', 'You have reached the upload limit per hour. Please try again later.');

        $this->setLocalization('ger', 'global', 'uploadErrorFiletypeForbidden', 'Dieser Dateityp ist nicht erlaubt.');
        $this->setLocalization('eng', 'global', 'uploadErrorFiletypeForbidden', 'This Filetype is forbidden.');

        $this->setLocalization('ger', 'global', 'uploadErrorExtensionNotListed', 'Dieser Dateityp wird nicht unterstützt.');
        $this->setLocalization('eng', 'global', 'uploadErrorExtensionNotListed', 'This file extension is not supported.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `tfloodprotect`');
        $this->removeConfig('upload_modul_limit');
        $this->removeLocalization('uploadErrorReachedLimitPerHour');
        $this->removeLocalization('uploadErrorFiletypeForbidden');
        $this->removeLocalization('uploadErrorExtensionNotListed');
    }
}
