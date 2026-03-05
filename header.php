<!DOCTYPE html>
<?php
	$options = \ArgonModern\Options::instance();

	$themecolor_origin = $options->get('theme_color', '#5e72e4');
	$themecolor = $themecolor_origin;
	if (isset($_COOKIE["argon_custom_theme_color"])){
		if (\ArgonModern\Utils::checkHEX($_COOKIE["argon_custom_theme_color"]) && $options->get('show_customize_theme_color_picker') != 'false'){
			$themecolor = $_COOKIE["argon_custom_theme_color"];
		}
	}

	if (\ArgonModern\Utils::hex2gray($themecolor) < 50){
		echo '<script>document.getElementsByTagName("html")[0].classList.add("themecolor-toodark");</script>';
	}

	$RGB = \ArgonModern\Utils::hexstr2rgb( $themecolor );
	$HSL = \ArgonModern\Utils::rgb2hsl( $RGB['R'], $RGB['G'], $RGB['B'] );

	$cardradius_origin = $options->get('card_radius', '4');
	$cardradius = $cardradius_origin;
	if (isset($_COOKIE["argon_card_radius"]) && is_numeric($_COOKIE["argon_card_radius"])){
		$cardradius = $_COOKIE["argon_card_radius"];
	}
	$htmlclasses = "";
	$page_layout = $options->get('page_layout', 'double');
	if ($page_layout == "single"){
		$htmlclasses .= "single-column ";
	} else if ($page_layout == "triple"){
		$htmlclasses .= "triple-column ";
	} else if ($page_layout == "double-reverse"){
		$htmlclasses .= "double-column-reverse ";
	}
	if ($options->get('enable_immersion_color') == "true"){
		$htmlclasses .= "immersion-color ";
	}
	if ($options->get('enable_amoled_dark') == "true"){
		$htmlclasses .= "amoled-dark ";
	}
	if ($options->get('card_shadow') == 'big' || get_option('argon_card_shadow') == 'big'){
		$htmlclasses .= 'use-big-shadow ';
	}
	if ($options->get('font') == 'serif' || get_option('argon_font') == 'serif'){
		$htmlclasses .= 'use-serif ';
	}
	if ($options->get('disable_codeblock_style') == 'true'){
		$htmlclasses .= 'disable-codeblock-style ';
	}
	if ($options->get('enable_headroom') == 'absolute'){
		$htmlclasses .= 'navbar-absolute ';
	}
	$banner_size = $options->get('banner_size', 'full');
	if ($banner_size != 'full'){
		if ($banner_size == 'mini'){
			$htmlclasses .= 'banner-mini ';
		}else if ($banner_size == 'hide'){
			$htmlclasses .= 'no-banner ';
		}else if ($banner_size == 'fullscreen'){
			$htmlclasses .= 'banner-as-cover ';
		}
	}
	if ($options->get('toolbar_blur', 'false') == 'true'){
		$htmlclasses .= 'toolbar-blur ';
	}
	if (is_search()){
		$htmlclasses .= 'no-banner ';
	}
	$htmlclasses .= $options->get('article_header_style', 'article-header-style-default') . ' ';
	if(strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') === false){
		$htmlclasses .= ' using-safari';
	}
