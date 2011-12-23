<?php
/*
 * A cross-platform inter-process mutex. Designed specifically for use in 
 * preventing race condition attacks:
 * https://defuse.ca/race-conditions-in-web-applications.htm
 *
 * This code is explcitly placed into the public domain by Defuse Cyber-Security. 
 * You are free to use it for any purpose whatsoever.
 *
 * Always test your implementation to make sure the attack is being prevented!
 * If you have multiple servers processing requests, a simple mutex like this
 * will NOT prevent race condition attacks.
 *
 * Example:
 * require_once('mutex.php');
 * function withdraw($amount)
 * {
 *     $mutex = new Mutex(1234);
 *     if($mutex->lock())
 *     {
 *         $balance = getBalance();
 *         if($amount <= $balance)
 *         {
 *             $balance = $balance - $amount;
 *             echo "You have withdrawn: $amount <br />";
 *             setBalance($balance);
 *         }
 *         else
 *         {
 *             echo "Insufficient funds.";
 *         }
 *         $mutex->unlock();
 *     }
 * }
 */

// Change this and keep its value secret if you're really paranoid.
define("SEM_SALT", "I1DeeWAFaqhnepP9DOxnnK");
// Where to keep the lock files if we don't have the System V semaphore functions.
define("SEM_DIR", "C:\\"); // MUST end in a slash.
define("HAVE_SYSV", function_exists('sem_get'));

class Mutex
{
    private $semaphore;
    private $locked;

    /*
     * $key - The mutex identifier.
     * $key can be anything that reliably casts to a string.
     */
    function __construct($key)
    {
        // Paranoia says: Do not let the client specify the actual key.
        $key = hexdec(substr(sha1(SEM_SALT . $key, false), 0, PHP_INT_SIZE * 2 - 1));
        $this->locked = FALSE;

        if(HAVE_SYSV)
        {
            $this->semaphore = sem_get($key, 1);
        }
        else
        {
            $lockfile = SEM_DIR . "{$key}.sem";
            $this->semaphore = fopen($lockfile, 'w+');
        }
    }

    /*
     * Locks the mutex. If another thread/process has a lock on the mutex,
     * this call will block until it is unlocked.
     */
    function lock()
    {
        if($this->locked)
        {
            trigger_error("Mutex is already locked", E_USER_ERROR);
            return;
        }

        if(HAVE_SYSV)
            $res = sem_acquire($this->semaphore);
        else
            $res = flock($this->semaphore, LOCK_EX);

        if($res)
        {
            $this->locked = TRUE;
            return TRUE;
        }
        else
            return FALSE;
    }

    /*
     * Unlocks the mutex.
     */
    function unlock()
    {
        if(!$this->locked)
        {
            trigger_error("Mutex is not locked", E_USER_ERROR);
            return;
        }

        if(HAVE_SYSV)
            $res = sem_release($this->semaphore);
        else
        {
            $res = flock($this->semaphore, LOCK_UN);
        }

        if($res)
        {
            $this->locked = FALSE;
            return TRUE;
        }
        else
            return FALSE;
    }

    /*
     * Removes the mutex from the system.
     */
    function remove()
    {
        if($this->locked)
        {
            trigger_error("Trying to delete a locked mutex", E_USER_ERROR);
            return;
        }

        if(HAVE_SYSV)
            sem_remove($this->semaphore);
        else
            unlink($this->semaphore);
    }

    function __destruct()
    {
        if($this->locked)
            trigger_error("Semaphore is still locked when being destructed!", E_USER_ERROR);

        if(!HAVE_SYSV)
        {
            fclose($this->semaphore);
        }
    }
}

?>
