<?php


use PHPUnit\Framework\TestCase;

class BasicTest extends TestCase
{
    /** 测试没有布局的例子 */
    public function testUnLayout(){
        $config = [
            'tpl_suffix' => 'html',
            'layout_on' => false,
        ];

        $cozy = new Template($config);
        $cozy->parse();
    }
}