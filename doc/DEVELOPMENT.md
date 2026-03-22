# Argon Modern 开发文档 (Development Guide)

欢迎参与 `argon-modern` 的重构与开发。本项目是对原版 Argon 主题的现代化重构版，旨在通过 **面向对象 (OOP)**、**按需加载 (On-demand Loading)** 和 **规范化架构** 提升主题的性能与可维护性。

---

## 1. 架构概述

项目采用模块化设计，核心逻辑通过命名空间 `ArgonModern` 进行隔离。

### 1.1 核心目录结构
- **`inc/`**: 存放所有核心 PHP 类文件（采用单例模式）。
- **`template-parts/`**: 存放拆分后的模板组件，保持 `index.php`、`single.php` 等入口文件的简洁。
- **`assets/`**: 存放前端资源。
  - `vendor/`: 独立的第三方库，已实现按需加载。
  - `js/` & `css/`: 主题核心压缩资源。
- **`functions.php`**: 主题初始化入口，仅负责实例化 `inc/` 下的核心类。

---

## 2. 核心类职责 (Core Classes)

### 2.1 `Singleton` Trait
提供统一的单例实现。所有类通过 `::instance()` 获取实例。
- **规范**: 必须实现 `protected function setup()` 方法用于挂载 WordPress Hooks。

### 2.2 `Options` 类
**职责**: 统一的主题配置管理器。
- **兼容性**: 自动兼容 `argon_` 前缀的旧配置。
- **性能**: 配置在内存中缓存，减少数据库查询。
- **用法**:
  ```php
  $val = \ArgonModern\Options::instance()->get('option_name', 'default_value');
  ```

### 2.3 `Assets` 类 (关键)
**职责**: 管理前端资源的加载、CDN 切换及**性能优化**。
- **按需加载**: 已废弃原版的合并资源包（如 `argon_js_merged.js`），改为根据页面内容和后台配置动态入队（`wp_enqueue_script`）对应插件。
- **依赖管理**: 强制处理了 jQuery 无冲突模式下的 `$` 绑定问题。

### 2.4 `Template` 类
**职责**: 封装模板渲染的静态辅助方法。
- **Meta 统一化**: 通过 `render_article_meta()` 统一预览页与详情页的文章元数据渲染。
- **导航组件**: 负责“推荐文章”、“上下篇文章”等卡片的逻辑生成。

---

## 3. 性能优化规范

为了保持性能，开发时应遵循以下规范：

### 3.1 CSS/JS 按需加载
严禁在 `Assets.php` 中直接全局加载体积较大的 Vendor 库。
- **判断逻辑**: 优先使用 `Template::have_catalog()` 或 `$options->get()` 进行条件判断。
- **示例**: 仅在文章包含目录时加载 `headindex.js`。

### 3.2 避免渲染阻塞
- 所有的 JS 脚本必须注册在页脚（`$in_footer = true`）。
- 优先使用 `wp_register_script` 并在需要时才 `wp_enqueue_script`。

---

## 4. 前端开发与 Pjax 适配

主题深度集成 Pjax 异步加载，所有脚本必须具备**幂等性**。

### 4.1 脚本初始化
严禁直接在全局作用域编写初始化逻辑。必须封装为初始化函数并在 `argontheme.js` 的 `pjax:complete` 回调中注册。
```javascript
function myNewFeatureInit() {
    if ($(".my-selector").length > 0) {
        // 初始化逻辑...
    }
}
// 必须在 pjax:complete 中调用
```

### 4.2 健壮性检查
由于库是按需加载的，调用插件前**必须**进行类型检查，防止控制台报错：
```javascript
if (typeof $.fn.somePlugin !== "undefined") {
    $(".el").somePlugin();
}
```

---

## 5. 模板重构指南

在重构或新增模板时，应遵循以下流程：

1. **逻辑下沉**: 将复杂的 PHP 逻辑（如计算阅读时间、处理占位符）移至 `inc/Template.php`。
2. **样式隔离**: 文章卡片样式应优先复用 Bootstrap 类名，特殊 UI（如蓝色左边框文末内容）应在 `style.css` 中定义独立类。
3. **占位符支持**: 文末内容等功能需支持 `%url%`, `%link%`, `%title%`, `%author%` 等变量替换。

---

## 6. 开发调试
- **DEBUG 模式**: 开发时建议在 `wp-config.php` 中开启 `WP_DEBUG`。
