<?php

namespace JTL\dbeS\Sync;

use JTL\dbeS\Starter;
use SimpleXMLElement;

/**
 * Class ImageUpload
 * @package JTL\dbeS\Sync
 */
final class ImageUpload extends AbstractSync
{
    /**
     * @param Starter $starter
     * @return mixed|null
     */
    public function handle(Starter $starter)
    {
        foreach ($starter->getXML(true) as $i => $item) {
            [$file, $xml] = [\key($item), \reset($item)];
            if (\strpos($file, 'images.xml') !== false) {
                $this->handleInserts(\pathinfo($file)['dirname'] . '/', $xml);
                return null;
            }
        }

        return null;
    }

    /**
     * @param string            $tmpDir
     * @param SimpleXMLElement $xml
     */
    private function handleInserts($tmpDir, SimpleXMLElement $xml): void
    {
        $items = $this->getArray($xml);
        foreach ($items as $item) {
            $tmpfile = $tmpDir . $item->kBild;
            if (!\file_exists($tmpfile)) {
                $this->logger->notice('Cannot find image: ' . $tmpfile);
                continue;
            }
            if (\copy($tmpfile, \PFAD_ROOT . \PFAD_MEDIA_IMAGE_STORAGE . $item->cPfad)) {
                $this->upsert('tbild', [$item], 'kBild');
                $this->db->update(
                    'tartikelpict',
                    'kBild',
                    (int)$item->kBild,
                    (object)['cPfad' => $item->cPfad]
                );
            } else {
                $this->logger->error(\sprintf(
                    'Copy "%s" to "%s"',
                    $tmpfile,
                    \PFAD_ROOT . \PFAD_MEDIA_IMAGE_STORAGE . $item->cPfad
                ));
            }
        }
    }

    /**
     * @param SimpleXMLElement $xml
     * @return array
     */
    private function getArray(SimpleXMLElement $xml): array
    {
        $items = [];
        foreach ($xml->children() as $child) {
            $items[] = (object)[
                'kBild' => (int)$child->attributes()->kBild,
                'cPfad' => (string)$child->attributes()->cHash
            ];
        }

        return $items;
    }
}
