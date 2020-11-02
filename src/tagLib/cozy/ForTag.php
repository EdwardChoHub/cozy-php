<?php

namespace EdwardChoHub\tagLib\cozy;

use EdwardChoHub\base\BaseTag;

class ForTag extends BaseTag
{
    public $args = [
        'name' => self::ARG_FORCE,
        'start' => 0,
        'end' => self::ARG_FORCE,
        'step' => 1,
    ];

    public function handle()
    {
        $prefix = sprintf(
            'for({%s}={%s};{%s}<{%s};{%s}={%s}+{%s}){',
            $this->args['name'],
            $this->args['start'],
            $this->args['name'],
            $this->args['end'],
            $this->args['name'],
            $this->args['name'],
            $this->args['step']
        );
        return $this->prefix($prefix)
            ->suffix('}');
    }
}