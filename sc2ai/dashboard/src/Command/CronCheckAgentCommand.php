<?php

namespace App\Command;

use App\Util\Dashboard\Dashboard;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CronCheckAgentCommand extends Command
{
    protected static $defaultName = 'cron:check-agent';
    private $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Check if the SC-AI-Agent is still running, if not restart it')
            ->addArgument('debug', InputArgument::OPTIONAL, 'Debug Flag');
    }

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

            $debug ? $io->text('Trying to Restart...') : null;

            $process = Process::fromShellCommandline(
                'python3 -m pysc2.bin.agent  --map Simple64  --agent agent.refined.SparseAgent  --agent_race terran  --max_agent_steps 0 --norender',
                '/sc2ai/agent'
            );

            try {
                $process->mustRun();
                $debug ? $io->text('Agent started...') : null;
            } catch (ProcessFailedException $e) {
                $io->error('Error Running Agent: ' . $e->getMessage());
                exit();
            }

            $debug ? $io->text('Agent should run again now') : null;

        } else {

            $debug ? $io->success('All Clear and Running...') : null;

        }

    }
}
