<?php declare(strict_types=1);

namespace JTL\Plugin\Admin\Installation;

use stdClass;

/**
 * Class InstallationResponse
 * @package JTL\Plugin\Admin\Installation
 */
class InstallationResponse
{
    public const STATUS_OK = 'OK';

    public const STATUS_FAILED = 'FAILED';

    /**
     * @var string
     */
    private $status = self::STATUS_OK;

    /**
     * @var string|null
     */
    private $errorMessage;

    /**
     * @var string|null
     */
    private $dir_name;

    /**
     * @var string|null
     */
    private $path;

    /**
     * @var array
     */
    private $files_unpacked = [];

    /**
     * @var array
     */
    private $files_failed = [];

    /**
     * @var array
     */
    private $messages = [];

    /**
     * @var stdClass
     */
    private $html;

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return InstallationResponse
     */
    public function setStatus(string $status): InstallationResponse
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * @param string|null $errorMessage
     * @return InstallationResponse
     */
    public function setError(?string $errorMessage): InstallationResponse
    {
        $this->errorMessage = $errorMessage;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDirName(): ?string
    {
        return $this->dir_name;
    }

    /**
     * @param string|null $dir_name
     * @return InstallationResponse
     */
    public function setDirName(?string $dir_name): InstallationResponse
    {
        $this->dir_name = $dir_name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param string|null $path
     * @return InstallationResponse
     */
    public function setPath(?string $path): InstallationResponse
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return array
     */
    public function getFilesUnpacked(): array
    {
        return $this->files_unpacked;
    }

    /**
     * @param array $files_unpacked
     * @return InstallationResponse
     */
    public function setFilesUnpacked(array $files_unpacked): InstallationResponse
    {
        $this->files_unpacked = $files_unpacked;

        return $this;
    }

    /**
     * @param string $file
     * @return InstallationResponse
     */
    public function addFileUnpacked(string $file): InstallationResponse
    {
        $this->files_unpacked[] = $file;

        return $this;
    }

    /**
     * @return array
     */
    public function getFilesFailed(): array
    {
        return $this->files_failed;
    }

    /**
     * @param array $files_failed
     * @return InstallationResponse
     */
    public function setFilesFailed(array $files_failed): InstallationResponse
    {
        $this->files_failed = $files_failed;

        return $this;
    }

    /**
     * @param string $file
     * @return InstallationResponse
     */
    public function addFileFailed(string $file): InstallationResponse
    {
        $this->files_failed[] = $file;

        return $this;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * @param array $messages
     * @return InstallationResponse
     */
    public function setMessages(array $messages): InstallationResponse
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * @param string $message
     * @return InstallationResponse
     */
    public function addMessage(string $message): InstallationResponse
    {
        $this->messages[] = $message;

        return $this;
    }

    /**
     * @return stdClass
     */
    public function getHtml(): stdClass
    {
        return $this->html;
    }

    /**
     * @param stdClass $html
     * @return InstallationResponse
     */
    public function setHtml(stdClass $html): InstallationResponse
    {
        $this->html = $html;

        return $this;
    }

    /**
     * @return false|string
     */
    public function toJson()
    {
        return \json_encode(\get_object_vars($this));
    }
}
