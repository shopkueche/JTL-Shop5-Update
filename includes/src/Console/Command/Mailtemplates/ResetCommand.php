<?php declare(strict_types=1);

namespace JTL\Console\Command\Mailtemplates;

use JTL\Console\Command\Command;
use JTL\DB\ReturnType;
use JTL\Mail\Hydrator\TestHydrator;
use JTL\Mail\Mail\Mail;
use JTL\Mail\Mailer;
use JTL\Mail\Renderer\SmartyRenderer;
use JTL\Mail\Template\TemplateFactory;
use JTL\Mail\Validator\NullValidator;
use JTL\Shop;
use JTL\Shopsetting;
use JTL\Smarty\MailSmarty;
use JTL\Mail\Admin\Controller;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ResetCommand
 * @package JTL\Console\Command\Mailtemplates
 */
class ResetCommand extends Command
{
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this->setName('mailtemplates:reset')
            ->setDescription('reset all mailtemplates');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db         = Shop::Container()->getDB();
        $settings   = Shopsetting::getInstance();
        $renderer   = new SmartyRenderer(new MailSmarty($db));
        $hydrator   = new TestHydrator($renderer->getSmarty(), $db, $settings);
        $validator  = new NullValidator();
        $mailer     = new Mailer($hydrator, $renderer, $settings, $validator);
        $mail       = new Mail();
        $factory    = new TemplateFactory($db);
        $controller = new Controller($db, $mailer, $factory);
        $io         = $this->getIO();

        $templates = $db->queryPrepared(
            'SELECT DISTINCT kEmailVorlage FROM temailvorlagesprache',
            [],
            ReturnType::ARRAY_OF_OBJECTS
        );

        $count = 0;
        foreach ($templates as $template) {
            $controller->resetTemplate((int)$template->kEmailVorlage);
            $count++;
        }
        $io->writeln('<info>' . $count. ' templates has been reset.</info>');
    }
}
