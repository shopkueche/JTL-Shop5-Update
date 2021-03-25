<?php declare(strict_types=1);
/**
 * @author fm
 * @created Wed, 23 Oct 2019 15:22:00 +0200
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20191023152200
 */
class Migration_20191023152200 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add filesystem options';

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->setConfig(
            'fs_general_header',
            'General',
            CONF_FS,
            'General',
            null,
            90,
            (object)[ 'cConf' => 'N' ],
            true
        );
        $this->setConfig(
            'fs_adapter',
            'local',
            CONF_FS,
            'Adapter',
            'selectbox',
            92,
            (object)[
                'cBeschreibung' => 'Adapter',
                'inputOptions'  => [
                    'local' => 'Lokal',
                    'ftp' => 'FTP',
                    'sftp' => 'SFTP',
                ],
            ]
        );
        $this->setConfig('fs_timeout', '10', CONF_FS, 'Timeout', 'number', 93, null, true);
        $this->setConfig(
            'sftp_header',
            'SFTP Verbindung',
            CONF_FS,
            'SFTP Verbindung',
            null,
            200,
            (object)[ 'cConf' => 'N' ],
            true
        );
        $this->setConfig('sftp_hostname', 'localhost', CONF_FS, 'SFTP Hostname', 'text', 201, null, true);
        $this->setConfig('sftp_port', '22', CONF_FS, 'SFTP Port', 'number', 202, null, true);
        $this->setConfig('sftp_user', '', CONF_FS, 'SFTP Benutzer', 'text', 203, null, true);
        $this->setConfig('sftp_pass', '', CONF_FS, 'SFTP Passwort', 'pass', 204, null, true);
        $this->setConfig('sftp_privkey', '', CONF_FS, 'SFTP Private Key', 'text', 205, null, true);
        $this->setConfig('sftp_path', '/', CONF_FS, 'SFTP Pfad', 'text', 206, null, true);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->removeConfig('fs_general_header');
        $this->removeConfig('fs_adapter');
        $this->removeConfig('fs_timeout');
        $this->removeConfig('sftp_header');
        $this->removeConfig('sftp_hostname');
        $this->removeConfig('sftp_port');
        $this->removeConfig('sftp_user');
        $this->removeConfig('sftp_pass');
        $this->removeConfig('sftp_privkey');
        $this->removeConfig('sftp_path');
    }
}
