<?php
/**
 * @copyright JTL-Software-GmbH
 */

namespace jtl\Wizard\steps;

use JTL\Shop;
use jtl\Wizard\Question;
use jtl\Wizard\ShopWizard;

/**
 * Class GlobalSettingsStep
 * @package jtl\Wizard\steps
 */
class GlobalSettingsStep extends Step implements IStep
{
    /**
     * @var int
     */
    private $standardTax;

    /**
     * @var ShopWizard
     */
    private $wizard;

    /**
     * GlobalSettingsStep constructor.
     * @param ShopWizard $wizard
     */
    public function __construct($wizard)
    {
        $this->wizard    = $wizard;
        $this->questions = [
            new Question('Kleinunternehmerregelung nach	 §19 UStG anwenden?', Question::TYPE_BOOL, 10),
            new Question('Globale Email-Adresse:', Question::TYPE_EMAIL, 11)
        ];
    }

    /**
     * @return int[]
     */
    public function getAvailableQuestions()
    {
        $availables   = [];
        $availables[] = 0;
        $availables[] = 1;

        return $availables;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Globale Einstellungen';
    }

    /**
     * @return int
     */
    public function getTax()
    {
        $res               = Shop::Container()->getDB()->query(
            "SELECT fSteuersatz FROM tsteuersatz WHERE kSteuersatz = 1",
            1
        );
        $this->standardTax = $res->fSteuersatz;

        return $this->standardTax;
    }

    /**
     * @param bool $jumpToNext
     * @return mixed|void
     */
    public function finishStep($jumpToNext = true)
    {
        $db = Shop::Container()->getDB();
        if ($this->questions[0]->getValue()) {
            //  Einstellung 223 auf "endpreis"
            $db->update('teinstellungen', 'cName', 'global_ust_auszeichnung', (object)[
                'cWert' => 'endpreis'
            ]);

            //  Einstellung 224 FooterText anpassen
            $db->update('teinstellungen', 'cName', 'global_fusszeilehinweis', (object)[
                'cWert' => '* Gemäß §19 UStG wird keine Umsatzsteuer berechnet'
            ]);

            //  Einstellung 225 auf "nein" stellen
            $db->update('teinstellungen', 'cName', 'global_steuerpos_anzeigen', (object)[
                'cWert' => 'N'
            ]);
        } else {
            $db->update('teinstellungen', 'cName', 'global_ust_auszeichnung', (object)[
                'cWert' => 'auto'
            ]);
            $db->update('teinstellungen', 'cName', 'global_fusszeilehinweis', (object)[
                'cWert' => ''
            ]);
            $db->update('teinstellungen', 'cName', 'global_steuerpos_anzeigen', (object)[
                'cWert' => 'Y'
            ]);
        }

        if ($this->questions[1]->getValue()) {
            $db->update('teinstellungen', 'cName', 'email_master_absender', (object)[
                'cWert' => $this->questions[1]->getValue()
            ]);

            if ($jumpToNext === true) {
                if ($this->questions[0]->getValue()) {
                    $this->wizard->setStep(3);
                    $this->wizard->setStep(new AdditionalLinks($this->wizard));
                } else {
                    $this->wizard->setStep(new CustomerFormStep($this->wizard));
                }
            }
        }
    }
}
