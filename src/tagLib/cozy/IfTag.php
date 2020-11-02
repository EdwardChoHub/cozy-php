<?php

namespace EdwardChoHub\tagLib\cozy;

use EdwardChoHub\base\BaseTag;

class IfTag extends BaseTag
{
    public $args = [
        'condition' => self::ARG_FORCE,
    ];

    public function handle()
    {
        return $this->prefix("if ({$this->args['condition']}){")
            ->suffix('}');
    }
}