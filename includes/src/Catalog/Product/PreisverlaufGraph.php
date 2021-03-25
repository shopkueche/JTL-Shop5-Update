<?php

namespace JTL\Catalog\Product;

use JTL\DB\ReturnType;
use JTL\Shop;

/**
 * Class PreisverlaufGraph
 * @package JTL\Catalog\Product
 */
class PreisverlaufGraph
{
    /**
     * Zeitdifferenz (Unix Timestamp) zwischen dem ältesten Preisverlaufseintrag und dem jüngsten
     *
     * @var int
     */
    public $nDiffStamp;

    /**
     * Anzahl an Preisen für die Y-Achsen Legende
     *
     * @var int
     */
    public $nAnzahlPreise;

    /**
     * Anzahl Tage in der es Preisverlaufseinträge für den Aritkel gibt
     *
     * @var int
     */
    public $nAnzahlTage;

    /**
     * Aktueller Schritt für den jeweiligen X-Achsen Legendeneintrag
     *
     * @var int
     */
    public $nStepX;

    /**
     * Aktueller Schritt für den jeweiligen Y-Achsen Legendeneintrag
     *
     * @var int
     */
    public $nStepY;

    /**
     * Maximale Höhe des Bildes
     *
     * @var int
     */
    public $nHoehe;

    /**
     * Maximale Breite des Bildes
     *
     * @var int
     */
    public $nBreite;

    /**
     * Der Preisschritt für die Y-Achsen Legendenbeschriftung
     *
     * @var int
     */
    public $nStep;

    /**
     * Jüngster Preisverlaufseintrag (Unix Timestamp)
     *
     * @var int
     */
    public $nMaxTimestamp;

    /**
     * Ältester Preisverlaufseintrag (Unix Timestamp)
     *
     * @var int
     */
    public $nMinTimestamp;

    /**
     * Schriftgröße für die Legendenbeschriftung bzw. allen Schriften im Bild
     *
     * @var int
     */
    public $nSchriftgroesse;

    /**
     * Linke Polster zwischen dem Bildanfang und der äusseren Box
     *
     * @var int
     */
    public $nPolsterLinks;

    /**
     * Rechte Polster zwischen dem Bildanfang und der äusseren Box
     *
     * @var int
     */
    public $nPolsterRechts;

    /**
     * Oberes Polster zwischen dem Bildanfang und der äusseren Box
     *
     * @var int
     */
    public $nPolsterOben;

    /**
     * Unteres Polster zwischen dem Bildanfang und der äusseren Box
     *
     * @var int
     */
    public $nPolsterUnten;

    /**
     * X-Achsen Polster zwischen der äusseren Box und der inneren Box
     *
     * @var int
     */
    public $nInternPolsterX;

    /**
     * Y-Achsen Polster zwischen der äusseren Box und der inneren Box
     *
     * @var int
     */
    public $nInternPolsterY;

    /**
     * Breite der äusseren Box
     *
     * @var int
     */
    public $nBreiteRahmen;

    /**
     * Höhe der äusseren Box
     *
     * @var int
     */
    public $nHoeheRahmen;

    /**
     * X-Achsen Polster zwischen der äusseren Box und der inneren Box in Pixel
     *
     * @var int
     */
    public $nInternPolsterXPixel;

    /**
     * Y-Achsen Polster zwischen der äusseren Box und der inneren Box in Pixel
     *
     * @var int
     */
    public $nInternPolsterYPixel;

    /**
     * Breite der inneren Box
     *
     * @var int
     */
    public $nInnenRahmenBreite;

    /**
     * Höhe der inneren Box
     *
     * @var int
     */
    public $nInnenRahmenHoehe;

    /**
     * Y-Position der oberen Aussenbox
     *
     * @var int
     */
    public $nAussenRahmenOben;

    /**
     * X-Position der linken Aussenbox
     *
     * @var int
     */
    public $nAussenRahmenLinks;

    /**
     * Y-Position der unteren Aussenbox
     *
     * @var int
     */
    public $nAussenRahmenUnten;

    /**
     * X-Position der rechten Aussenbox
     *
     * @var int
     */
    public $nAussenRahmenRechts;

    /**
     * Y-Position der oberen Innenbox
     *
     * @var int
     */
    public $nInnenRahmenOben;

