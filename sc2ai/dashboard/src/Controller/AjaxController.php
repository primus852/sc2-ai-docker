<?php

namespace App\Controller;

use primus852\ShortResponse\ShortResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Annotation\Route;

class AjaxController extends AbstractController
{
    /**
     * @Route("/_ajax/_clearLock", name="ajaxClearLock")
     */
    public function clear_lock()
    {
        $fs = new Filesystem();
        $lockFile = $this->get('kernel')->getProjectDir().'/src/Command/CronCheckAgentCommand.php.lock';

        if(!$fs->exists($lockFile)){
            return ShortResponse::success('No Lock found');
        }

        $fs->remove($lockFile);

        return ShortResponse::success('Lock cleared');

    }
}
