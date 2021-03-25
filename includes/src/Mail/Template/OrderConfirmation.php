<?php declare(strict_types=1);

namespace JTL\Mail\Template;

use JTL\DB\ReturnType;
use JTL\Smarty\JTLSmarty;

/**
 * Class OrderConfirmation
 * @package JTL\Mail\Template
 */
class OrderConfirmation extends OrderShipped
{
    protected $id = \MAILTEMPLATE_BESTELLBESTAETIGUNG;

    /**
     * @inheritdoc
     */
    public function preRender(JTLSmarty $smarty, $data): void
    {
        parent::preRender($smarty, $data);
        if ($data === null) {
            return;
        }
        $smarty->assign('Verfuegbarkeit_arr', $data->cVerfuegbarkeit_arr ?? null);
        $moduleID = $data->tbestellung->Zahlungsart->cModulId ?? null;
        if (!empty($moduleID)) {
            $paymentConf = $this->db->queryPrepared(
                'SELECT tzahlungsartsprache.*
                    FROM tzahlungsartsprache
                    JOIN tzahlungsart
                        ON tzahlungsart.kZahlungsart = tzahlungsartsprache.kZahlungsart
                        AND tzahlungsart.cModulId = :module
                    JOIN tsprache
                        ON tsprache.cISO = tzahlungsartsprache.cISOSprache
                    WHERE tsprache.kSprache = :lid',
                ['module' => $moduleID, 'lid' => $this->languageID],
                ReturnType::SINGLE_OBJECT
            );
            if (isset($paymentConf->kZahlungsart) && $paymentConf->kZahlungsart > 0) {
                $smarty->assign('Zahlungsart', $paymentConf);
            }
        }
    }
}