    /**
     * X-Position der linken Innenbox
     *
     * @var int
     */
    public $nInnenRahmenLinks;

    /**
     * Y-Position der unteren Innenbox
     *
     * @var int
     */
    public $nInnenRahmenUnten;

    /**
     * X-Position der rechten Innenbox
     *
     * @var int
     */
    public $nInnenRahmenRechts;

    /**
     * Größter Preis vom aktuellen Preisverlauf
     *
     * @var float
     */
    public $fMaxPreis;

    /**
     * Kleinster Preis vom aktuellen Preisverlauf
     *
     * @var float
     */
    public $fMinPreis;

    /**
     * Differenz zwischen dem kleinsten und größten Preisverlaufspreis
     *
     * @var float
     */
    public $fDiffPreis;

    /**
     * Array von Preissteps für die Berechnung der Y-Achsen Legende
     *
     * @var float
     */
    public $fStepWert_arr;

    /**
     * Schriftart für die Legendenbeschriftung bzw. allen Schriften im Bild
     *
     * @var string
     */
    public $cSchriftart;

    /**
     * Schriftverzeichnis der Schriftart
     *
     * @var string
     */
    public $cSchriftverzeichnis;

    /**
     * Daten vom Preisverlauf aus der Datenbank
     *
     * @var array
     */
    public $oPreisverlaufData_arr;

    /**
     * Daten vom Backend für die Einstellung von Farben, Padding, Größe etc.
     *
     * @var array
     */
    public $oConfig_arr;

    /**
     * Währung und Steuersatz der Preise
     *
     * @var array
     */
    public $oPreisConfig;

    /**
     * Bild vom Graphen
     *
     * @var string image
     */
    public $image;

    /**
     * Hintergrundfarbe des Bildes
     *
     * @var string
     */
    public $ColorBackground;

    /**
     * Gridfarbe
     *
     * @var string
     */
    public $ColorGrid;

    /**
     * Graphenfarbe
     *
     * @var string
     */
    public $ColorGraph;

    /**
     * Boxfarbe
     *
     * @var string
     */
    public $ColorBox;

    /**
     * Textfarbe
     *
     * @var string
     */
    public $ColorText;

    /**
     * @param int    $productID
     * @param int    $customerGroupID
     * @param int    $month
     * @param array  $config
     * @param object $priceConfig
     */
    public function __construct($productID, $customerGroupID, $month, $config, $priceConfig)
    {
        $this->nPolsterLinks   = 25;
        $this->nPolsterRechts  = 25;
        $this->nPolsterOben    = 25;
        $this->nPolsterUnten   = 25;
        $this->nInternPolsterX = 3;
        $this->nInternPolsterY = 3;

        $this->nBreiteRahmen        = 0;
        $this->nHoeheRahmen         = 0;
        $this->nInternPolsterXPixel = 0;
        $this->nInternPolsterYPixel = 0;
        $this->nInnenRahmenBreite   = 0;
        $this->nInnenRahmenHoehe    = 0;
        $this->nAussenRahmenOben    = 0;
        $this->nAussenRahmenLinks   = 0;
        $this->nAussenRahmenUnten   = 0;
        $this->nAussenRahmenRechts  = 0;
        $this->nInnenRahmenOben     = 0;
        $this->nInnenRahmenLinks    = 0;
        $this->nInnenRahmenUnten    = 0;
        $this->nInnenRahmenRechts   = 0;

        $this->nHoehe              = 0;
        $this->nBreite             = 0;
        $this->nAnzahlPreise       = 0;
        $this->nAnzahlTage         = 0;
        $this->nStepX              = 0;
        $this->nStepY              = 0;
        $this->nMaxTimestamp       = 0;
        $this->nMinTimestamp       = 0;
        $this->fMaxPreis           = 0.0;
        $this->fMinPreis           = 0.0;
        $this->fDiffPreis          = 0.0;
        $this->nStep               = 0;
        $this->fStepWert_arr       = [
            0.25,
            0.5,
            1.0,
            2.5,
            5.0,
            7.5,
            10.0,
            12.5,
            15.0,
            25.0,
            50.0,
            100.0,
            250.0,
            2500.0,
            25000.0
        ];
        $this->ColorBackground     = [255, 255, 255];
        $this->ColorGrid           = [255, 255, 255];
        $this->ColorGraph          = [255, 255, 255];
        $this->ColorBox            = [255, 255, 255];
        $this->ColorText           = [255, 255, 255];
        $this->nSchriftgroesse     = 8;
        $this->cSchriftart         = 'GeosansLight.ttf';
        $this->cSchriftverzeichnis = \PFAD_ROOT . \PFAD_FONTS . '/';

        $this->oConfig_arr  = $config;
        $this->oPreisConfig = $priceConfig;
        $this->setzeBreiteHoehe();
        $this->berechneSigniPunkte();
        $this->image = @\imagecreate($this->nBreite, $this->nHoehe);
        $this->berechneFarbHexNachDec();
        \imagecolorallocate(
            $this->image,
            $this->ColorBackground[0],
            $this->ColorBackground[1],
            $this->ColorBackground[2]
        );

        if ($this->berechneMinMaxPreis((int)$productID, (int)$customerGroupID, (int)$month)) {
            $this->berechneYPreisStep();
        }
    }

