<?php

namespace MageArab\MegaFramework\Factories;

use MageArab\MegaFramework\Core\PostType;

final class PostTypeFactory
{
    public static function create($name, $slug, $args = array())
    {
        return new PostType($name, $slug, $args);
    }
}