					<footer id="footer" class="site-footer card shadow-sm border-0">
						<?php
							echo \ArgonModern\Options::instance()->get('footer_html');
						?>
						<div>Theme <a href="https://github.com/solstice23/argon-theme" target="_blank"><strong>Argon</strong></a><?php if (\ArgonModern\Options::instance()->get('hide_footer_author') != 'true') {echo " By solstice23"; }?></div>
					</footer>
				</main>
			</div>
		</div>
		<?php if (\ArgonModern\Options::instance()->get('math_render') == 'mathjax3') { /*Mathjax V3*/?>
			<script>
				window.MathJax = {
					tex: {
						inlineMath: [["$", "$"], ["\\\\(", "\\\\)"]],
						displayMath: [['$$','$$']],
						processEscapes: true,
						packages: {'[+]': ['noerrors']}
					},
					options: {
						skipHtmlTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code'],
						ignoreHtmlClass: 'tex2jax_ignore',
						processHtmlClass: 'tex2jax_process'
					},
					loader: {
						load: ['[tex]/noerrors']
					}
				};
			</script>
			<script src="<?php echo \ArgonModern\Options::instance()->get('mathjax_cdn_url') == '' ? '//cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml-full.js' : \ArgonModern\Options::instance()->get('mathjax_cdn_url'); ?>" id="MathJax-script" async></script>
		<?php }?>
		<?php if (\ArgonModern\Options::instance()->get('math_render') == 'mathjax2') { /*Mathjax V2*/?>
			<script type="text/x-mathjax-config" id="mathjax_v2_script">
				MathJax.Hub.Config({
					messageStyle: "none",
					tex2jax: {
						inlineMath: [["$", "$"], ["\\\\(", "\\\\)"]],
						displayMath: [['$$','$$']],
						processEscapes: true,
						skipTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code']
					},
					menuSettings: {
						zoom: "Hover",
						zscale: "200%"
					},
					"HTML-CSS": {
						showMathMenu: "false"
					}
				});
			</script>
			<script src="<?php echo \ArgonModern\Options::instance()->get('mathjax_v2_cdn_url') == '' ? '//cdn.jsdelivr.net/npm/mathjax@2.7.5/MathJax.js?config=TeX-AMS_HTML' : \ArgonModern\Options::instance()->get('mathjax_v2_cdn_url'); ?>"></script>
		<?php }?>
		<?php if (\ArgonModern\Options::instance()->get('math_render') == 'katex') { /*Katex*/?>
			<link rel="stylesheet" href="<?php echo \ArgonModern\Options::instance()->get('katex_cdn_url') == '' ? '//cdn.jsdelivr.net/npm/katex@0.11.1/dist/' : \ArgonModern\Options::instance()->get('katex_cdn_url'); ?>katex.min.css">
			<script src="<?php echo \ArgonModern\Options::instance()->get('katex_cdn_url') == '' ? '//cdn.jsdelivr.net/npm/katex@0.11.1/dist/' : \ArgonModern\Options::instance()->get('katex_cdn_url'); ?>katex.min.js"></script>
			<script src="<?php echo \ArgonModern\Options::instance()->get('katex_cdn_url') == '' ? '//cdn.jsdelivr.net/npm/katex@0.11.1/dist/' : \ArgonModern\Options::instance()->get('katex_cdn_url'); ?>contrib/auto-render.min.js"></script>
			<script>
				document.addEventListener("DOMContentLoaded", function() {
					renderMathInElement(document.body,{
						delimiters: [
							{left: "$$", right: "$$", display: true},
							{left: "$", right: "$", display: false},
							{left: "\\(", right: "\\)", display: false}
						]
					});
				});
			</script>
		<?php }?>

		<?php if (\ArgonModern\Options::instance()->get('enable_code_highlight') == 'true') { /*Highlight.js*/?>
			<link rel="stylesheet" href="<?php echo argon_get_asset_uri('/assets/vendor/highlight/styles/' . (\ArgonModern\Options::instance()->get('code_theme') == '' ? 'vs2015' : \ArgonModern\Options::instance()->get('code_theme')) . '.css'); ?>">
			<?php if (\ArgonModern\Options::instance()->get('enable_code_highlight_line_number') == 'true') { ?>
				<style>
					.hljs-ln-numbers {
						-webkit-touch-callout: none;
						-webkit-user-select: none;
						-khtml-user-select: none;
						-moz-user-select: none;
						-ms-user-select: none;
						user-select: none;
						text-align: center;
						color: #ccc;
						border-right: 1px solid #CCC;
						vertical-align: top;
						padding-right: 5px !important;
					}
					.hljs-ln-code {
						padding-left: 10px !important;
					}
				</style>
			<?php } ?>
		<?php }?>

	</div>
</div>
<?php wp_footer(); ?>
</body>

<?php echo \ArgonModern\Options::instance()->get('custom_html_foot'); ?>

</html>