?>
<html <?php language_attributes(); ?> class="no-js <?php echo $htmlclasses;?>">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<?php if ($options->get('enable_mobile_scale') != 'true'){ ?>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<?php }else{ ?>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
	<?php } ?>
	<link rel="profile" href="http://gmpg.org/xfn/11">

	<meta property="og:site_name" content="<?php echo get_bloginfo('name');?>">
	<meta property="og:title" content="<?php echo wp_get_document_title();?>">
	<meta property="og:type" content="article">
	<?php global $wp; ?>
	<meta property="og:url" content="<?php echo home_url(add_query_arg(array(), $wp->request));?>">
	<?php
		$seo_description = \ArgonModern\Template::get_seo_description();
		if ($seo_description != ''){ ?>
			<meta name="description" content="<?php echo $seo_description?>">
			<meta property="og:description" content="<?php echo $seo_description?>">
	<?php } ?>

	<?php
		$seo_keywords = \ArgonModern\Template::get_seo_keywords();
		if ($seo_keywords != ''){ ?>
			<meta name="keywords" content="<?php echo $seo_keywords;?>">
	<?php } ?>

	<?php
		if (is_single() || is_page()){
			$og_image = \ArgonModern\Template::get_og_image();
			if ($og_image != ''){ ?>
				<meta property="og:image" content="<?php echo $og_image?>" />
	<?php 	}
		} ?>

	<meta name="theme-color" content="<?php echo $themecolor; ?>">
	<meta name="theme-color-rgb" content="<?php echo $RGB['R'] . ',' . $RGB['G'] . ',' . $RGB['B']; ?>">
	<meta name="theme-color-origin" content="<?php echo $options->get('theme_color', '#5e72e4'); ?>">
	<meta name="argon-enable-custom-theme-color" content="<?php echo ($options->get('show_customize_theme_color_picker') != 'false' ? 'true' : 'false'); ?>">

	<meta name="theme-card-radius" content="<?php echo $cardradius; ?>">
	<meta name="theme-card-radius-origin" content="<?php echo $cardradius_origin; ?>">

	<meta name="theme-version" content="<?php echo ARGON_MODERN_VERSION; ?>">

	<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
	<link rel="pingback" href="<?php echo esc_url( get_bloginfo( 'pingback_url' ) ); ?>">
	<?php endif; ?>

	<?php wp_head(); ?>
	<style id="themecolor_css">
		:root {
			--themecolor: <?php echo $themecolor; ?>;
			--themecolor-R: <?php echo $RGB['R']; ?>;
			--themecolor-G: <?php echo $RGB['G']; ?>;
			--themecolor-B: <?php echo $RGB['B']; ?>;
			--themecolor-H: <?php echo $HSL['H']; ?>;
			--themecolor-S: <?php echo $HSL['S']; ?>;
			--themecolor-L: <?php echo $HSL['L']; ?>;
			--themecolor-rgbstr: <?php echo $RGB['R'] . ',' . $RGB['G'] . ',' . $RGB['B']; ?>;
			--themecolor-dark0: hsl(<?php echo $HSL['H']; ?>, <?php echo $HSL['S']; ?>%, <?php echo max($HSL['L'] - 2.5, 0); ?>%);
			--themecolor-dark: hsl(<?php echo $HSL['H']; ?>, <?php echo $HSL['S']; ?>%, <?php echo max($HSL['L'] - 5, 0); ?>%);
			--themecolor-dark2: hsl(<?php echo $HSL['H']; ?>, <?php echo $HSL['S']; ?>%, <?php echo max($HSL['L'] - 10, 0); ?>%);
			--themecolor-dark3: hsl(<?php echo $HSL['H']; ?>, <?php echo $HSL['S']; ?>%, <?php echo max($HSL['L'] - 15, 0); ?>%);
			--themecolor-light: hsl(<?php echo $HSL['H']; ?>, <?php echo $HSL['S']; ?>%, <?php echo min($HSL['L'] + 10, 100); ?>%);
			--themecolor-gradient: linear-gradient(150deg, var(--themecolor-light) 15%, var(--themecolor) 70%, var(--themecolor-dark0) 94%);
			--card-radius: <?php echo $cardradius; ?>px;
		}
	</style>
	<script>
		document.documentElement.classList.remove("no-js");
		var argonConfig = {
			wp_path: "<?php echo $options->get('wp_path') == '' ? '/' : $options->get('wp_path'); ?>",
			language: "<?php echo \ArgonModern\Template::get_locate(); ?>",
			nonce: "<?php echo wp_create_nonce( 'argon_nonce' ); ?>",
			dateFormat: "<?php echo $options->get('dateformat', 'YMD'); ?>",
			<?php if ($options->get('enable_zoomify') == 'true'){ ?>
				zoomify: {
					duration: <?php echo $options->get('zoomify_duration', 200); ?>,
					easing: "<?php echo $options->get('zoomify_easing', 'cubic-bezier(0.4,0,0,1)'); ?>",
					scale: <?php echo $options->get('zoomify_scale', 0.9); ?>
				},
			<?php } else { ?>
				zoomify: false,
			<?php } ?>
			pangu: "<?php echo $options->get('enable_pangu', 'false'); ?>",
			<?php if ($options->get('enable_lazyload') != 'false'){ ?>
				lazyload: {
					threshold: <?php echo $options->get('lazyload_threshold', 800); ?>,
					effect: "<?php echo $options->get('lazyload_effect', 'fadeIn'); ?>"
				},
			<?php } else { ?>
				lazyload: false,
			<?php } ?>
			fold_long_comments: <?php echo $options->get('fold_long_comments', 'false'); ?>,
			fold_long_shuoshuo: <?php echo $options->get('fold_long_shuoshuo', 'false'); ?>,
			disable_pjax: <?php echo $options->get('pjax_disabled', 'false'); ?>,
			pjax_animation_durtion: <?php echo ($options->get("disable_pjax_animation") == 'true' ? '0' : '600'); ?>,
			headroom: "<?php echo $options->get('enable_headroom', 'false'); ?>",
			no_banner_by_default: <?php echo ($banner_size == 'hide' ? 'true' : 'false'); ?>,
			card_shadow: "<?php echo (get_option('argon_card_shadow') == 'big' || $options->get('card_shadow') == 'big' ? 'big' : 'default'); ?>",
			font: "<?php echo (get_option('argon_font') == 'serif' || $options->get('font') == 'serif' ? 'serif' : 'sans-serif'); ?>",
			waterflow_columns: "<?php echo $options->get('article_list_waterflow', '1'); ?>",
			code_highlight: {
				enable: <?php echo $options->get('argon_enable_code_highlight', 'false'); ?>,
				hide_linenumber: <?php echo $options->get('argon_code_highlight_hide_linenumber', 'false'); ?>,
				transparent_linenumber: <?php echo $options->get('argon_code_highlight_transparent_linenumber', 'false'); ?>,
				break_line: <?php echo $options->get('argon_code_highlight_break_line', 'false'); ?>
			}
		}
	</script>
	<?php if ($options->get('argon_math_render') == 'mathjax3') { ?>
		<script src="<?php echo $options->get('argon_mathjax_cdn_url') == '' ? '//cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml-full.js' : $options->get('argon_mathjax_cdn_url'); ?>" async></script>
	<?php } ?>
	<?php if ($options->get('argon_math_render') == 'mathjax2') { ?>
		<script src="<?php echo $options->get('argon_mathjax_v2_cdn_url') == '' ? '//cdn.jsdelivr.net/npm/mathjax@2.7.5/MathJax.js?config=TeX-AMS_HTML' : $options->get('argon_mathjax_v2_cdn_url'); ?>" async></script>
	<?php } ?>
	<?php if ($options->get('argon_math_render') == 'katex') { ?>
		<link rel="stylesheet" href="<?php echo $options->get('argon_katex_cdn_url') == '' ? '//cdn.jsdelivr.net/npm/katex@0.11.1/dist/' : $options->get('argon_katex_cdn_url'); ?>katex.min.css">
		<script src="<?php echo $options->get('argon_katex_cdn_url') == '' ? '//cdn.jsdelivr.net/npm/katex@0.11.1/dist/' : $options->get('argon_katex_cdn_url'); ?>katex.min.js" defer></script>
		<script src="<?php echo $options->get('argon_katex_cdn_url') == '' ? '//cdn.jsdelivr.net/npm/katex@0.11.1/dist/' : $options->get('argon_katex_cdn_url'); ?>contrib/auto-render.min.js" defer onload='renderMathInElement(document.body,{delimiters: [{left: "$$", right: "$$", display: true},{left: "$", right: "$", display: false},{left: "\\(", right: "\\)", display: false}]});'></script>
	<?php } ?>
	<script>
		var darkmodeAutoSwitch = "<?php echo ($options->get("darkmode_autoswitch") == '' ? 'false' : $options->get("darkmode_autoswitch"));?>";
		function setDarkmode(enable){
			if (enable == true){
				document.documentElement.classList.add("darkmode");
			}else{
				document.documentElement.classList.remove("darkmode");
			}
			if (window.jQuery) {
				jQuery(window).trigger("scroll");
			}
		}
		function toggleDarkmode(){
			if (document.documentElement.classList.contains("darkmode")){
				setDarkmode(false);
				sessionStorage.setItem("Argon_Enable_Dark_Mode", "false");
			}else{
				setDarkmode(true);
				sessionStorage.setItem("Argon_Enable_Dark_Mode", "true");
			}
		}
		if (sessionStorage.getItem("Argon_Enable_Dark_Mode") == "true"){
			setDarkmode(true);
		}
		function toggleDarkmodeByPrefersColorScheme(media){
			if (sessionStorage.getItem('Argon_Enable_Dark_Mode') == "false" || sessionStorage.getItem('Argon_Enable_Dark_Mode') == "true"){
				return;
			}
			if (media.matches){
				setDarkmode(true);
			}else{
				setDarkmode(false);
			}
		}
		function toggleDarkmodeByTime(){
			if (sessionStorage.getItem('Argon_Enable_Dark_Mode') == "false" || sessionStorage.getItem('Argon_Enable_Dark_Mode') == "true"){
				return;
			}
			let hour = new Date().getHours();
			if (hour < 7 || hour >= 22){
				setDarkmode(true);
			}else{
				setDarkmode(false);
			}
		}
		if (darkmodeAutoSwitch == 'system'){
			var darkmodeMediaQuery = window.matchMedia("(prefers-color-scheme: dark)");
			darkmodeMediaQuery.addListener(toggleDarkmodeByPrefersColorScheme);
			toggleDarkmodeByPrefersColorScheme(darkmodeMediaQuery);
		}
		if (darkmodeAutoSwitch == 'time'){
			toggleDarkmodeByTime();
		}
		if (darkmodeAutoSwitch == 'alwayson'){
			setDarkmode(true);
		}

		function toggleAmoledDarkMode(){
			document.documentElement.classList.toggle("amoled-dark");
			if (document.documentElement.classList.contains("amoled-dark")){
				localStorage.setItem("Argon_Enable_Amoled_Dark_Mode", "true");
			}else{
				localStorage.setItem("Argon_Enable_Amoled_Dark_Mode", "false");
			}
		}
		if (localStorage.getItem("Argon_Enable_Amoled_Dark_Mode") == "true"){
			document.documentElement.classList.add("amoled-dark");
		}else if (localStorage.getItem("Argon_Enable_Amoled_Dark_Mode") == "false"){
			document.documentElement.classList.remove("amoled-dark");
		}
	</script>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
	$page_background_url = $options->get('page_background_url', '');
	$page_background_dark_url = $options->get('page_background_dark_url', '');
	if ($page_background_url != '' || $page_background_dark_url != ''){
		$page_background_opacity = $options->get('page_background_opacity', '1');
?>
	<div id="page_background" class="page-background <?php if ($page_background_dark_url != ''){echo 'has-dark-bg';} ?>" style="background-image: url(<?php echo $page_background_url; ?>); opacity: <?php echo $page_background_opacity; ?>;"></div>
	<?php if ($page_background_dark_url != ''){ ?>
		<style>
			.darkmode #page_background{
				background-image: url(<?php echo $page_background_dark_url; ?>);
			}
		</style>
	<?php } ?>
	<style>
		body {
			background: transparent !important;
		}
	</style>
