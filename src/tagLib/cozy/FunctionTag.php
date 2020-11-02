<?php

namespace EdwardChoHub\tagLib\cozy;

use Exception;
use EdwardChoHub\base\BaseTag;
use ReflectionFunction;

class FunctionTag extends BaseTag
{
    public function handle()
    {
        //默认解析为函数，如果找不到函数则直接报错
        $args = [];
        $reflect = new ReflectionFunction($this->tagName);
        if (!$reflect->isClosure()) {
            return false;
        }
        foreach ($reflect->getParameters() as $parameter) {
            $name = $parameter->name;
            if (isset($tidyNode->attribute[$name])) {
                $args[$name] = $tidyNode->attribute[$name];
            } else {
                if (!$parameter->isDefaultValueAvailable()) {
                    return false;
                }
                $args[$name] = $parameter->getDefaultValue();
            }
        }
        return $this->prefix("$this->tagName(" . implode(',', $args) . ");");
    }
}