    /**
     * Holt den Preisverlauf für den aktuellen Artikel aus der Datenbank
     *
     * @param int $productID
     * @param int $customerGroupID
     * @param int $month
     * @return array
     */
    public function holePreisverlauf(int $productID, int $customerGroupID, int $month): array
    {
        $items = Shop::Container()->getDB()->queryPrepared(
            'SELECT fVKNetto, UNIX_TIMESTAMP(dDate) AS timestamp
                FROM tpreisverlauf
                WHERE kArtikel = :aid
                    AND kKundengruppe = :cid
                    AND DATE_SUB(NOW(), INTERVAL :mnth MONTH) < dDate
                ORDER BY dDate DESC',
            [
                'aid'  => $productID,
                'cid'  => $customerGroupID,
                'mnth' => $month
            ],
            ReturnType::ARRAY_OF_OBJECTS
        );
        if (\count($items) === 0) {
            return [];
        }
        $this->nAnzahlTage = \count($items);

        if ($this->oPreisConfig->Netto > 0) {
            foreach ($items as $i => $oPreisverlauf) {
                $items[$i]->fVKNetto +=
                    ($oPreisverlauf->fVKNetto * ($this->oPreisConfig->Netto / 100.0));
            }
        }
        if ($this->nAnzahlTage > 1) {
            $this->nMaxTimestamp = $items[0]->timestamp;
            $this->nMinTimestamp = $items[\count($items) - 1]->timestamp;
            $this->nDiffStamp    = $this->nMaxTimestamp - $this->nMinTimestamp;

            return $items;
        }
        if ($this->nAnzahlTage === 1) {
            $this->nMaxTimestamp = $items[0]->timestamp;
            $this->nMinTimestamp = $this->nMaxTimestamp;

            return $items;
        }

        return [];
    }

    /**
     * Berechnet für den aktuellen Artikel den maximalen und minimalen Preis
     *
     * @param int $productID
     * @param int $customerGroupID
     * @param int $month
     * @return bool
     */
    public function berechneMinMaxPreis(int $productID, int $customerGroupID, int $month): bool
    {
        $this->oPreisverlaufData_arr = $this->holePreisverlauf($productID, $customerGroupID, $month);

        if (\is_array($this->oPreisverlaufData_arr) && \count($this->oPreisverlaufData_arr) > 1) {
            $net = [];
            foreach ($this->oPreisverlaufData_arr as $oPreisverlauf) {
                $net[] = $oPreisverlauf->fVKNetto;
            }

            $this->fMaxPreis  = \round((float)\max($net), 2);
            $this->fMinPreis  = \round((float)\min($net), 2);
            $this->fDiffPreis = $this->fMaxPreis - $this->fMinPreis;
        } elseif ($this->oPreisverlaufData_arr !== null && \count($this->oPreisverlaufData_arr) === 1) {
            $this->fMaxPreis = $this->oPreisverlaufData_arr[0]->fVKNetto;
            $this->fMinPreis = $this->fMaxPreis;
        } else {
            return false;
        }

        \imagecolorallocate($this->image, $this->ColorText[0], $this->ColorText[1], $this->ColorText[2]);

        return true;
    }

