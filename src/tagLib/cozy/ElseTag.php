<?php

namespace EdwardChoHub\tagLib\cozy;

use EdwardChoHub\base\BaseTag;

class ElseTag extends BaseTag
{
    public $args = [
        'condition' => self::ARG_FORCE,

    ];

    public function handle()
    {
        return $this->prefix("}");
    }
}