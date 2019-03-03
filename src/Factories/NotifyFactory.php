<?php

namespace MageArab\MegaFramework\Factories;

use MageArab\MegaFramework\Core\Notify;

final class NotifyFactory extends Notify
{
    public static function add($message, $class, $dismissible = false)
    {
        (new Notify($message, $class, $dismissible))->done();
    }

    public static function info($message, $dismissible = false)
    {
        (new Notify($message, 'info', $dismissible))->done();
    }

    public static function success($message, $dismissible = false)
    {
        (new Notify($message, 'success', $dismissible))->done();
    }

    /**
     * @param $message
     * @param bool $dismissible
     */
    public static function warning($message, $dismissible = false)
    {
        /** @var string $message */
        (new Notify($message, 'warning', $dismissible))->done();
    }

    public static function error($message, $dismissible = false)
    {
        (new Notify($message, 'error', $dismissible))->done();
    }
}