    /**
     * Berechnet den Y Werteschritt für die Beschriftung
     */
    public function berechneYPreisStep(): void
    {
        if ($this->nAnzahlTage === 1) {
            $this->nAnzahlPreise = 1;
        } elseif ($this->nAnzahlTage > 1) {
            if (\count($this->fStepWert_arr) === 0) {
                return;
            }
            foreach ($this->fStepWert_arr as $i => $fStepWert) {
                if (($this->fDiffPreis / $fStepWert) < 10) {
                    $this->nStep = $i;
                    break;
                }
            }
            \imagecolorallocate($this->image, $this->ColorText[0], $this->ColorText[1], $this->ColorText[2]);

            $this->fMaxPreis = \round(
                (
                    (($this->fMaxPreis * 100) -
                        (($this->fMaxPreis * 100) % ($this->fStepWert_arr[$this->nStep] * 100))) +
                    ($this->fStepWert_arr[$this->nStep] * 100)
                ) / 100,
                2
            );
            $this->fMinPreis = \round(
                (($this->fMinPreis * 100) - (($this->fMinPreis * 100) % ($this->fStepWert_arr[$this->nStep] * 100)))
                / 100,
                2
            );

            $this->fDiffPreis    = $this->fMaxPreis - $this->fMinPreis;
            $this->nAnzahlPreise = (int)($this->fDiffPreis / $this->fStepWert_arr[$this->nStep]);
        }
    }

    /**
     * Zeichnet die Aussenbox
     */
    public function zeichneAussenBox(): void
    {
        $BoxColor = \imagecolorallocate($this->image, $this->ColorBox[0], $this->ColorBox[1], $this->ColorBox[2]);

        \imageline(
            $this->image,
            $this->nAussenRahmenLinks,
            $this->nAussenRahmenOben,
            $this->nAussenRahmenRechts,
            $this->nAussenRahmenOben,
            $BoxColor
        ); // Oben
        \imageline(
            $this->image,
            $this->nAussenRahmenRechts,
            $this->nAussenRahmenOben,
            $this->nAussenRahmenRechts,
            $this->nAussenRahmenUnten,
            $BoxColor
        ); // Rechts
        \imageline(
            $this->image,
            $this->nAussenRahmenLinks,
            $this->nAussenRahmenOben,
            $this->nAussenRahmenLinks,
            $this->nAussenRahmenUnten,
            $BoxColor
        ); // Links
        \imageline(
            $this->image,
            $this->nAussenRahmenLinks,
            $this->nAussenRahmenUnten,
            $this->nAussenRahmenRechts,
            $this->nAussenRahmenUnten,
            $BoxColor
        ); // Unten
    }

