<?php

namespace JTL\dbeS\Sync;

use JTL\DB\ReturnType;
use JTL\dbeS\Starter;
use JTL\Helpers\Text;
use stdClass;

/**
 * Class Brocken
 *
 * @package JTL\dbeS\Sync
 */
final class Brocken extends AbstractSync
{
    /**
     * @param Starter $starter
     * @return mixed|null
     */
    public function handle(Starter $starter)
    {
        $input = Text::filterXSS($starter->getPostData('b'));
        $data  = $this->db->query(
            'SELECT cBrocken
                FROM tbrocken
                ORDER BY dErstellt DESC
                LIMIT 1',
            ReturnType::SINGLE_OBJECT
        );
        if (empty($data->cBrocken)) {
            $data            = new stdClass();
            $data->cBrocken  = $input;
            $data->dErstellt = 'NOW()';
            $this->db->insert('tbrocken', $data);
        } elseif (isset($data->cBrocken) && $data->cBrocken !== $input && \strlen($data->cBrocken) > 0) {
            $this->db->update(
                'tbrocken',
                'cBrocken',
                $data->cBrocken,
                (object)['cBrocken' => $input, 'dErstellt' => 'NOW()']
            );
        }
        $this->cache->flushTags([\CACHING_GROUP_CORE]);

        return null;
    }
}
