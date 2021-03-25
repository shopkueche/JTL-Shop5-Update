<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20200923091400
 */
class Migration_20200923091400 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add missingToken, unknownError messages';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization(
            'ger',
            'messages',
            'missingToken',
            'Fehlerhafter Token.'
        );
        $this->setLocalization(
            'eng',
            'messages',
            'missingToken',
            'Missing token.'
        );
        $this->setLocalization(
            'ger',
            'messages',
            'unknownError',
            'Ein unbekannter Fehler trat auf.'
        );
        $this->setLocalization(
            'eng',
            'messages',
            'unknownError',
            'An unknown error occured.'
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('missingToken', 'messages');
        $this->removeLocalization('unknownError', 'messages');
    }
}
