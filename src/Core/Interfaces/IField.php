<?php

namespace MageArab\MegaFramework\Interfaces;


interface IField
{
    public function __construct($field = array(), $value = '');
    public function render();
    public function enqueue();
    public function getExtra();
    public function setExtra($extra);
}