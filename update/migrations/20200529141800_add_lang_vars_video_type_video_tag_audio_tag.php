<?php
/**
 * Add lang var videoTypeNotSupported
 *
 * @author je
 * @created Fr, 29 May 2020 14:18:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200529141800
 */
class Migration_20200529141800 extends Migration implements IMigration
{
    protected $author      = 'je';
    protected $description = 'Add lang var videoTypeNotSupported, videoTagNotSupported and audioTagNotSupported';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization('ger', 'errorMessages', 'videoTypeNotSupported', 'Dieses Video kann nicht angezeigt werden. Folgende Formate werden unterstützt: .mp4, .ogg und .webm .');
        $this->setLocalization('eng', 'errorMessages', 'videoTypeNotSupported', 'This video cannot be played. Following video types are supported: .mp4, .ogg and .webm .');
        $this->setLocalization('ger', 'errorMessages', 'videoTagNotSupported', 'Das HTML5 <video> Tag wird von Ihrem Browser nicht unterstützt.');
        $this->setLocalization('eng', 'errorMessages', 'videoTagNotSupported', 'Your browser does not support the HTML5 <video> tag.');
        $this->setLocalization('ger', 'errorMessages', 'audioTagNotSupported', 'Das HTML5 <audio> Tag wird von Ihrem Browser nicht unterstützt.');
        $this->setLocalization('eng', 'errorMessages', 'audioTagNotSupported', 'Your browser does not support the HTML5 <audio> tag.');
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('videoTypeNotSupported');
        $this->removeLocalization('videoTagNotSupported');
        $this->removeLocalization('audioTagNotSupported');
    }
}
