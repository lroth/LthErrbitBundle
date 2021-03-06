<?php

namespace Btn\ErrbitBundle\EventListener;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Errbit\Errbit;

class ErrbitExceptionListener
{
    /** @var array */
    protected $config;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        Errbit::instance()->configure($config);
    }

    /**
     * Handle exception method
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($this->config['enabled']) {
            $exception = $event->getException();

            if (!$this->config['exceptions']['not_found_http'] && $exception instanceof NotFoundHttpException) {
                // skip NotFoundHttpException
                return;
            }

            if (!$this->config['exceptions']['resource_not_found'] && $exception instanceof ResourceNotFoundException) {
                // skip ResourceNotFoundException
                return;
            }

            // get exeption and send to errbit
            Errbit::instance()->notify($exception);
        }
    }
}