<?php } ?>

<div id="toolbar">
	<header class="header-global">
		<nav id="navbar-main" class="navbar navbar-main navbar-expand-lg navbar-transparent navbar-light bg-primary headroom--not-bottom headroom--not-top headroom--pinned">
			<div class="container">
				<div class="navbar-brand mr-0">
					<?php if ($options->get('toolbar_icon') != '') { ?>
						<a class="navbar-brand navbar-icon mr-lg-5" href="<?php echo $options->get('toolbar_icon_link'); ?>" aria-label="<?php bloginfo('name'); ?>">
							<img src="<?php echo $options->get('toolbar_icon'); ?>" alt="<?php bloginfo('name'); ?>" style="height: 38px; width: auto;" width="150" height="38">
						</a>
					<?php } ?>
					<?php
						$toolbar_title = $options->get('toolbar_title') == '' ? get_bloginfo('name') : $options->get('toolbar_title');
						if ($toolbar_title == '--hidden--'){
							$toolbar_title = '';
						}
					?>
					<a class="navbar-brand font-weight-bold" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php bloginfo('name'); ?>"><?php echo $toolbar_title; ?></a>
				</div>
				<div class="navbar-collapse collapse" id="navbar_global">
					<div class="navbar-collapse-header">
						<div class="input-group input-group-alternative">
							<div class="input-group-prepend">
								<span class="input-group-text"><i class="fa fa-search"></i></span>
							</div>
							<input id="navbar_search_input_mobile" class="form-control" placeholder="<?php _e('搜索什么...', 'argon-modern');?>" type="text" autocomplete="off">
						</div>
					</div>
					<?php
						if ( has_nav_menu('toolbar_menu') ){
							echo "<ul class='navbar-nav navbar-nav-hover align-items-lg-center'>";
							wp_nav_menu( array(
								'container'  => '',
								'theme_location'  => 'toolbar_menu',
								'items_wrap'  => '%3$s',
								'depth' => 0,
								'walker' => new toolbarMenuWalker()
							) );
							echo "</ul>";
						}
					?>
					<ul class="navbar-nav align-items-lg-center ml-lg-auto">
						<li id="navbar_search_container" class="nav-item" data-toggle="modal">
							<div id="navbar_search_input_container">
								<div class="input-group input-group-alternative">
									<div class="input-group-prepend">
										<span class="input-group-text"><i class="fa fa-search"></i></span>
									</div>
									<input id="navbar_search_input" class="form-control" placeholder="<?php _e('搜索什么...', 'argon-modern');?>" type="text" autocomplete="off">
								</div>
							</div>
						</li>
					</ul>
				</div>
				<div id="navbar_menu_mask" data-toggle="collapse" data-target="#navbar_global"></div>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar_global" aria-controls="navbar_global" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
			</div>
		</nav>
	</header>
