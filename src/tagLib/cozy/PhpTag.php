<?php


namespace EdwardChoHub\tagLib\cozy;


use EdwardChoHub\base\BaseTag;

/** 自定义 */
class PhpTag extends BaseTag
{
    public $args = [];

    public function handle()
    {
        return $this;
    }
}