<?php

namespace App\Controller;

use App\Entity\Stats;
use App\Util\Dashboard\Dashboard;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default")
     */
    public function index()
    {

        $dashboard = new Dashboard($this->getDoctrine()->getManager());

        $totals = $dashboard->get_summary();
        $last = $dashboard->get_summary(100);

        $chart_totals = $dashboard->get_charts();
        $chart_last = $dashboard->get_charts(100);

        try{
            $last_seen = $dashboard->last_seen();
            $last_episodes = $dashboard->last_episodes();
        }catch (\Exception $e){
            throw new NotFoundHttpException($e->getMessage());
        }

        # $fs = new Filesystem();
        # $lockFile = $this->get('kernel')->getProjectDir().'/src/Command/CronCheckAgentCommand.php.lock';

        # $hasLock = $fs->exists($lockFile) ? true : false;

        return $this->render('default/index.html.twig', array(
            'controller_name' => 'Stats',
            'total' => array(
                'win' => $totals['win'],
                'loss' => $totals['loss'],
                'draw' => $totals['draw'],
            ),
            'last' => array(
                'win' => $last['win'],
                'loss' => $last['loss'],
                'draw' => $last['draw'],
            ),
            'chart_total' => array(
                'win' => $chart_totals['win'],
                'loss' => $chart_totals['loss'],
                'draw' => $chart_totals['draw'],
                'score' => $chart_totals['score'],
            ),
            'chart_last' => array(
                'win' => $chart_last['win'],
                'loss' => $chart_last['loss'],
                'draw' => $chart_last['draw'],
                'score' => $chart_last['score'],
            ),
            'last_seen' => $last_seen,
            'last_episodes' => $last_episodes,
            'episodes' => $dashboard->total_episodes(),
            # 'locked' => $hasLock,
        ));
    }
}