</div>
<div class="modal fade" id="argon_search_modal" role="dialog" aria-labelledby="argon_search_modal_title" aria-modal="true">
	<div class="modal-dialog modal-dialog-centered modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="argon_search_modal_title"><?php _e('搜索', 'argon-modern');?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<?php get_search_form(); ?>
			</div>
		</div>
	</div>
</div>

<section id="banner" class="banner section section-lg section-shaped">
	<div class="shape <?php echo $options->get('banner_background_hide_shapes') == 'true' ? '' : 'shape-style-1' ?> <?php echo $options->get('banner_background_color_type') == '' ? 'shape-primary' : $options->get('banner_background_color_type'); ?>">
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
		<span></span>
	</div>
	<?php
		$banner_title = $options->get('banner_title') == '' ? get_bloginfo('name') : $options->get('banner_title');
		$enable_banner_title_typing_effect = $options->get('enable_banner_title_typing_effect') != 'true' ? "false" : $options->get('enable_banner_title_typing_effect');
	?>
	<div id="banner_container" class="banner-container container text-center">
		<?php if ($enable_banner_title_typing_effect != "true"){?>
			<div class="banner-title text-white"><span class="banner-title-inner"><?php echo apply_filters('argon_banner_title_html', $banner_title); ?></span>
			<?php echo $options->get('banner_subtitle') == '' ? '' : '<span class="banner-subtitle d-block">' . $options->get('banner_subtitle') . '</span>'; ?></div>
		<?php }else{ ?>
			<div class="banner-title text-white" data-interval="<?php echo $options->get('banner_typing_effect_interval', 100); ?>"><span data-text="<?php echo $banner_title; ?>" class="banner-title-inner">&nbsp;</span>
			<?php echo $options->get('banner_subtitle') == '' ? '' : '<span data-text="' . $options->get('banner_subtitle') . '" class="banner-subtitle d-block">&nbsp;</span>'; ?></div>
		<?php } ?>
	</div>
	<?php
		$banner_background_url = \ArgonModern\Template::get_banner_background_url();
	?>
	<?php if ($banner_background_url != '') { ?>
		<style>
			section.banner{
				background-image: url(<?php echo $banner_background_url; ?>) !important;
			}
		</style>
	<?php } ?>
	<?php if ($options->get('page_background_banner_style', 'false') == 'transparent') { ?>
		<style>
			#banner, #banner .shape {
				background: transparent !important;
			}
		</style>
	<?php } ?>
	<?php if ($options->get('banner_size') == 'fullscreen') { ?>
		<div class="cover-scroll-down">
			<i class="fa fa-angle-down" aria-hidden="true"></i>
		</div>
	<?php } ?>
