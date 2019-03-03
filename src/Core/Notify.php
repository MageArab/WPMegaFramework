<?php

namespace MageArab\MegaFramework\Core;

use MageArab\MegaFramework\Traits;

class Notify
{
    use Traits\SmartObject;

    private $message;
    private $class;
    private $isDismissible;

    public function __construct($message, $class, $dismissible)
    {
        $this->class = $class;
        $this->message = $message;
        if ($dismissible == true) {
            $this->isDismissible = 'is-dismissible';
        }

    }

    public function done(){
        add_action('admin_notices', array($this, 'notify'));
    }

    public function notify()
    {
        echo '<div class="notice notice-' . $this->class . ' ' .$this->isDismissible . '"><p>' . $this->message . '</p></div>';
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param mixed $class
     */
    public function setClass($class): void
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getIsDismissible(): string
    {
        return $this->isDismissible;
    }

    /**
     * @param string $isDismissible
     */
    public function setIsDismissible(string $isDismissible): void
    {
        $this->isDismissible = $isDismissible;
    }
}