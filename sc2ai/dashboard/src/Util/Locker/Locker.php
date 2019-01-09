<?php
namespace App\Util\Locker;


use Symfony\Component\Filesystem\Filesystem;

class Locker
{

    const MAX_AGE = 5;
    const EXT = '.lock';

    /**
     * @param string $file
     * @param bool $force
     * @return bool
     * @throws LockerException
     */
    public static function check_lock(string $file, bool $force)
    {

        $fs = new Filesystem();
        if (!$force) {
            if ($fs->exists($file . '.lock')) {

                $last_update = \DateTime::createFromFormat('Y-m-d H:i:s', date("Y-m-d H:i:s", filemtime($file . '.lock')));
                try{
                $now = new \DateTime();
                }catch(\Exception $e){
                    throw new LockerException('Invalid Datetime');
                }
                $age = $now->diff($last_update);

                /**
                 * If the LockFile is old, sth might have happened, delete it
                 */
                if (self::in_minutes($age) > self::MAX_AGE) {
                    $fs->remove($file . self::EXT);
                    return false;
                }
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @param string $file
     */
    public static function touch(string $file)
    {
        $fs = new Filesystem();
        $fs->touch($file . self::EXT);
    }

    /**
     * @param string $file
     */
    public static function remove(string $file)
    {
        $fs = new Filesystem();

        if ($fs->exists($file . self::EXT)) {
            $fs->remove($file . self::EXT);
        }
    }

    /**
     * @param \DateInterval $interval
     * @return float|int
     */
    private static function in_minutes(\DateInterval $interval)
    {
        $minutes = $interval->days * 24 * 60;
        $minutes += $interval->h * 60;
        $minutes += $interval->i;

        return $minutes;
    }

}