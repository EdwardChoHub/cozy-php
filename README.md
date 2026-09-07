# Cozy

一个基于 [Tidy](https://www.php.net/manual/en/book.tidy.php) 扩展的 PHP 模板引擎。

Cozy 不做字符串正则替换，而是先把模板解析成 DOM（tidyNode 树），再递归地把 `<cozy:xxx>` 标签编译成原生 PHP 语句，最终产出一个可以直接 `include` 的 PHP 文件。因此：

- 模板即 HTML，所见即所得，IDE 仍能高亮、格式化；
- 编译结果就是原生 PHP，没有额外的运行时解析开销；
- 支持文件级缓存，模板未变动时直接复用编译结果。

## 环境要求

- PHP >= 5.5
- `ext-tidy`（必需）
- [Composer](https://getcomposer.org/)

## 安装

```bash
composer require edwardchohub/cozy
```

## 快速开始

### 1. 准备模板 `view/index.html`

```html
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?></title>
</head>
<body>
<ul>
    <cozy:foreach name="$list" key="$k" value="$v">
        <cozy:if condition="$k == 0">
            <li><?= $v['name'] ?></li>
        </cozy:if>
        <cozy:else/>
            <li><?= $v['name'] ?></li>
        </cozy:else>
    </cozy:foreach>
</ul>
</body>
</html>
```

### 2. 编译并渲染

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use EdwardChoHub\Cozy\Template;

$cozy = new Template([
    'tpl_file'   => __DIR__ . '/view/index.html',   // 模板文件（必填）
    'cache_path' => __DIR__ . '/runtime/cache',     // 编译缓存目录
    'tpl_cache'  => true,                           // 开启缓存
]);

// 编译，返回编译后的 PHP 源码
$content = $cozy->parse();

// 写入运行文件后 include，变量在当前作用域即可生效
$runFile = __DIR__ . '/runtime/index.php';
file_put_contents($runFile, $content);

$title = 'Cozy Demo';
$list  = [
    ['name' => 'Tom'],
    ['name' => 'Jerry'],
];

include $runFile;
```

> 编译产物是普通 PHP 文件，模板中的变量通过 `include` 所在作用域传入即可，无需额外的 `assign()`。

## 配置参考

在构造 `Template` 时以数组传入，未列出的项使用默认值：

| 配置项 | 默认值 | 说明 |
| --- | --- | --- |
| `tpl_file` | `''` | 待编译的模板文件（必填） |
| `tpl_cache` | `true` | 是否开启编译缓存 |
| `cache_path` | `''` | 编译缓存目录 |
| `cache_prefix` | `''` | 缓存文件名前缀 |
| `cache_time` | `0` | 缓存有效期（秒），`0` 表示永久 |
| `cache_file` | `''` | 自定义缓存文件名 |
| `tpl_suffix` | `['html','phtml','php']` | 允许的模板后缀 |
| `path_separator` | `/` | 路径分隔符 |
| `namespace_default` | `cozy` | 默认标签命名空间（不带前缀的标签归属） |
| `layout_on` | `false` | 是否启用布局 |
| `layout_file` | `''` | 布局模板文件 |
| `layout_tag` | `view_content` | 布局中被替换的内容占位标记 |
| `extra_taglib` | `[]` | 自定义标签库命名空间，优先级最高 |

## 标签参考

标签统一使用 `<cozy:标签名 />` 形式（默认命名空间 `cozy`，可通过配置修改）。

### 条件：`if` / `elseif` / `else`

```html
<cozy:if condition="$id == 1">
    <p>ID 为 1</p>
<cozy:elseif condition="$id == 2"/>
    <p>ID 为 2</p>
<cozy:else/>
    <p>其它</p>
</cozy:if>
```

### 循环：`foreach`

| 属性 | 必填 | 默认 | 说明 |
| --- | --- | --- | --- |
| `name` | 是 | - | 待遍历的数组，如 `$list` |
| `key` | 否 | `key` | 键名变量 |
| `value` | 否 | `value` | 值变量 |

```html
<cozy:foreach name="$list" key="$k" value="$v">
    <li><?= $k ?> : <?= $v['name'] ?></li>
</cozy:foreach>
```

### 计数循环：`for`

| 属性 | 必填 | 默认 | 说明 |
| --- | --- | --- | --- |
| `name` | 是 | - | 循环变量名 |
| `start` | 否 | `0` | 起始值 |
| `end` | 是 | - | 结束值 |
| `step` | 否 | `1` | 步长 |

```html
<cozy:for name="i" start="0" end="9" step="1">
    <span><?= $i ?></span>
</cozy:for>
```

### 赋值：`set`

```html
<cozy:set name="$total" value="count($list)"/>
```

### 输出：`echo`

```html
<cozy:echo arg="$user['name']"/>
```

### 原生 PHP

模板中可以直接书写 `<?php ?>`、`<?= ?>`，编译时会原样保留：

```html
<p><?= htmlspecialchars($content) ?></p>
```

## 动态属性

除了 `<cozy:xxx>` 标签，任意 HTML 标签上带 `cozy:` 前缀的**属性**也会被编译成 PHP 表达式，用于按条件输出或切换属性。这一能力由兜底处理器 `src/tagLib/cozy/NormalTag.php` 提供——凡是没有专属 `XxxTag` 的标签，最终都会交给它处理。

### 三种写法

| 写法 | 含义 | 编译结果 |
| --- | --- | --- |
| `cozy:attr="表达式"` | 属性值由表达式决定 | `attr="<?php echo 表达式 ?>"` |
| `cozy:attr:值="条件"` | 条件成立时输出该属性 | `<?php echo 条件?'attr="值"':'' ?>` |
| `cozy:style:名:值="条件"` | 条件成立时追加一条内联样式 | `style` 中追加 `<?php echo 条件?'名:值;':'' ?>` |

几种特殊处理：

- **布尔属性**：`cozy:disabled="条件"` 等价于 `cozy:disabled:disabled="条件"`，条件不成立时整个属性不输出；适用于 `disabled`、`checked`、`selected`、`readonly`、`required` 等。
- **`class` / `style` 合并**：同一标签上多个条件片段会**追加**到同一个属性上，而不是互相覆盖；标签上已写的静态 `class="..."` 会作为基础值参与合并。
- **静态值即默认值**：既有静态属性又有动态属性时，静态值被视为默认分支（实现上用 `str_replace("''", 静态值, 动态片段)` 回填）。

### 示例

模板：

```html
<cozy:foreach name="$category_list" item="$item" key="$key">
    <option cozy:value="$key"
            cozy:selected="$current == $key"
            cozy:class:active="$current == $key"
            cozy:style:color:red="$item['hot']"
            cozy:disabled="$item['disabled']">
        <?= $item['name'] ?>
    </option>
</cozy:foreach>
```

编译结果（示意）：

```html
<option value="<?php echo $key ?>"
        <?php echo ($current == $key)?'selected="selected"':'';?>
        class="<?php echo ($current == $key)?'active':'';?>"
        style="<?php echo ($item['hot'])?'color:red;':'';?>"
        <?php echo ($item['disabled'])?'disabled="disabled"':'';?>>
    <?= $item['name'] ?>
</option>
```

### 书写约束

因为模板要先经 Tidy 解析成 DOM，动态属性需遵守：

- 属性值必须用引号包裹，表达式内避免使用双引号（可用单引号或 `&quot;`）；
- Tidy 会对属性值做实体编码（如 `&` → `&amp;`），含 `&&` 的表达式需要在 `NormalTag` 中先 `html_entity_decode()` 再拼接，否则会生成非法 PHP；
- 属性名会被 Tidy 统一成小写，书写时不区分大小写，但建议统一小写。

## 缓存机制

- 缓存文件名取模板内容的 `md5`，模板内容变化后自动重新编译；
- 缓存文件头部写入 `/**{时间戳}-{md5}*/` 作为校验标识，配合 `cache_time` 判断过期；
- 关闭 `tpl_cache` 则每次都重新编译，适合开发环境。

## 自定义标签

自定义标签只需继承 `EdwardChoHub\base\BaseTag` 并实现 `handle()`，通过 `prefix()` / `suffix()` 返回包裹内容的 PHP 片段：

```php
<?php
namespace EdwardChoHub\tagLib\cozy;

use EdwardChoHub\base\BaseTag;

class UpperTag extends BaseTag
{
    public $args = [
        'name' => self::ARG_FORCE,   // 标记为强制入参
    ];

    public function handle()
    {
        return $this->prefix("echo strtoupper({$this->args['name']});")
            ->suffix('');
    }
}
```

即可在模板中使用：

```html
<cozy:upper name="$title"/>
```

类名的查找与优先级（见 `Template::handlerTag()`）：

1. `{extra_taglib}\{命名空间}\{标签名}Tag` —— 外部扩展标签库；
2. `{命名空间}\{标签名}Tag` —— 具体标签处理器；
3. `FunctionTag` —— 函数式标签兜底；
4. `NormalTag` —— 普通标签兜底（原样输出标签，并解析带命名空间的动态属性）。

`handle()` 返回 `false` 时会自动降级到下一优先级的处理器。

## 测试

```bash
phpunit tests/BasicTest.php
```

## 已知限制

仍在开发中的部分，使用时请注意：

- `composer.json` 的 `autoload` 段存在嵌套的 `autoload` 键，需修正为 `{"autoload": {"psr-4": {"EdwardChoHub\\Cozy\\": "src/"}}}` 才能正常自动加载；
- `layout_on` / `layout_file` / `layout_tag` 等布局配置已预留，但布局替换逻辑尚未接入；
- 动态属性的语法已定义（见「动态属性」），但 `NormalTag` 的实现中使用了未定义变量 `$keys`、以数组作为数组下标等，尚未跑通；`require_once`、`php` 标签同样待实现；
- 缓存命中时 `parse()` 目前不会读取已有缓存文件内容，仅重新编译时返回源码。

## License

[MIT](LICENSE)
