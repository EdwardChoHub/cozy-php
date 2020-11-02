<?php

namespace EdwardChoHub\tagLib\cozy;

use EdwardChoHub\base\BaseTag;

class ForeachTag extends BaseTag
{
    public $args = [
        'name' => self::ARG_FORCE,
        'key' => 'key',
        'value' => 'value',
    ];

    public function handle()
    {
        return $this->prefix("foreach({$this->args['name']} as {$this->args['key']} => {$this->args['value']}){")
            ->suffix('}');
    }
}