<?php

namespace JTL;

use stdClass;

/**
 * Class Piechart
 * @package JTL
 */
class Piechart extends Chartdata
{
    /**
     * @param string $name
     * @param array  $data
     * @return $this
     */
    public function addSerie($name, array $data): self
    {
        if ($this->_series === null) {
            $this->_series = [];
        }
        $serie           = new stdClass();
        $serie->type     = 'pie';
        $serie->name     = $name;
        $serie->data     = $data;
        $this->_series[] = $serie;

        return $this;
    }
}
