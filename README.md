![Argon](https://cdn.jsdelivr.net/gh/solstice23/cdn@master/argon_new_animate.svg)

**简体中文**

# Argon Modern Theme
Argon Modern - 基于 Argon 的现代化重构版本，更轻盈、更简洁、更美观的 WordPress 主题

原项目：[github.com/solstice23/argon-theme](https://github.com/solstice23/argon-theme)


# 项目状态

> 本项目是对原版 Argon 主题的现代化重构，采用 **面向对象 (OOP)**、**按需加载 (On-demand Loading)** 和 **规范化架构**，旨在提升主题的性能与可维护性。

# 特性

## 核心特性

+ **轻盈美观** - 使用 Argon Design System 前端框架，细节精致，轻盈美观
+ **高度可定制化** - 可自定义主题色、布局 (双栏/单栏/三栏)、顶栏、侧栏、Banner、背景图、日夜间模式不同背景、背景沉浸、浮动操作按钮等，提供了丰富的自定义选项
+ **夜间模式** - 支持日间、夜间、纯黑三种模式，并可以根据时间自动切换或跟随系统夜间模式
+ **功能繁多** - Tag 和分类统计、作者链接、额外链接、文章字数和预计阅读时间、文章过时信息显示

## 性能优化

+ **按需加载** - 废弃合并资源包，根据页面内容和配置动态加载对应资源
+ **模块化架构** - 采用命名空间 `ArgonModern` 隔离核心逻辑，所有类使用单例模式
+ **性能优化** - 配置内存缓存减少数据库查询，JS 脚本页脚加载避免渲染阻塞

## 高级功能

+ **Pjax** - 支持 Pjax 无刷新加载，提高浏览体验
+ **友情链接** - 支持使用 Wordpress 自带的链接管理器进行友链管理，支持多种友链样式
+ **"说说" 功能** - 随时发表想法，并在专门的 "说说" 页面展示，也支持说说和首页文章穿插
+ **评论功能扩展** - Ajax 评论，评论支持 Markdown、验证码、再次编辑、显示 UA、悄悄话模式、回复时邮件通知、查看编辑记录、无限加载、评论点赞、评论置顶等功能

## 丰富功能

+ **诸多功能** - 文章目录、阅读进度、Mathjax 或 Katex 公式解析、图片放大预览、Pangu.js 文本格式化、平滑滚动等
+ **丰富的短代码** - 支持通过短代码在文章中插入 TODO、标签、警告、提示、折叠区块、Github 信息卡、时间线、隐藏文本、视频等模块
+ **适配 Gutenberg 编辑器** - 支持使用 Gutenberg 编辑器可视化插入区块
+ **多语言** - 支持中文、英文、俄文等语言

## 其他特性

+ **自适应** - 精心优化的文章阅读界面 CSS
+ **可切换字体** - 衬线/非衬线字体可选
+ **自定义扩展** - 可自定义 CSS 和 JS
+ **CDN 加速** - 支持使用 CDN 加速静态文件访问
+ **SEO 友好** - 搜索引擎优化
+ **Banner 动画** - Banner 打字动画
+ **其他** - 留言板页面、文章脚注等

# 安装

1. 克隆或下载本项目到本地
2. 将整个 `argon-modern` 文件夹上传到 WordPress 的 `wp-content/themes/` 目录
3. 在 WordPress 后台 "主题" 页面启用 Argon Modern 主题

# 架构说明

## 目录结构

```
argon-modern/
├── inc/                    # 核心 PHP 类文件（单例模式）
│   ├── Assets.php         # 资源加载管理（按需加载）
│   ├── Options.php        # 主题配置管理
│   ├── Template.php       # 模板渲染辅助
│   ├── Comments.php       # 评论功能
│   ├── Shortcodes.php     # 短代码支持
│   ├── Shuoshuo.php       # 说说功能
│   ├── UserAgent.php      # UA 解析
│   └── ...
├── template-parts/        # 拆分的模板组件
├── assets/                # 前端资源
│   ├── vendor/           # 第三方库（按需加载）
│   ├── js/               # 主题核心 JS
│   ├── css/              # 主题核心 CSS
│   └── ...
├── *.php                  # 模板文件
└── functions.php          # 主题初始化入口
```

## 核心类

- **`Singleton`** - 单例模式 Trait，所有类的基类
- **`Options`** - 主题配置管理器，自动兼容旧版配置
- **`Assets`** - 资源加载管理器，实现按需加载
- **`Template`** - 模板渲染辅助类
- **`Comments`** - 评论功能管理类
- **`Shortcodes`** - 短代码解析类
- **`Shuoshuo`** - 说说功能管理类

# 开发文档

详细的开发文档请参阅 [DEVELOPMENT.md](DEVELOPMENT.md)

## 快速开始

### 获取配置值

```php
$options = \ArgonModern\Options::instance();
$val = $options->get('option_name', 'default_value');
```

### 添加新功能

1. 在 `inc/` 目录创建新的类，使用 `Singleton` Trait
2. 在 `functions.php` 的 `init()` 函数中注册类
3. 实现 `setup()` 方法挂载 WordPress Hooks

### 前端脚本初始化

```javascript
function myNewFeatureInit() {
    if ($(".my-selector").length > 0) {
        // 初始化逻辑
    }
}
// 在 pjax:complete 中调用
```

# 短代码支持

主题支持以下短代码：

| 短代码 | 功能说明 |
|--------|----------|
| `[label]` | 标签徽章 |
| `[progressbar]` | 进度条 |
| `[checkbox]` | 复选框 |
| `[alert]` | 警告提示框 |
| `[admonition]` | 提示框 |
| `[collapse]` / `[fold]` | 折叠区块 |
| `[timeline]` | 时间线 |
| `[hidden]` / `[spoiler]` | 隐藏文本 |
| `[github]` | Github 信息卡 |
| `[video]` | 视频嵌入 |
| `[friendlinks]` | 友情链接 |
| `[ref]` | 脚注引用 |
| `[post_time]` | 文章发布时间 |
| `[post_modified_time]` | 文章修改时间 |

# 主题设置

在 WordPress 后台左侧菜单找到 "Argon 设置" 即可进行主题配置，支持：

- 主题色、布局、顶栏、侧栏配置
- Banner、背景图设置
- 夜间模式配置
- 评论功能配置
- 文章 Meta 信息显示配置
- 短代码和编辑器配置
- 杂项设置（CDN、SEO 等）

# 注意

Argon 使用 [GPL V3.0](https://github.com/solstice23/argon-theme/blob/master/LICENSE) 协议开源，请遵守此协议进行二次开发等。

您**必须在页脚保留 Argon 主题的名称及其链接**，否则请不要使用 Argon 主题。

您**可以删除**页脚的作者信息，但是**不能删除** Argon 主题的名称和链接。

本项目保留 Argon 主题的名称及其链接，以表示对原项目的尊重。

# 参与开发

欢迎参与重构与开发，目前项目仍然有许多bug，欢迎提交 PR 修复。

## 开发规范

1. **代码规范** - 遵循 WordPress 编码规范和 PSR 标准
2. **模块化** - 新功能以独立类形式放在 `inc/` 目录
3. **性能优先** - 资源按需加载，避免全局加载大体积库
4. **Pjax 适配** - 所有脚本必须具备幂等性，在 `pjax:complete` 中初始化
5. **文档同步** - 完成功能后更新开发文档

## 调试

- 开发时建议在 `wp-config.php` 中开启 `WP_DEBUG`
- 使用浏览器开发者工具检查前端资源加载情况

# 更新日志

## v2.0.0 - Modern Refactoring

+ 🎉 **架构重构** - 采用面向对象设计，所有核心类使用单例模式
+ ⚡ **性能优化** - 实现资源按需加载，废弃合并资源包
+ 🔧 **配置管理** - 新增 `Options` 类统一管理配置，自动兼容旧版
+ 📦 **模块化** - 命名空间隔离，代码更易维护
+ 🐛 **Bug 修复** - 修复多个已知问题
+ 📝 **文档完善** - 新增开发文档和架构说明

### 核心变更

- 新增 `inc/` 目录存放核心类
- 新增 `Assets.php` 实现按需加载
- 新增 `Options.php` 配置管理
- 新增 `Template.php` 模板辅助
- 重构评论、短代码、说说等功能

### 技术栈

- PHP 7.4+
- WordPress 5.0+
- Bootstrap 4
- jQuery 3.x
- Argon Design System

# 原项目信息

原版 Argon Theme 由 [solstice23](https://github.com/solstice23) 开发

- 原项目：[github.com/solstice23/argon-theme](https://github.com/solstice23/argon-theme)
- 文档：[argon-docs.solstice23.top](https://argon-docs.solstice23.top/)

# 许可证

[GPL V3.0](LICENSE)

