<?php
/**
 * @copyright JTL-Software-GmbH
 */

namespace jtl\Wizard\steps;

use JTL\Shop;
use jtl\Wizard;
use jtl\Wizard\Question;

/**
 * Class CustomerFormStep
 * @package jtl\Wizard\steps
 */
class CustomerFormStep extends Step implements IStep
{
    /**
     * @var Wizard\ShopWizard
     */
    private $wizard;

    /**
     * @var string
     */
    private $ustidStatus;

    /**
     * CustomerFormStep constructor.
     * @param Wizard\ShopWizard $wizard
     *
     * Step 2
     */
    public function __construct($wizard)
    {
        $dependOnQ0 = function () {
            return false;
        };

        $this->wizard    = $wizard;
        $this->questions = [
            new Question('Verkauf an Endkunden', Question::TYPE_BOOL, 20),
            new Question('Eindeutige Artikelmerkmale: Merkmale', Question::TYPE_BOOL, 21, 20),
            new Question('Eindeutige Artikelmerkmale: Attribute anzeigen', Question::TYPE_BOOL, 22, 20),
            new Question('Eindeutige Artikelkurzbeschreibung anzeigen', Question::TYPE_BOOL, 23, 20),
            new Question('Verkauf an Händler', Question::TYPE_BOOL, 24),
            new Question('UstID des Shops', Question::TYPE_TEXT, 25, 24),
            new Question('Telefonnummer abfragen', Question::TYPE_BOOL, 26),
            new Question('Geburtsdatum abfragen', Question::TYPE_BOOL, 27),
            new Question('Weltweit versenden', Question::TYPE_BOOL, 28)
        ];
    }

    /**
     * @return int[]
     */
    public function getAvailableQuestions()
    {
        $availables = [];

        $availables[] = 0;

        if ($this->questions[0]->getValue()) {
            $availables[] = 1;
            $availables[] = 2;
            $availables[] = 3;
        }

        $availables[] = 4;

        if ($this->questions[4]->getValue()) {
            $availables[] = 5;
        }

        $availables[] = 6;
        $availables[] = 7;
        $availables[] = 8;

        return $availables;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Formularfelder';
    }

    /**
     * @return bool
     */
    public function isUstidStatus()
    {
        $res               = Shop::Container()->getDB()->query('SELECT cUSTID FROM tfirma', 1);
        $this->ustidStatus = !empty($res->cUSTID);

        return $this->ustidStatus;
    }

    /**
     * @param bool $jumpToNext
     * @return mixed|void
     */
    public function finishStep($jumpToNext = true)
    {
        $db = Shop::Container()->getDB();
        // B2B
        if ($this->questions[4]->getValue()) {
            // Einschalten
            // KundenAbfrage Firma
            $db->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_firma', (object)[
                'cWert' => 'Y'
            ]);

