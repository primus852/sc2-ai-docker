<?php

namespace App\Command;

use App\Util\Dashboard\Dashboard;
use App\Util\Locker\Locker;
use App\Util\Locker\LockerException;
use App\Util\TelegramBot\TelegramBot;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CronCheckAgentCommand extends Command
{
    protected static $defaultName = 'cron:check-agent';
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

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setDescription('Check if the SC-AI-Agent is still running, if not restart it')
            ->addArgument('debug', InputArgument::OPTIONAL, 'Debug Flag');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws LockerException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $debug = $input->getArgument('debug') === 'y' ? true : false;

        if ($debug) {
            $io->note('Debug Mode Enabled');
        }

        /**
         * Create new Dashboard
         */
        try {
            $dashboard = new Dashboard($this->em);
        } catch (\Exception $e) {
            $io->error('Error Creating Dashboard: ' . $e->getMessage());
            exit();
        }

        /**
         * Get last seen of Agent
         */
        try {
            $last_seen = $dashboard->last_seen();
        } catch (\Exception $e) {
            $io->error('Could not determine last Agent Update: ' . $e->getMessage());
            exit();
        }

        /**
         * Print the Status
         */
        $debug ? $io->text('Agent is: ' . $last_seen['status']) : null;

        /**
         * Restart if offline
         */
        if ($last_seen['status'] === 'offline') {

            /**
             * Check if message was already sent
             */
            try {
                if (Locker::check_lock(__FILE__, false)) {
                    $io->error('Lockfile already exists: ' . __FILE__ . Locker::EXT);
                    exit();
                }
            } catch (LockerException $e) {
                throw new LockerException($e->getMessage());
            }

            $debug ? $io->text('Sending Message...') : null;

            try{

                /**
                 * Create Telegram
                 */
                $telegram = new TelegramBot();
                $telegram->send_to_user('Agent is down!');

                /**
                 * Create the Lockfile
                 */
                Locker::touch(__FILE__);

            }catch (\Exception $e){
                $io->error('Could not send Telegram: '.$e->getMessage());
            }

            $debug ? $io->text('Message sent') : null;

        } else {

            $debug ? $io->success('All Clear and Running...') : null;

        }

    }
}