    /**
     * Zeichnet für den aktuellen Artikel das Grid in die Aussenbox
     */
    public function zeichneGrid(): void
    {
        // Farben
        $GridColor = \imagecolorallocate($this->image, $this->ColorGrid[0], $this->ColorGrid[1], $this->ColorGrid[2]);
        $TextColor = \imagecolorallocate($this->image, $this->ColorText[0], $this->ColorText[1], $this->ColorText[2]);
        //$nTimestampXWert = time();
        $nTimestampXWert = $this->nMaxTimestamp;
        // Y-Achsen Ausrichtung der Beschriftung
        $nBeschriftungsEinzug = ($this->fMaxPreis > 1000)
            ? 75
            : 65;
        if ($this->nAnzahlPreise > 1) {
            // Pixel pro Schritt Y Achse
            $nPixelProSchrittY = $this->nInnenRahmenHoehe / $this->nAnzahlPreise;

            if ($this->nAnzahlTage < 6) {
                $nLoop = $this->nAnzahlTage;
                // Timestampschritt
                $nTimestampXSchritt = $this->nDiffStamp / ($this->nAnzahlTage - 1);
                // Pixel pro Schritt X Achse
                $nPixelProSchrittX = $this->nInnenRahmenBreite / ($this->nAnzahlTage - 1);
            } else {
                $nLoop = 6;
                // Timestampschritt
                $nTimestampXSchritt = $this->nDiffStamp / 6;
                // Pixel pro Schritt X Achse
                $nPixelProSchrittX = $this->nInnenRahmenBreite / 5;
            }
            // Grid X
            \imagefttext(
                $this->image,
                $this->nSchriftgroesse,
                0,
                $this->nInnenRahmenRechts - 15,
                $this->nAussenRahmenUnten + 15,
                $TextColor,
                $this->cSchriftverzeichnis . $this->cSchriftart,
                \date('j. M', $nTimestampXWert)
            );
            \imageline(
                $this->image,
                $this->nInnenRahmenRechts,
                $this->nAussenRahmenOben,
                $this->nInnenRahmenRechts,
                $this->nAussenRahmenUnten,
                $GridColor
            );

            $this->nStepX = $this->nInnenRahmenRechts;

            for ($i = 1; $i < $nLoop; $i++) {
                $this->nStepX    -= $nPixelProSchrittX;
                $nTimestampXWert -= $nTimestampXSchritt;

                \imagefttext(
                    $this->image,
                    $this->nSchriftgroesse,
                    0,
                    $this->nStepX - 15,
                    $this->nAussenRahmenUnten + 15,
                    $TextColor,
                    $this->cSchriftverzeichnis . $this->cSchriftart,
                    \date('j. M', $nTimestampXWert)
                );
                \imageline(
                    $this->image,
                    $this->nStepX,
                    $this->nAussenRahmenOben,
                    $this->nStepX,
                    $this->nAussenRahmenUnten,
                    $GridColor
                );
            }

            // Grid Y
            $this->nStepY = $this->nInnenRahmenOben;
            $price        = $this->fMaxPreis;

            \imagefttext(
                $this->image,
                $this->nSchriftgroesse,
                0,
                $this->nAussenRahmenLinks - $nBeschriftungsEinzug,
                $this->nStepY + ($this->nSchriftgroesse / 2),
                $TextColor,
                $this->cSchriftverzeichnis . $this->cSchriftart,
                \round($price, 2) . ' ' . $this->oPreisConfig->Waehrung
            );
            \imageline(
                $this->image,
                $this->nAussenRahmenLinks,
                $this->nStepY,
                $this->nAussenRahmenRechts,
                $this->nStepY,
                $GridColor
            );

            for ($i = 0; $i < $this->nAnzahlPreise; $i++) {
                $this->nStepY += $nPixelProSchrittY;
                $price        -= $this->fStepWert_arr[$this->nStep];
                \imagefttext(
                    $this->image,
                    $this->nSchriftgroesse,
                    0,
                    $this->nAussenRahmenLinks - $nBeschriftungsEinzug,
                    $this->nStepY + ($this->nSchriftgroesse / 2),
                    $TextColor,
                    $this->cSchriftverzeichnis . $this->cSchriftart,
                    \round($price, 2) . ' ' . $this->oPreisConfig->Waehrung
                );
                \imageline(
                    $this->image,
                    $this->nAussenRahmenLinks,
                    $this->nStepY,
                    $this->nAussenRahmenRechts,
                    $this->nStepY,
                    $GridColor
                );
            }
        } elseif ($this->nAnzahlPreise === 1) {
            // Grid X
            \imagefttext(
                $this->image,
                $this->nSchriftgroesse,
                0,
                ($this->nInnenRahmenLinks + $this->nInnenRahmenBreite / 2) - 15,
                $this->nAussenRahmenUnten + 15,
                $TextColor,
                $this->cSchriftverzeichnis . $this->cSchriftart,
                \date('j. M', $nTimestampXWert)
            );
            \imageline(
                $this->image,
                $this->nInnenRahmenLinks + $this->nInnenRahmenBreite / 2,
                $this->nAussenRahmenOben,
                $this->nInnenRahmenLinks + $this->nInnenRahmenBreite / 2,
                $this->nAussenRahmenUnten,
                $GridColor
            );
            // Grid Y
            \imagefttext(
                $this->image,
                $this->nSchriftgroesse,
                0,
                $this->nAussenRahmenLinks - $nBeschriftungsEinzug,
                ($this->nInnenRahmenOben + $this->nInnenRahmenHoehe / 2) + ($this->nSchriftgroesse / 2),
                $TextColor,
                $this->cSchriftverzeichnis . $this->cSchriftart,
                \round($this->fMinPreis, 2) . ' ' . $this->oPreisConfig->Waehrung
            );
        }
    }

