<?php

namespace EdwardChoHub\tagLib\cozy;

use EdwardChoHub\base\BaseTag;

class ElseifTag extends BaseTag
{
    public $args = [
        'condition' => self::ARG_FORCE,

    ];

    public function handle()
    {
        return $this->prefix("} elseif({$this->args['condition']}) {")
            ->suffix('}');
    }
}