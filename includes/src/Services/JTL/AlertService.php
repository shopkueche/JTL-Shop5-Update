<?php

namespace JTL\Services\JTL;

use Illuminate\Support\Collection;
use JTL\Alert\Alert;

/**
 * Class AlertService
 * @package JTL\Services\JTL
 */
class AlertService implements AlertServiceInterface
{
    /**
    * @var Collection
    */
    private $alertList;

    /**
     * Alertservice constructor.
     */
    public function __construct()
    {
        $this->alertList = new Collection();
        $this->initFromSession();
    }

    /**
     * @inheritdoc
     */
    public function initFromSession(): void
    {
        $alerts = $_SESSION['alerts'] ?? '';

        if (!empty($alerts)) {
            foreach ($alerts as $alertSerialized) {
                $alert = \unserialize($alertSerialized, ['allowed_classes', Alert::class]);
                if ($alert !== false) {
                    $this->pushAlert($alert);
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function addAlert(string $type, string $message, string $key, array $options = null): ?Alert
    {
        if (\trim($message) === '' || \trim($type) === '' || \trim($key) === '') {
            return null;
        }
        $alert = new Alert($type, $message, $key, $options);
        $this->pushAlert($alert);

        return $alert;
    }

    /**
     * @inheritdoc
     */
    public function getAlert(string $key): ?Alert
    {
        return $this->getAlertList()->first(static function (Alert $alert) use ($key) {
            return $alert->getKey() === $key;
        });
    }

    /**
     * @inheritdoc
     */
    public function displayAlertByKey(string $key): void
    {
        if ($alert = $this->getAlert($key)) {
            $alert->display();
        }
    }

    /**
     * @inheritdoc
     */
    public function getAlertList(): Collection
    {
        return $this->alertList;
    }

    /**
     * @inheritdoc
     */
    public function alertTypeExists(string $type): bool
    {
        return \count($this->getAlertList()->filter(static function (Alert $alert) use ($type) {
            return $alert->getType() === $type;
        })) > 0;
    }

    /**
     * @inheritdoc
     */
    public function removeAlertByKey(string $key): void
    {
        $key = $this->getAlertList()->search(static function (Alert $alert) use ($key) {
            return $alert->getKey() === $key;
        });
        if ($key !== false) {
            $this->getAlertList()->pull($key);
        }
    }

    /**
     * @param Alert $alert
     */
    private function pushAlert(Alert $alert): void
    {
        $this->removeAlertByKey($alert->getKey());
        $this->getAlertList()->push($alert);
    }
}
