<?php
/**
 * Created by PhpStorm.
 * User: Torte
 * Date: 02.01.2019
 * Time: 18:58
 */

namespace App\Util\Dashboard;


use App\Entity\Stats;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;

class Dashboard
{

    private $em;

    /**
     * Dashboard constructor.
     * @param ObjectManager $em
     */
    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function last_seen()
    {
        /**
         * Create Criteria
         */
        $criteria = Criteria::create();
        $criteria
            ->where(Criteria::expr()->gt('id', 0))
            ->orderBy(array(
                'created' => 'DESC'
            ))
            ->setMaxResults(1);


        $stats = $this->em->getRepository(Stats::class)->matching($criteria);

        $status = 'offline';
        $status_class = 'danger';

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $now->modify('-5 minutes');
        $last = null;

        foreach ($stats as $stat) {
            $last = new \DateTime($stat->getCreated()->format('Y-m-d H:i:s'), new \DateTimeZone('UTC'));
        }

        /**
         * If last one is older than $now, set offline
         */
        if ($last !== null) {
            if ($last > $now) {
                $status = 'online';
                $status_class = 'success';
            }
        }


        return array(
            'status' => $status,
            'status_class' => $status_class,
            'last' => $last
        );
    }

    /**
     * @param int|null $limit
     * @return mixed
     */
    public function last_episodes(?int $limit = 25)
    {

        /**
         * Create Criteria
         */
        $criteria = Criteria::create();
        $criteria
            ->where(Criteria::expr()->gt('id', 0))
            ->orderBy(array(
                'created' => 'DESC'
            ))
            ->setMaxResults($limit);


        $stats = $this->em->getRepository(Stats::class)->matching($criteria);

        return $stats;

    }

    /**
     * @return int
     */
    public function total_episodes()
    {
        $total = $this->em->getRepository(Stats::class)->findAll();

        return count($total);
    }

    /**
     * @param int $limit
     * @return array
     */
    public function get_charts(?int $limit = 0)
    {

        /**
         * Create Criteria
         */
        $criteria = Criteria::create();
        $criteria
            ->where(Criteria::expr()->gt('id', 0))
            ->orderBy(array(
                'created' => 'DESC'
            ));

        /**
         * If we have a limit, apply...
         */
        if ($limit > 0) {
            $criteria->setMaxResults($limit);
        }

        $stats = $this->em->getRepository(Stats::class)->matching($criteria);

        /**
         * Init Vars
         */
        $win_str = null;
        $loss_str = null;
        $draw_str = null;
        $win = 0;
        $loss = 0;
        $draw = 0;
        $total = 0;

        /* @var $stat Stats */
        foreach ($stats as $stat) {

            $total++;

            if ($stat->getOutcome() === 1) {
                $win++;
            }

            if ($stat->getOutcome() === -1) {
                $loss++;
            }

            if ($stat->getOutcome() === 0) {
                $draw++;
            }

            $win_str .= round($win * 100 / $total, 0) . ',';
            $loss_str .= round($loss * 100 / $total, 0) . ',';
            $draw_str .= round($draw * 100 / $total, 0) . ',';

        }

        return array(
            'win' => substr($win_str, 0, -1),
            'loss' => substr($loss_str, 0, -1),
            'draw' => substr($draw_str, 0, -1),
        );

    }

    /**
     * @param int $limit
     * @return array
     */
    public function get_summary(?int $limit = 0)
    {

        /**
         * Init the result
         */
        $result = array(
            'win' => 0,
            'loss' => 0,
            'draw' => 0,
            'total' => 0,
        );

        /**
         * Create Criteria
         */
        $criteria = Criteria::create();
        $criteria
            ->where(Criteria::expr()->gt('id', 0))
            ->orderBy(array(
                'created' => 'DESC'
            ));

        /**
         * If we have a limit, apply...
         */
        if ($limit > 0) {
            $criteria->setMaxResults($limit);
        }

        $stats = $this->em->getRepository(Stats::class)->matching($criteria);

        /* @var $stat \App\Entity\Stats */
        foreach ($stats as $stat) {

            if ($stat->getOutcome() === 1) {
                $result['win']++;
            }

            if ($stat->getOutcome() === -1) {
                $result['loss']++;
            }

            if ($stat->getOutcome() === 0) {
                $result['draw']++;
            }

            $result['total']++;

        }

        $win_pct = 0;
        $loss_pct = 0;
        $draw_pct = 0;

        if ($result['total'] > 0) {
            $win_pct = round($result['win'] * 100 / $result['total'], 0);
            $loss_pct = round($result['loss'] * 100 / $result['total'], 0);
            $draw_pct = round($result['draw'] * 100 / $result['total'], 0);
        }

        return array(
            'win' => $win_pct,
            'loss' => $loss_pct,
            'draw' => $draw_pct,
        );

    }
}