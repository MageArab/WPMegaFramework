<?php
namespace MageArab\MegaFramework\Fields;

class Field
{
    private $_id;
    private $_name;
    private $_type;
    private $_value;
    private $_extra;

    protected static $_instance;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct($field = array(), $value = '')
    {

    }

    public function render(){
        return '<input '.$this->getExtra().' id="'.$this->getId().'" name="'.$this->getName().'" type="'.$this->getType().'" value="'.$this->getValue().'">';
    }

    public function enqueue(){

    }

    public function getId()
    {
        return $this->_id;
    }

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function getType()
    {
        return $this->_type;
    }

    public function setType($type)
    {
        $this->_type = $type;
    }

    public function getValue()
    {
        return $this->_value;
    }

    public function setValue($value)
    {
        $this->_value = $value;
    }

    public function getExtra()
    {
        return $this->_extra;
    }

    public function setExtra($extra)
    {
        $this->_extra = $extra;
    }
}