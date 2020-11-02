<?php

namespace EdwardChoHub\tagLib\cozy;

use EdwardChoHub\base\BaseTag;

class RequireOne extends BaseTag
{
    public $args = [
        'ext' => 'html',
        'file' => self::ARG_FORCE,
    ];

    public function handle()
    {
        return $this->prefix("require_once '{$this->args['file']}.{$this->args['ext']}'");
    }
}