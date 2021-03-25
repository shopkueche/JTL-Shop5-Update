<?php

namespace JTL\dbeS\Push;

use JTL\DB\ReturnType;
use JTL\Helpers\Request;
use JTL\Plugin\Payment\LegacyMethod;
use stdClass;

/**
 * Class Invoice
 * @package JTL\dbeS\Push
 */
final class Invoice extends AbstractPush
{
    /**
     * @inheritdoc
     */
    public function getData()
    {
        if (!isset($_POST['kBestellung'], $_POST['kSprache'])) {
            return [];
        }
        $orderID = Request::postInt('kBestellung');
        $langID  = Request::postInt('kSprache');
        if ($orderID <= 0 || $langID <= 0) {
            return $this->pushError('Wrong params (kBestellung: ' . $orderID . ', kSprache: ' . $langID . ').');
        }
        $order = $this->getOrder($orderID);
        if (!$order) {
            return $this->pushError('Keine Bestellung mit kBestellung ' . $orderID . ' gefunden!');
        }
        $paymentMethod = LegacyMethod::create($order->cModulId);
        if (!$paymentMethod) {
            return $this->pushError('Keine Bestellung mit kBestellung ' . $orderID . ' gefunden!');
        }
        $invoice = $paymentMethod->createInvoice($orderID, $langID);
        if (\is_object($invoice)) {
            if ($invoice->nType === 0 && \strlen($invoice->cInfo) === 0) {
                $invoice->cInfo = 'Funktion in Zahlungsmethode nicht implementiert';
            }
            return $this->createResponse(
                $order->kBestellung,
                ($invoice->nType === 0 ? 'FAILURE' : 'SUCCESS'),
                $invoice->cInfo
            );
        }

        return $this->pushError('Fehler beim Erstellen der Rechnung (kBestellung: ' . $order->kBestellung . ').');
    }

    /**
     * @param int $id
     * @return stdClass|null
     */
    private function getOrder(int $id): ?stdClass
    {
        return $this->db->queryPrepared(
            'SELECT tbestellung.kBestellung, tbestellung.fGesamtsumme, tzahlungsart.cModulId
            FROM tbestellung
            LEFT JOIN tzahlungsart
              ON tbestellung.kZahlungsart = tzahlungsart.kZahlungsart
            WHERE tbestellung.kBestellung = :oid 
            LIMIT 1',
            ['oid' => $id],
            ReturnType::SINGLE_OBJECT
        );
    }

    /**
     * @param int    $orderID
     * @param string $type
     * @param string $comment
     * @return array
     */
    private function createResponse(int $orderID, $type, $comment): array
    {
        $res                               = ['tbestellung' => []];
        $res['tbestellung']['kBestellung'] = $orderID;
        $res['tbestellung']['cTyp']        = $type;
        $res['tbestellung']['cKommentar']  = \html_entity_decode(
            $comment,
            \ENT_COMPAT | \ENT_HTML401,
            'ISO-8859-1'
        ); // decode entities for jtl-wawi.
        // Entities are html-encoded since
        // https://gitlab.jtl-software.de/jtlshop/jtl-shop/commit/e81f7a93797d8e57d00a1705cc5f13191eee9ca1

        return $res;
    }

    /**
     * @param string $message
     * @return array
     */
    private function pushError(string $message): array
    {
        $this->logger->error('Error @ invoice_xml: ' . $message);

        return $this->createResponse(0, 'FAILURE', $message);
    }
}
