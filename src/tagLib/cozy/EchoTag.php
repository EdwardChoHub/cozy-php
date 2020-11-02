<?php


namespace EdwardChoHub\tagLib\cozy;


use EdwardChoHub\base\BaseTag;

class EchoTag extends BaseTag
{
    public $args = [
        'arg' => self::ARG_FORCE,
    ];

    public function handle()
    {
        return $this->prefix("echo {$this->args['arg']};");
    }
}