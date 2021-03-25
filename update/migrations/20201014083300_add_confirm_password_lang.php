<?php declare(strict_types=1);

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20201014083300
 */
class Migration_20201014083300 extends Migration implements IMigration
{
    protected $author      = 'mh';
    protected $description = 'Add confirm password lang';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setLocalization(
            'ger',
            'forgot password',
            'confirmNewPassword',
            'Neues Passwort übernehmen'
        );
        $this->setLocalization(
            'eng',
            'forgot password',
            'confirmNewPassword',
            'Confirm new password'
        );

    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeLocalization('confirmNewPassword', 'forgot password');
    }
}
