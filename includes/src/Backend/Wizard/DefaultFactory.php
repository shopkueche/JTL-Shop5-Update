<?php declare(strict_types=1);

namespace JTL\Backend\Wizard;

use Illuminate\Support\Collection;
use JTL\Backend\AdminAccount;
use JTL\Backend\Wizard\Steps\EmailSettings;
use JTL\Backend\Wizard\Steps\GeneralSettings;
use JTL\Backend\Wizard\Steps\LegalPlugins;
use JTL\Backend\Wizard\Steps\PaymentPlugins;
use JTL\DB\DbInterface;
use JTL\L10n\GetText;
use JTL\Services\JTL\AlertServiceInterface;

/**
 * Class DefaultFactory
 * @package JTL\Backend\Wizard
 */
final class DefaultFactory
{
    /**
     * @var Collection
     */
    private $steps;

    /**
     * DefaultFactory constructor.
     * @param DbInterface           $db
     * @param GetText               $getText
     * @param AlertServiceInterface $alertService
     * @param AdminAccount          $adminAccount
     */
    public function __construct(
        DbInterface $db,
        GetText $getText,
        AlertServiceInterface $alertService,
        AdminAccount $adminAccount
    ) {
        $getText->loadConfigLocales();
        $getText->loadAdminLocale('pages/wizard');

        $this->steps = new Collection();
        $this->steps->push(new GeneralSettings($db, $alertService));
        $this->steps->push(new EmailSettings($db, $alertService, $adminAccount));
        $this->steps->push(new LegalPlugins($db, $alertService));
        $this->steps->push(new PaymentPlugins($db, $alertService));
    }

    /**
     * @return Collection
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    /**
     * @param Collection $steps
     */
    public function setSteps(Collection $steps): void
    {
        $this->steps = $steps;
    }
}
