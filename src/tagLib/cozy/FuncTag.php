<?php

namespace EdwardChoHub\tagLib\cozy;

use Exception;
use EdwardChoHub\base\BaseTag;
use ReflectionFunction;

class FuncTag extends BaseTag
{
    public $args = [
        'name' => self::ARG_FORCE,
        'args' => '',
    ];

    public function handle()
    {

        return $this->prefix("{$this->args}({$this->args['args']})");
    }
}