</section>

<div id="float_action_buttons" class="float-action-buttons fabtns-unloaded">
	<button id="fabtn_toggle_sides" class="btn btn-icon btn-neutral fabtn shadow-sm" type="button" aria-label="移动侧边栏位置" tooltip-move-to-left="<?php _e('移至左侧', 'argon'); ?>" tooltip-move-to-right="<?php _e('移至右侧', 'argon'); ?>">
		<span class="btn-inner--icon fabtn-show-on-right"><i class="fa fa-caret-left"></i></span>
		<span class="btn-inner--icon fabtn-show-on-left"><i class="fa fa-caret-right"></i></span>
	</button>
	<button id="fabtn_back_to_top" class="btn btn-icon btn-neutral fabtn shadow-sm" type="button" aria-label="Back To Top" tooltip="<?php _e('回到顶部', 'argon'); ?>">
		<span class="btn-inner--icon"><i class="fa fa-angle-up"></i></span>
	</button>
	<button id="fabtn_go_to_comment" class="btn btn-icon btn-neutral fabtn shadow-sm d-none" type="button" <?php if ($options->get('fab_show_gotocomment_button') != 'true') echo " style='display: none;'";?> aria-label="Comment" tooltip="<?php _e('评论', 'argon'); ?>">
		<span class="btn-inner--icon"><i class="fa fa-comment-o"></i></span>
	</button>
	<button id="fabtn_toggle_darkmode" class="btn btn-icon btn-neutral fabtn shadow-sm" type="button" <?php if ($options->get('fab_show_darkmode_button') != 'true') echo " style='display: none;'";?> aria-label="Toggle Darkmode" tooltip-darkmode="<?php _e('夜间模式', 'argon'); ?>" tooltip-blackmode="<?php _e('暗黑模式', 'argon'); ?>" tooltip-lightmode="<?php _e('日间模式', 'argon'); ?>">
		<span class="btn-inner--icon"><i class="fa fa-moon-o"></i><i class='fa fa-lightbulb-o'></i></span>
	</button>
	<button id="fabtn_toggle_blog_settings_popup" class="btn btn-icon btn-neutral fabtn shadow-sm" type="button" <?php if ($options->get('fab_show_settings_button') == 'false') echo " style='display: none;'";?> aria-label="Open Blog Settings Menu" tooltip="<?php _e('设置', 'argon'); ?>">
		<span class="btn-inner--icon"><i class="fa fa-cog"></i></span>
	</button>
	<div id="fabtn_blog_settings_popup" class="card shadow-sm" style="opacity: 0;">
		<div id="close_blog_settings"><i class="fa fa-close"></i></div>
		<div class="blog-setting-item mt-3">
			<div style="transform: translateY(-4px);"><div id="blog_setting_toggle_darkmode_and_amoledarkmode" tooltip-switch-to-darkmode="<?php _e('切换到夜间模式', 'argon'); ?>" tooltip-switch-to-blackmode="<?php _e('切换到暗黑模式', 'argon'); ?>"><span><?php _e('夜间模式', 'argon');?></span><span><?php _e('暗黑模式', 'argon');?></span></div></div>
			<div style="flex: 1;"></div>
			<label id="blog_setting_darkmode_switch" class="custom-toggle">
				<span class="custom-toggle-slider rounded-circle"></span>
			</label>
		</div>
		<div class="blog-setting-item mt-3">
			<div style="flex: 1;"><?php _e('字体', 'argon');?></div>
			<div>
				<button id="blog_setting_font_sans_serif" type="button" class="blog-setting-font btn btn-outline-primary blog-setting-selector-left">Sans Serif</button><button id="blog_setting_font_serif" type="button" class="blog-setting-font btn btn-outline-primary blog-setting-selector-right">Serif</button>
			</div>
		</div>
		<div class="blog-setting-item mt-3">
			<div style="flex: 1;"><?php _e('阴影', 'argon');?></div>
			<div>
				<button id="blog_setting_shadow_small" type="button" class="blog-setting-shadow btn btn-outline-primary blog-setting-selector-left"><?php _e('浅阴影', 'argon');?></button><button id="blog_setting_shadow_big" type="button" class="blog-setting-shadow btn btn-outline-primary blog-setting-selector-right"><?php _e('深阴影', 'argon');?></button>
			</div>
		</div>
		<div class="blog-setting-item mt-3 mb-3">
			<div style="flex: 1;"><?php _e('滤镜', 'argon');?></div>
			<div id="blog_setting_filters" class="ml-3">
				<button id="blog_setting_filter_off" type="button" class="blog-setting-filter-btn ml-0" filter-name="off"><?php _e('关闭', 'argon');?></button>
				<button id="blog_setting_filter_sunset" type="button" class="blog-setting-filter-btn" filter-name="sunset"><?php _e('日落', 'argon');?></button>
				<button id="blog_setting_filter_darkness" type="button" class="blog-setting-filter-btn" filter-name="darkness"><?php _e('暗化', 'argon');?></button>
				<button id="blog_setting_filter_grayscale" type="button" class="blog-setting-filter-btn" filter-name="grayscale"><?php _e('灰度', 'argon');?></button>
			</div>
		</div>
		<div class="blog-setting-item mb-3">
			<div id="blog_setting_card_radius_to_default" style="cursor: pointer;" tooltip="<?php _e('恢复默认', 'argon'); ?>"><?php _e('圆角', 'argon');?></div>
			<div style="flex: 1;margin-left: 20px;margin-right: 8px;transform: translateY(2px);">
				<div id="blog_setting_card_radius"></div>
			</div>
		</div>
		<?php if ($options->get('show_customize_theme_color_picker') != 'false') {?>
			<div class="blog-setting-item mt-1 mb-3">
				<div style="flex: 1;"><?php _e('主题色', 'argon');?></div>
				<div id="theme-color-picker" class="ml-3"></div>
			</div>
		<?php }?>
	</div>
	<button id="fabtn_open_sidebar" class="btn btn-icon btn-neutral fabtn shadow-sm" type="button" aria-label="Open Sidebar Menu" tooltip="菜单">
		<span class="btn-inner--icon"><i class="fa fa-bars"></i></span>
	</button>
	<button id="fabtn_reading_progress" class="btn btn-icon btn-neutral fabtn shadow-sm" type="button" aria-label="Reading Progress" tooltip="<?php _e('阅读进度', 'argon'); ?>">
		<div id="fabtn_reading_progress_bar" style="width: 0%;"></div>
		<span id="fabtn_reading_progress_details">0%</span>
	</button>
</div>

<div id="content" class="site-content">
<script>
	document.addEventListener('DOMContentLoaded', function() {
		// 为所有 noUi-handle 添加 aria-label
		var handles = document.querySelectorAll('.noUi-handle');
		handles.forEach(function(handle, idx) {
			if (handle.classList.contains('noUi-handle-lower')) {
				handle.setAttribute('aria-label', '圆角滑块');
			} else if (handle.classList.contains('noUi-handle-upper')) {
				handle.setAttribute('aria-label', '圆角滑块（上）');
			}
		});
	});
</script>
