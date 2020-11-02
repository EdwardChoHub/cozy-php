<?php

namespace EdwardChoHub\base;

abstract class BaseTag
{
    //强制入参
    const ARG_FORCE = 'base_tag_arg_force_9ca5d13dd6f1be4867e4d80760392058';

    /** @var $tidyNode \tidyNode */
    public $tidyNode;
    /** @var $tagName string */
    public $tagName;

    /**
     * @var array 入参集合
     */
    public $args = [];

    /**
     * @var string 前缀
     */
    private $prefix = '';

    /**
     * @var string 后缀
     */
    private $suffix = '';

    public function __construct($args = []){
        $this->args;
    }


    protected function prefix($prefix){
        $this->prefix = $prefix;
        return $this;
    }

    protected function suffix($suffix){
        $this->prefix = $suffix;
        return $this;
    }

    public function getPrefix(){
        return $this->prefix;
    }

    public function getSuffix(){
        return $this->suffix;
    }

    /**
     * 处理函数
     * @return $this
     */
    abstract public function handle();

    public function __set($name, $value){
        $this->$name = $value;
        return $this;
    }

    protected function php($content)
    {
        return "<?php {$content} ?>";
    }
}