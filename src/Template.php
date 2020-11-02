<?php

use EdwardChoHub\base\BaseTag;

/**
 * Class Cozy
 */
class Template
{
    /**
     * @var tidyNode 根
     */
    private $tidyNode = null;
    //缓存文件名
    protected $cacheFilename = '';
    //缓存完整文件名（包含路径）
    protected $cacheFullFilename = '';
    //缓存内容
    protected $cacheContent = '';
    //默认处理器
    protected $functionTag = 'function';
    //默认普通标签处理函
    protected $normalTag = 'normal';
    /**
     * 处理函数集合（命名空间划分）
     * @var array
     */
    protected $namespaceTagHandlers = [
        //默认标签的处理函数集合
        'cozy' => [
            'IF' => null,
            'ELSEIF' => null,
            'ELSE' => null,
            'FOREACH' => null,
            'SET' => null,
            'GET' => null,
        ],
    ];


    //可注入配置
    protected $config = [
        //标签命名空间（默认）
        'namespace_default' => 'cozy',
        //布局使用
        'layout_on' => false,
        //布局模板 filename
        'layout_file' => '',
        //布局模板替换标记
        'layout_tag' => 'view_content',
        //拓展标签
        'extra_taglib' => [],
        //缓存名称
        'cache_file' => '',
        //开启缓存
        'tpl_cache' => true,
        //模板文件后缀(数组)
        'tpl_suffix' => ['html', 'phtml', 'php'],
        //分隔符
        'path_separator' => '/',
        //模板保存文件
        'tpl_file' => '',
        //缓存时间 0表示永久
        'cache_time' => 0,
        //缓存文件前缀标识
        'cache_prefix' => '',
        //缓存文件夹
        'cache_path' => '',
    ];

//\<php\:view-content.*?\/\>
    public function __construct($config = [])
    {
        $this->config = array_merge($this->config, $config);
    }


    /**
     * 转换存tidyNode对象
     * @param $filename string
     */
    private function tidy($filename)
    {
        $content = file_get_contents($filename);

        //生成缓存文件名
        $this->cacheFilename = md5($content);

        //html修复为xhtml
        $tidy_config = [
            'clean' => true,
            'output-xhtml' => true,
            'wrap' => 0,
        ];

        $tidy = tidy_parse_string($content, $tidy_config, 'UTF8');
        unset($content);

        $tidy->cleanRepair();

        $this->tidyNode = $tidy->html()->getParent();
    }

    /**
     * 检查缓存文件是否有效
     * @author QiuMinMin
     * Date: 2020/9/12 21:25
     */
    private function checkCache()
    {
        if (!$this->config['tpl_cache']) {
            //已禁用文件缓存
            return false;
        }

        if (!is_file($this->cacheFullFilename)) {
            //缓存文件不存在
            return false;
        }

        $file = fopen($this->cacheFullFilename, 'r');
        //拿到cache_id
        preg_match('/\/\*\*(.*)\*\//', fread($file, $file), $matches);
        fclose($file);
        if (empty($matches[1])) {
            //没有获取到缓存标识
            return false;
        }

        list($timeout, $cacheFilename) = explode('-', trim($matches[1]));
        if (empty($cacheFilename)) {
            return false;
        }

        if (!empty($this->config['cache_time']) && $this->config['cache_time'] > time() - $timeout) {
            //缓存超时
            return false;
        }

        if ($this->cacheFilename != $cacheFilename) {
            //缓存标识不正确
            return false;
        }

        return true;
    }

    /**
     * 处理入口
     * @return string 缓存后的内容
     */
    public function parse()
    {
        $this->tidy($this->config['tpl_file']);

        //完整缓存文件名
        $this->cacheFullFilename =
            $this->config['cache_prefix'] .
            $this->config['cache_path'] .
            $this->config['path_separator'] .
            $this->cacheFilename;

        //检查缓存
        if (!$this->checkCache()) {
            //缓存有效直接返回缓存文件地址
            $this->compiler();
        }

        //返回缓存后的内容
        return $this->cacheContent;
    }

