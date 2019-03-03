<?php

namespace MageArab\MegaFramework\Core;


use MageArab\MegaFramework\Traits\Singleton;

class ACF
{
    use Singleton;

    /** Unique key holder */
    private $key = 0;
    /** Unique key generator */
    private function key ($base='')
    {
        $k = $this->key++;
        return $base . $k;
    }
    /**
     * Use configuration file to register fields
     */
    public function useConfig ($path)
    {
        if (file_exists($path)) require $path;
    }
    /**
     * Register acf group
     */
    public function addGroup ($conf,$location,$keygen=null,$name='')
    {
        $key = !is_null($keygen)
            ? $keygen->generate($name,'group')
            : $this->key('group_');
        $args = array_merge(array(
            'key' => $key,
            'location' => $location
        ),$conf);
        acf_add_local_field_group($args);
        return $args['key'];
    }
    /**
     * Register acf field
     */
    public function addField ($parent,$label,$name,$type,$required=0,$conf=array(),$keygen=null)
    {
        $key = !is_null($keygen)
            ? $keygen->generate($name,'field')
            : $this->key('field_');
        $args = array_merge(array(
            'key' => $key,
            'parent' => $parent,
            'label' => $label,
            'name' => $name,
            'type' => $type
        ),$conf);
        acf_add_local_field ($args);
        return $args['key'];
    }
}