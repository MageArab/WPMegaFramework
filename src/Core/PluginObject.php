<?php

namespace MageArab\MegaFramework\Core;

use MageArab\MegaFramework\Traits;

abstract class PluginObject
{
    use Traits\SmartObject;

    public $textDomain = 'default';
    protected $name;
    protected $slug;

    public function getName()
    {
        return __($this->name, $this->textDomain);
    }

    public function get($var)
    {
        if (!$this->$var) {
            return false;
        }
        return $this->$var;
    }

    public function getSlug()
    {
        if (!$this->slug) {
            return false;
        }
        return $this->slug;
    }
}