            // KundenAbfrage UstID
            $db->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_ustid', (object)[
                'cWert' => 'Y'
            ]);

            // Zentralamt
            $db->update('teinstellungen', 'cName', 'shop_ustid_bzstpruefung', (object)[
                'cWert' => 'Y'
            ]);

            // Firmenzusatz
            $db->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_firmazusatz', (object)[
                'cWert' => 'Y'
            ]);

            // Adresszusatz
            $db->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_adresszusatz', (object)[
                'cWert' => 'Y'
            ]);

        }

        // B2C
        if ($this->questions[0]->getValue()) {
            // Ausschalten
            // KundenAbfrage Firma
            $db->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_firma', (object)[
                'cWert' => '0'
            ]);

            // KundenAbfrage UstID
            $db->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_ustid', (object)[
                'cWert' => '0'
            ]);

            // Zentralamt
            $db->update('teinstellungen', 'cName', 'shop_ustid_bzstpruefung', (object)[
                'cWert' => 'N'
            ]);

            // Firmenzusatz
            $db->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_firmazusatz', (object)[
                'cWert' => '0'
            ]);

            // Adresszusatz
            $db->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_adresszusatz', (object)[
                'cWert' => '0'
            ]);
        }

        if ($this->questions[5]->getValue()) {
            // Abfrage UstID
            $db->update('teinstellungen', 'cName', 'shop_ustid', (object)[
                'cWert' => $this->questions[5]->getValue()
            ]);
        }

        if ($this->questions[1]->getValue()) {
            // Eindeutige Artikelmerkmale Merkmale
            $db->update('teinstellungen', 'cName', 'bestellvorgang_artikelmerkmale', (object)[
                'cWert' => 'Y'
            ]);
        } else {
            $db->update('teinstellungen', 'cName', 'bestellvorgang_artikelmerkmale', (object)[
                'cWert' => 'N'
            ]);
        }

        if ($this->questions[2]->getValue()) {
            // Eindeutige Artikelmerkmale Attribute
            $db->update('teinstellungen', 'cName', 'bestellvorgang_artikelattribute', (object)[
                'cWert' => 'Y'
            ]);
        } else {
            $db->update('teinstellungen', 'cName', 'bestellvorgang_artikelattribute', (object)[
                'cWert' => 'N'
            ]);
        }

        if ($this->questions[3]->getValue()) {
            // Eindeutige Artikelkurzbeschreibung
            $db->update('teinstellungen', 'cName', 'bestellvorgang_artikelkurzbeschreibung', (object)[
                'cWert' => 'Y'
            ]);
        } else {
            $db->update('teinstellungen', 'cName', 'bestellvorgang_artikelkurzbeschreibung', (object)[
                'cWert' => 'N'
            ]);
        }

        if ($this->questions[6]->getValue()) {
            // Telefonnummer abfragen
            $db->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_tel', (object)[
                'cWert' => 'Y'
            ]);
        } else {
            $db->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_tel', (object)[
                'cWert' => '0'
            ]);
        }

        if ($this->questions[7]->getValue()) {
            // Geburtstag abfragen
            $db->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_geburtstag', (object)[
                'cWert' => 'Y'
            ]);
        } else {
            $db->update('teinstellungen', 'cName', 'kundenregistrierung_abfragen_geburtstag', (object)[
                'cWert' => '0'
            ]);
        }

        // Weltweit versenden
        if ($this->questions[8]->getValue()) {
            // Land abfragen
            $db->update('teinstellungen', 'cName', 'kundenregistrierung_standardland', (object)[
                'cWert' => 'Deutschland'
            ]);
            $db->update('teinstellungen', 'cName', 'lieferadresse_abfragen_standardland', (object)[
                'cWert' => 'Deutschland'
            ]);

            // Bundesland abfragen
            $db->update('teinstellungen', 'cName', 'kundenregistrierung_bundesland', (object)[
                'cWert' => 'Y'
            ]);

            $db->update('teinstellungen', 'cName', 'lieferadresse_abfragen_bundesland', (object)[
                'cWert' => 'Y'
            ]);
        } else {
            // Land nicht abfragen
            $db->update('teinstellungen', 'cName', 'kundenregistrierung_standardland', (object)[
                'cWert' => ''
            ]);
            $db->update('teinstellungen', 'cName', 'lieferadresse_abfragen_standardland', (object)[
                'cWert' => ''
            ]);

            // Bundesland nicht abfragen
            $db->update('teinstellungen', 'cName', 'kundenregistrierung_bundesland', (object)[
                'cWert' => '0'
            ]);

            $db->update('teinstellungen', 'cName', 'lieferadresse_abfragen_bundesland', (object)[
                'cWert' => '0'
            ]);
        }

        if ($jumpToNext && ($this->questions[0]->getValue() || $this->questions[4]->getValue())) {
            $this->wizard->setStep(new AdditionalLinks($this->wizard));
        }
    }
}
