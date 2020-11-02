<?php


namespace EdwardChoHub\tagLib\cozy;


use EdwardChoHub\base\BaseTag;

class SetTag extends BaseTag
{
    public $args = [
        'name' => self::ARG_FORCE,
        'value' => null,
    ];

    public function handle()
    {
        return $this->prefix("{$this->args['name']} = {$this->args['value']};");
    }
}