    /**
     * Zeichnet für einen Artikel den aktuellen Preisverlauf
     */
    public function zeichnePreisverlauf(): void
    {
        // Preis am letzten X Grid Punkt
        $fXStartPreis = $this->oPreisverlaufData_arr[0]->fVKNetto;
        // X StartWert
        $nXStartWert = $this->nInnenRahmenRechts;
        // Aktueller X Wert
        $nXWertNow = $nXStartWert;
        // Farben
        $GraphColor = \imagecolorallocate(
            $this->image,
            $this->ColorGraph[0],
            $this->ColorGraph[1],
            $this->ColorGraph[2]
        );

        if (\is_array($this->oPreisverlaufData_arr) && \count($this->oPreisverlaufData_arr) > 1) {
            $nSecProPixel = $this->nDiffStamp / ($nXStartWert - $this->nStepX);
            // X Endwert
            $nXEnd = 0;
            $nYEnd = 0;
            $pvdc  = (\count($this->oPreisverlaufData_arr) - 1);
            for ($i = 0; $i < $pvdc; $i++) {
                // Hole Y Wert für den Linienanfang
                $nYWert = $this->holeYPreis($this->oPreisverlaufData_arr[$i]->fVKNetto);
                // Hole Y Wert für den Linienanfang vom nächsten Preis
                $nYWertNext   = $this->holeYPreis($this->oPreisverlaufData_arr[$i + 1]->fVKNetto);
                $nPixelBreite = ($this->oPreisverlaufData_arr[$i]->timestamp -
                        $this->oPreisverlaufData_arr[$i + 1]->timestamp) / $nSecProPixel;
                // Zeichne X Linie
                \imageline($this->image, $nXWertNow, $nYWertNext, $nXWertNow - $nPixelBreite, $nYWertNext, $GraphColor);
                // Zeichne Y Linie
                \imageline($this->image, $nXWertNow, $nYWert, $nXWertNow, $nYWertNext, $GraphColor);

                $nXEnd = $nXWertNow - $nPixelBreite;
                $nYEnd = $nYWertNext;
                // Aktueller X Wert
                $nXWertNow -= $nPixelBreite;
            }

            // Ränderspitzen
            \imageline(
                $this->image,
                $nXStartWert + 5,
                $this->holeYPreis($fXStartPreis),
                $nXStartWert,
                $this->holeYPreis($fXStartPreis),
                $GraphColor
            ); // Rechts
            \imageline($this->image, $nXEnd, $nYEnd, $nXEnd - 5, $nYEnd, $GraphColor); // Links
        } elseif (\is_array($this->oPreisverlaufData_arr) && \count($this->oPreisverlaufData_arr) === 1) {
            \imageline(
                $this->image,
                $this->nAussenRahmenLinks,
                $this->nInnenRahmenOben + $this->nInnenRahmenHoehe / 2,
                $this->nAussenRahmenRechts,
                $this->nInnenRahmenOben + $this->nInnenRahmenHoehe / 2,
                $GraphColor
            );
        }
    }

    /**
     * Berechnet zu jedem Preis aus der Datenbank, den Y Punkt
     *
     * @param float $fVKNetto
     * @return int
     */
    public function holeYPreis($fVKNetto)
    {
        $nPixelProCent = ($this->nStepY - $this->nInnenRahmenOben) / (($this->fMaxPreis - $this->fMinPreis) * 100);

        return ($this->nInnenRahmenOben +
                ($this->nStepY - $this->nInnenRahmenOben)) -
            ((($fVKNetto - $this->fMinPreis) * 100) * $nPixelProCent);
    }

    /**
     * Rechnet die gesetzten Hexwerte vom Adminmenü in Dezimalwerte um
     */
    public function berechneFarbHexNachDec(): void
    {
        if (\count($this->oConfig_arr) <= 0) {
            return;
        }
        foreach ($this->oConfig_arr as $i => $config) {
            if (\preg_match('/#[A-Fa-f0-9]{6}/', $config->cWert) == 1) {
                $decimals   = [];
                $cWertSub   = \mb_substr($config->cWert, 1);
                $decimals[] = \hexdec(\mb_substr($cWertSub, 0, 2));
                $decimals[] = \hexdec(\mb_substr($cWertSub, 2, 2));
                $decimals[] = \hexdec(\mb_substr($cWertSub, 4, 2));

                switch ($config->cName) {
                    case 'preisverlauf_hintergrundfarbe':
                        $this->ColorBackground = $decimals;
                        break;
                    case 'preisverlauf_gridfarbe':
                        $this->ColorGrid = $decimals;
                        break;
                    case 'preisverlauf_graphfarbe':
                        $this->ColorGraph = $decimals;
                        break;
                    case 'preisverlauf_boxfarbe':
                        $this->ColorBox = $decimals;
                        break;
                    case 'preisverlauf_textfarbe':
                        $this->ColorText = $decimals;
                        break;
                }
            }
        }
    }

