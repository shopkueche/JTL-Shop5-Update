<?php

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20201014154100
 */
class Migration_20201014154100 extends Migration implements IMigration
{
    protected $author      = 'dr';
    protected $description = 'Add video permit lang';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization(
            'ger',
            'global',
            'allowConsentYouTube',
            'YouTube-Videos zulassen'
        );
        $this->setLocalization(
            'eng',
            'global',
            'allowConsentYouTube',
            'Permit YouTube videos'
        );
        $this->setLocalization(
            'ger',
            'global',
            'allowConsentVimeo',
            'Vimeo-Videos zulassen'
        );
        $this->setLocalization(
            'eng',
            'global',
            'allowConsentVimeo',
            'Permit Vimeo videos'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeConfig('allowConsentYouTube');
        $this->removeConfig('allowConsentVimeo');
    }
}
