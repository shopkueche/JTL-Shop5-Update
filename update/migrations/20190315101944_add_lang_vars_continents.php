<?php
/**
 * add_lang_vars_continents
 *
 * @author mh
 * @created Fri, 15 Mar 2019 10:19:44 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20190315101944
 */
class Migration_20190315101944 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add lang vars continents';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'global', 'Europa', 'Europa');
        $this->setLocalization('eng', 'global', 'Europa', 'Europe');
        $this->setLocalization('ger', 'global', 'Asien', 'Asien');
        $this->setLocalization('eng', 'global', 'Asien', 'Asia');
        $this->setLocalization('ger', 'global', 'Nordamerika', 'Nordamerika');
        $this->setLocalization('eng', 'global', 'Nordamerika', 'North America');
        $this->setLocalization('ger', 'global', 'Suedamerika', 'Suedamerika');
        $this->setLocalization('eng', 'global', 'Suedamerika', 'South America');
        $this->setLocalization('ger', 'global', 'Ozeanien', 'Ozeanien');
        $this->setLocalization('eng', 'global', 'Ozeanien', 'Oceania');
        $this->setLocalization('ger', 'global', 'Afrika', 'Afrika');
        $this->setLocalization('eng', 'global', 'Afrika', 'Africa');
        $this->setLocalization('ger', 'global', 'Antarktis', 'Antarktis');
        $this->setLocalization('eng', 'global', 'Antarktis', 'Antarctica');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('Europa');
        $this->removeLocalization('Asien');
        $this->removeLocalization('Nordamerika');
        $this->removeLocalization('Suedamerika');
        $this->removeLocalization('Ozeanien');
        $this->removeLocalization('Afrika');
        $this->removeLocalization('Antarctica');
    }
}
