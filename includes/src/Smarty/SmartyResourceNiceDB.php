<?php

namespace JTL\Smarty;

use JTL\DB\DbInterface;
use JTL\DB\ReturnType;
use JTL\Shop;
use Smarty_Resource_Custom;

/**
 * Class SmartyResourceNiceDB
 * @package JTL\Smarty
 */
class SmartyResourceNiceDB extends Smarty_Resource_Custom
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var DbInterface
     */
    private $db;

    /**
     * SmartyResourceNiceDB constructor.
     * @param DbInterface $db
     * @param string      $type
     */
    public function __construct(DbInterface $db, string $type = ContextType::EXPORT)
    {
        $this->db   = $db;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @param string $name
     * @param string $source
     * @param int    $mtime
     */
    public function fetch($name, &$source, &$mtime)
    {
        if ($this->type === ContextType::EXPORT) {
            $source = $this->getExportSource((int)$name);
        } elseif ($this->type === ContextType::MAIL) {
            $source = $this->getMailSource($name);
        } elseif ($this->type === ContextType::NEWSLETTER) {
            $source = $this->getNewsletterSource($name);
        } else {
            $source = '';
            Shop::Container()->getLogService()->notice('Template-Typ ' . $this->type . ' wurde nicht gefunden');
        }
    }

    /**
     * @param string $name
     * @return string
     */
    private function getNewsletterSource(string $name): string
    {
        $source = '';
        $parts  = \explode('_', $name);
        $table  = 'tnewslettervorlage';
        $row    = 'kNewsletterVorlage';
        if ($parts[0] === 'NL') {
            $table = 'tnewsletter';
            $row   = 'kNewsletter';
        }
        $newsletter = $this->db->select($table, $row, $parts[1]);
        if ($parts[2] === 'html') {
            $source = $newsletter->cInhaltHTML;
        } elseif ($parts[2] === 'text' || $parts[2] === 'plain') {
            $source = $newsletter->cInhaltText;
        }

        return $source;
    }

    /**
     * @param string $name
     * @return string
     */
    private function getMailSource(string $name): string
    {
        $pcs = \explode('_', $name);
        if (isset($pcs[0], $pcs[1], $pcs[2], $pcs[3]) && $pcs[3] === 'anbieterkennzeichnung') {
            // Anbieterkennzeichnungsvorlage holen
            $vl = $this->db->queryPrepared(
                "SELECT tevs.cContentHtml, tevs.cContentText
                    FROM temailvorlage tev
                    JOIN temailvorlagesprache tevs
                        ON tevs.kEmailVorlage = tev.kEmailvorlage
                        AND tevs.kSprache = :langID
                    WHERE tev.cModulId = 'core_jtl_anbieterkennzeichnung'",
                ['langID' => (int)$pcs[4]],
                ReturnType::SINGLE_OBJECT
            );
        } else {
            // Plugin Emailvorlage?
            $vl = $this->db->select(
                'temailvorlagesprache',
                ['kEmailvorlage', 'kSprache'],
                [(int)$pcs[1], (int)$pcs[2]]
            );
        }
        if (isset($vl->cContentHtml)) {
            if ($pcs[0] === 'html') {
                $source = $vl->cContentHtml;
            } elseif ($pcs[0] === 'text') {
                $source = $vl->cContentText;
            } else {
                $source = '';
                Shop::Container()->getLogService()->notice('Ungueltiger Emailvorlagen-Typ: ' . $pcs[0]);
            }
        } else {
            $source = '';
            Shop::Container()->getLogService()->notice(
                'Emailvorlage mit der ID ' . (int)$pcs[1] .
                ' in der Sprache ' . (int)$pcs[2] . ' wurde nicht gefunden'
            );
        }

        return $source;
    }

    /**
     * @param int $id
     * @return string
     */
    private function getExportSource(int $id): string
    {
        $exportformat = $this->db->select('texportformat', 'kExportformat', $id);

        return empty($exportformat->kExportformat)
            ? ''
            : $exportformat->cContent;
    }

    /**
     * @param string $name
     * @return int
     */
    protected function fetchTimestamp($name): int
    {
        return \time();
    }
}
