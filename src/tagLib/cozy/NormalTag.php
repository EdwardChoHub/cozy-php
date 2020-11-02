<?php


namespace EdwardChoHub\tagLib\cozy;


use EdwardChoHub\base\BaseTag;

/**
 * 普通标签处理类(处理携带动态的属性)
 * Class NormalTag
 * @package minqCloud\tagLib\cozy
 */
class NormalTag extends BaseTag
{

    public function handle()
    {
        //非php模板标签
        $prefix = "<{$this->tidyNode->name} ";
        $attributes = [];
        foreach ($this->tidyNode->attribute as $key => $value) {
            $names = explode(':', $key);
            if(count($names) == 1){
                //如果已经有值则该值作为默认值
                if (!isset($attributes[$key])) {
                    $attributes[$key] = $value;
                } else {
                    $attributes[$key] = str_replace("''", $value, $attributes[$key]);
                }
            } else {
                array_shift($names);
                $attrName = array_shift($names);
                switch (count($names)) {
                    case 0:
                        //比如php:disabled 等于 php:disabled:disabled
                        array_push($keys, $attrName);
                    case 1:
                        //php:class:layui-btn
                        if (!isset($attributes[$names])) {
                            $attributes[$names] = '';
                        }
                        $attrVal = array_shift($names);
                        if ($names == 'CLASS') {
                            $attributes[$names] .= $this->php("echo {$value}?{$attrVal}:'';");
                        } else {
                            $prefix .= $this->php("echo {$value}?'{$attrName}=\"$attrVal\"';");
                        }
                        break;
                    case 2:
                        /** php:style:width:80px='condition' 组装后 style = '<?php echo condition?'width:80px;':'' ?>' */
                        if (!isset($attributes[$attrName])) {
                            $attributes[$attrName] = '';
                        }
                        $key = array_shift($keys);
                        $val = array_shift($keys);
                        //动态style值
                        $attributes[$attrName] .= $this->php("echo {$value}?'{$key}:{$val};':'';");
                        break;
                }
            }
        }
        foreach ($attributes as $name => $value) {
            $prefix .= " {$name}=\"$value\"";
        }
        $prefix .= '>';
        $suffix = "</{$this->tidyNode->name}>";
        return $this->prefix($prefix)->suffix($suffix);
    }
}