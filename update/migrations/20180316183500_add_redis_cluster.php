<?php
/**
 * Add redis cluster config
 *
 * @author fm
 * @created Fri, 16 Mar 2018 18:35:00 +0100
 */

use JTL\Update\IMigration;
use JTL\Update\Migration;

/**
 * Class Migration_20180316183500
 */
class Migration_20180316183500 extends Migration implements IMigration
{
    protected $author      = 'fm';
    protected $description = 'Add redis cluster config';

    /**
     * @inheritDoc
     */
    public function up()
    {
        $this->setConfig(
            'caching_rediscluster_hosts',
            '',
            CONF_CACHING,
            'Cluster-Hosts',
            'text',
            110,
            (object)[
                'cBeschreibung'     => 'Cluster-Hosts (Name:Port, mit Komma getrennt)',
                'nStandardAnzeigen' => 0
            ]
        );
        $this->setConfig(
            'caching_rediscluster_strategy',
            'N',
            CONF_CACHING,
            'Strategie',
            'selectbox',
            111,
            (object)[
                'nStandardAnzeigen' => 0,
                'cBeschreibung'     => 'Strategie',
                'inputOptions'      => [
                    '1' => 'Alle Anfragen nur an Master',
                    '2' => 'Failover für Leseanfragen an Slaves',
                    '3' => 'Leseanfragen zwischen Mastern und Slaves zufällig verteilen',
                    '4' => 'Leseanfragen zwischen Slaves zufällig verteilen'
                ]
            ]
        );
        $this->execute(
            "INSERT INTO teinstellungenconfwerte (
                SELECT teinstellungenconf.kEinstellungenConf, 'RedisCluster', 'redisCluster', 10
                FROM teinstellungenconf
                WHERE teinstellungenconf.cWertName = 'caching_method')"
        );
    }

    /**
     * @inheritDoc
     */
    public function down()
    {
        $this->removeConfig('caching_rediscluster_hosts');
        $this->removeConfig('caching_rediscluster_strategy');
        $this->execute(
            "DELETE teinstellungenconfwerte 
                FROM teinstellungenconfwerte 
                INNER JOIN teinstellungenconf 
                    ON teinstellungenconf.kEinstellungenConf = teinstellungenconfwerte.kEinstellungenConf
                WHERE teinstellungenconf.cWertName = 'caching_method'
                    AND teinstellungenconfwerte.cWert = 'redisCluster'"
        );
    }
}
