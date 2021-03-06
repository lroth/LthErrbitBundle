<?php

namespace Btn\ErrbitBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Errbit\Errbit;

class ErrbitShutdownListener
{
    /** @var boolean */
    protected $enabled;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->enabled = $config['enabled'];
        Errbit::instance()->configure($config);
    }

    /**
     * Register a function for execution on shutdown
     *
     * @param Symfony\Component\HttpKernel\Event\FilterControllerEvent $event
     */
    public function register(FilterControllerEvent $event)
    {
        if ($this->enabled) {
            register_shutdown_function(array($this, 'onShutdown'));
        }
    }

    /**
     * Handles the PHP shutdown event.
     *
     * This event exists almost solely to provide a means to catch and log errors that might have been
     * otherwise lost when PHP decided to die unexpectedly.
     */
    public function onShutdown()
    {
        // Get the last error if there was one, if not, let's get out of here.
        if (!$error = error_get_last()) {
            return;
        }

        $fatal  = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR);
        if (!in_array($error['type'], $fatal)) {
            return;
        }

        $message   = '[Shutdown Error]: %s';
        $message   = sprintf($message, $error['message']);
        $exception = new \ErrorException($message, $error['type'], 0, $error['file'], $error['line']);
        Errbit::instance()->notify($exception);
    }
}