    private function cacheNamespaceTagHandler($namespace, $tagName, $handler = null){
        if(empty($handler)){
            if(empty($this->namespaceTagHandlers[$namespace])){
                return false;
            }
            if(empty($this->namespaceTagHandlers[$namespace][$tagName])){
                return false;
            }
            return $this->namespaceTagHandlers[$namespace][$tagName];
        }else{
            if(empty($this->namespaceTagHandlers[$namespace])){
                $this->namespaceTagHandlers[$namespace] = [];
            }
            $this->namespaceTagHandlers[$namespace][$tagName] = $handler;
            return $this;
        }
    }

    /**
     * 调用标签处理函数
     * @param string $namespace
     * @param $tagName string 标签名
     * @param $tidyNode tidyNode
     * @param int $index
     * @return array|false
     * @throws ReflectionException
     */
    private function handlerTag($namespace, $tagName, $tidyNode, $index = 0)
    {
        //优先级 外库处理类 > 内库处理类 > 通用处理类
        $class = [
            sprintf('%s\%s\%s', $this->config['extra_taglib'], $namespace, ucfirst($tagName) . 'Tag'),
            sprintf('minqCloud\tagLib\%s\%s', $namespace, ucfirst($tagName) . 'Tag'),
            sprintf('minqCloud\tagLib\%s\%s', $namespace, ucfirst($this->functionTag) . 'Tag'),
            sprintf('minqCloud\tagLib\%s\%s', $namespace, ucfirst($this->normalTag) . 'Tag'),
        ];

        $handler = $this->cacheNamespaceTagHandler($namespace, $tagName);
        if($handler === false){
            //没有缓存创建新对象
            $class = $class ?: sprintf('minqCloud\tagLib\%s\%s', $namespace, ucfirst($tagName) . 'Tag');
            if(!class_exists($class)){
                return $this->handlerTag($namespace, $tagName, $tidyNode, ++$index);
            }else{
                $handler = new $class();
                //缓存处理器
                $this->cacheNamespaceTagHandler($namespace, $tagName, $handler);
            }
        }

        /**
         * @var $handler BaseTag
         */
        $handler->args = array_merge($tidyNode->attribute, $handler->args);
        $handler->tidyNode = $tidyNode;
        $handler->tagName = $tagName;

        //调用handle函数
        $result = $handler->handle();
        if($result === false){
            //处理不成功则使用普通标签处理函数处理
            return $this->handlerTag($namespace, $tagName, $tidyNode, ++$index);
        }

        return [$handler->getPrefix(), $handler->getSuffix()];
    }

    /**
     *
     * @param $tidyNode tidyNode
     * @return string
     * @throws ReflectionException
     * @author QiuMinMin
     * Date: 2020/9/12 22:25
     */
    private function compilerTidyNode($tidyNode)
    {
        if($tidyNode->isPhp() || $tidyNode->isText()){
            //php标签，文本直接返回
            return strval($tidyNode);
        }
        if(preg_match('/<!--.*-->/', strval($tidyNode))){
            //注释则直接返回空字符串
            return '';
        }

        $nameArrays = explode(':', $tidyNode->name);
        if (count($nameArrays) == 1) {
            array_unshift($nameArrays, 'cozy');
        }
        list($prefix, $suffix) = $this->handlerTag($nameArrays[0], $nameArrays[1], $tidyNode);

        //遍历获取编译后的内容
        $content = '';
        if ($tidyNode->hasChildren()) {
            foreach ($tidyNode->child as $children) {
                /**
                 * @var $children tidyNode
                 */
                $content .= $this->compilerTidyNode($children);
            }
        } else {
            $content .= "{$tidyNode->value}";
        }

        return $prefix . $content . $suffix;
    }

    /**
     * 编译缓存文件内容
     * @author QiuMinMin
     * Date: 2020/9/12 21:24
     */
    private function compiler()
    {
        $time = time();
        $cacheInfo = "<?php /** {$time}-{$this->cacheFilename} */?>";
        $this->cacheContent = $this->compilerTidyNode($this->tidyNode);

        if ($this->config['tpl_cache']) {
            //保存缓存后的内容
            $file = fopen($this->cacheFullFilename, 'w+');
            fwrite($file, $cacheInfo . '\r\n' . $this->cacheContent);
            fclose($file);
        }
    }
}