    /**
     * Breite und Höhe sowie Schriftgröße und Paddings
     */
    public function setzeBreiteHoehe(): void
    {
        if (\count($this->oConfig_arr) <= 0) {
            return;
        }
        foreach ($this->oConfig_arr as $config) {
            switch ($config->cName) {
                case 'preisverlauf_breite':
                    $this->nBreite = (int)$config->cWert;
                    break;
                case 'preisverlauf_hoehe':
                    $this->nHoehe = (int)$config->cWert;
                    break;
                case 'preisverlauf_schriftgroesse':
                    $this->nSchriftgroesse = (int)$config->cWert;
                    break;
                case 'preisverlauf_padding_oben':
                    $this->nPolsterOben = (int)$config->cWert;
                    break;
                case 'preisverlauf_padding_links':
                    $this->nPolsterLinks = (int)$config->cWert;
                    break;
                case 'preisverlauf_padding_unten':
                    $this->nPolsterUnten = (int)$config->cWert;
                    break;
                case 'preisverlauf_padding_rechts':
                    $this->nPolsterRechts = (int)$config->cWert;
                    break;
                case 'preisverlauf_padding_x':
                    $this->nInternPolsterX = (int)$config->cWert;
                    break;
                case 'preisverlauf_padding_y':
                    $this->nInternPolsterY = (int)$config->cWert;
                    break;
            }
        }
    }

    /**
     * Berechnet signifikante Schlüsselpunkte um weitere Berechnungen zu erleichtern
     */
    public function berechneSigniPunkte(): void
    {
        // Breite und Höhe des äusseren Rahmens
        $this->nBreiteRahmen = ($this->nBreite - $this->nPolsterRechts) - $this->nPolsterLinks;
        $this->nHoeheRahmen  = ($this->nHoehe - $this->nPolsterUnten) - $this->nPolsterOben;
        // Padding vom äusseren Rahmen zum Inneren in Pixel anstatt %
        $this->nInternPolsterXPixel = ($this->nBreiteRahmen * ($this->nInternPolsterX / 100));
        $this->nInternPolsterYPixel = ($this->nHoeheRahmen * ($this->nInternPolsterY / 100));
        // Breite und Höhe vom inneren Rahmen
        $this->nInnenRahmenBreite = $this->nBreiteRahmen - (2 * $this->nInternPolsterXPixel);
        $this->nInnenRahmenHoehe  = $this->nHoeheRahmen - (2 * $this->nInternPolsterXPixel);
        // Box AussenBox
        $this->nAussenRahmenOben   = $this->nPolsterOben;
        $this->nAussenRahmenLinks  = $this->nPolsterLinks;
        $this->nAussenRahmenUnten  = $this->nHoehe - $this->nPolsterUnten;
        $this->nAussenRahmenRechts = $this->nBreite - $this->nPolsterRechts;
        // Innen Box
        $this->nInnenRahmenOben   = $this->nPolsterOben + $this->nInternPolsterYPixel;
        $this->nInnenRahmenLinks  = $this->nPolsterLinks + $this->nInternPolsterXPixel;
        $this->nInnenRahmenUnten  = $this->nHoehe + $this->nPolsterUnten - $this->nInternPolsterYPixel;
        $this->nInnenRahmenRechts = $this->nBreite - $this->nPolsterRechts - $this->nInternPolsterXPixel;
    }

    /**
     * Zeichnet den Graphen
     */
    public function zeichneGraphen(): void
    {
        $this->zeichneGrid();
        $this->zeichnePreisverlauf();
        $this->zeichneAussenBox();

        \imagecolorallocate($this->image, $this->ColorBox[0], $this->ColorBox[1], $this->ColorBox[2]);

        \header('Content-type: image/png');
        \imagepng($this->image);
        \imagedestroy($this->image);
    }
}
