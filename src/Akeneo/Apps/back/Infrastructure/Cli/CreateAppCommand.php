<?php

declare(strict_types=1);

namespace Akeneo\Apps\Infrastructure\Cli;

use Akeneo\Apps\Application\Command\CreateAppHandler;
use Akeneo\Apps\Application\Query\FindAnAppHandler;
use Akeneo\Apps\Application\Query\FindAnAppQuery;
use Akeneo\Apps\Domain\Model\ValueObject\FlowType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command creates a new App with its pair of client id / secret for the web API.
 *
 * It's used for our benchmarks stack and other internal applications.
 *
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateAppCommand extends Command
{
    protected static $defaultName = 'akeneo:app:create';

    /** @var CreateAppHandler */
    private $createAppHandler;
    /** @var FindAnAppHandler */
    private $findAnAppHandler;

    public function __construct(CreateAppHandler $createAppHandler, FindAnAppHandler $findAnAppHandler)
    {
        parent::__construct();

        $this->createAppHandler = $createAppHandler;
        $this->findAnAppHandler = $findAnAppHandler;
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates a new pair of client id / secret for the web API')
            ->addArgument(
                'label',
                InputArgument::REQUIRED,
                'Sets a label to ease the administration of client ids.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $label = $input->getArgument('label');

        $command = new \Akeneo\Apps\Application\Command\CreateAppCommand($label, $label, FlowType::OTHER);
        $this->createAppHandler->handle($command);

        $query = new FindAnAppQuery($label);
        $app = $this->findAnAppHandler->handle($query);

        $output->writeln([
            'A new client has been added.',
            sprintf('client_id: <info>%s</info>', $app->clientId()),
            sprintf('secret: <info>%s</info>', $app->secret()),
        ]);

        return 0;
    }
}
