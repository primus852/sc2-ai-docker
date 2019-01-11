<?php

namespace App\Command;

use App\Util\TelegramBot\TelegramBot;
use App\Util\TelegramBot\TelegramBotException;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckBotCommand extends Command
{
    protected static $defaultName = 'app:check-bot';
    private $em;

    /**
     * CronCheckAgentCommand constructor.
     * @param ObjectManager $em
     */
    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Check the Bot API for new Messages')
            ->addArgument('debug', InputArgument::OPTIONAL, 'Debug Flag');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $debug = $input->getArgument('debug') === 'y' ? true : false;

        if ($debug) {
            $io->note('Debug Mode Enabled');
        }

        $debug ? $io->text('Getting Update...') : null;

        try {

            /**
             * Create Telegram
             */
            $telegram = new TelegramBot();

            /**
             * Save all new Messages to the Database
             */
            $telegram->update_bot($this->em);

            $debug ? $io->text('Sending Answers...') : null;
            /**
             * Sent Answers accordingly
             */
            $telegram->send_answers($this->em);

        } catch (TelegramBotException $e) {
            $io->error('Exception: ' . $e->getMessage());
            exit();
        }

    }
}
