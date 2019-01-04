<?php

namespace App\Controller;

use App\Entity\Stats;
use App\Util\Dashboard\Dashboard;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            ),
            'chart_last' => array(
                'win' => $chart_last['win'],
                'loss' => $chart_last['loss'],
                'draw' => $chart_last['draw'],
            ),
            'last_seen' => $dashboard->last_seen(),
            'episodes' => $dashboard->total_episodes(),
        ));
    }
}