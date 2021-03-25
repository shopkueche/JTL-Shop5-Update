<?php declare(strict_types=1);

namespace JTL\Filesystem;

use League\Flysystem\Adapter\Ftp;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Sftp\SftpAdapter;

/**
 * Class AdapterFactory
 * @package JTL\Filesystem
 */
class AdapterFactory
{

    /**
     * @var array
     */
    private $config;

    /**
     * AdapterFactory constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return AdapterInterface
     */
    public function getAdapter(): AdapterInterface
    {
        switch ($this->config['fs_adapter'] ?? $this->config['fs']['fs_adapter']) {
            case 'ftp':
                return new Ftp($this->getFtpConfig());
            case 'sftp':
                return new SftpAdapter($this->getSftpConfig());
            case 'local':
            default:
                return new Local(\PFAD_ROOT);
        }
    }

    /**
     * @param string $adapter
     */
    public function setAdapter(string $adapter): void
    {
        $this->config['fs_adapter'] = $adapter;
    }

    /**
     * @param array $config
     */
    public function setFtpConfig(array $config): void
    {
        $this->config = \array_merge($this->config, $config);
    }

    /**
     * @param array $config
     */
    public function setSftpConfig(array $config): void
    {
        $this->config = \array_merge($this->config, $config);
    }

    /**
     * @return array
     */
    private function getFtpConfig(): array
    {
        return [
            'host'                 => $this->config['ftp_hostname'],
            'port'                 => $this->config['ftp_port'],
            'username'             => $this->config['ftp_user'],
            'password'             => $this->config['ftp_pass'],
            'ssl'                  => (int)$this->config['ftp_ssl'] === 1,
            'root'                 => \rtrim($this->config['ftp_path'], '/') . '/',
            'timeout'              => $this->config['fs_timeout'],
            'passive'              => true,
            'ignorePassiveAddress' => false
        ];
    }

    /**
     * @return array
     */
    public function getSftpConfig(): array
    {
        return [
            'host'          => $this->config['sftp_hostname'],
            'port'          => $this->config['sftp_port'],
            'username'      => $this->config['sftp_user'],
            'password'      => $this->config['sftp_pass'],
            'privateKey'    => $this->config['sftp_privkey'],
            'root'          => \rtrim($this->config['sftp_path'], '/') . '/',
            'timeout'       => $this->config['fs_timeout'],
            'directoryPerm' => 0755
        ];
    }
}
