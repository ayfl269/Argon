<?php
/**
 * Template part for displaying post share component
 *
 * @package ArgonModern
 */

$options = \ArgonModern\Options::instance();

$current_url = get_permalink();
$current_title = get_the_title();
$current_desc = wp_trim_words(html_entity_decode(get_the_content()), 50);

$share_links = [
	'weibo'    => 'http://service.weibo.com/share/share.php?url=' . urlencode($current_url) . '&title=' . urlencode($current_title),
	'qq'       => 'http://connect.qq.com/widget/shareqq/index.html?url=' . urlencode($current_url) . '&title=' . urlencode($current_title),
	'qzone'    => 'http://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url=' . urlencode($current_url) . '&title=' . urlencode($current_title) . '&summary=' . urlencode($current_desc),
	'douban'   => 'http://shuo.douban.com/!service/share?href=' . urlencode($current_url) . '&name=' . urlencode($current_title),
	'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($current_url),
	'twitter'  => 'https://twitter.com/intent/tweet?text=' . urlencode($current_title) . '&url=' . urlencode($current_url),
];

// Use a static QR code API for WeChat to avoid heavy JS libraries
$wechat_qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($current_url);
?>
<div id="share_container">
	<div id="share" data-initialized="true">
			<?php if ($options->get('show_sharebtn') != 'abroad') { ?>
			<a class="no-pjax icon-wechat" tooltip="<?php _e('分享到微信', 'argon'); ?>" href="javascript:;" onclick="toggleWechatQr()">
				<button class="btn btn-icon btn-success">
					<span class="btn-inner--icon"><i class="fa fa-weixin"></i></span>
				</button>
				<div id="wechat_qr_popup" class="wechat-qrcode-wrapper" style="display: none;">
					<div class="wechat-qrcode">
						<h4><?php _e('分享到微信', 'argon');?></h4>
						<div class="qrcode">
							<img src="<?php echo $wechat_qr_url; ?>" width="150" height="150" alt="QR Code" />
						</div>
						<div class="help"><?php _e('微信扫描二维码', 'argon');?></div>
					</div>
				</div>
			</a>
			<a target="_blank" class="no-pjax icon-douban" tooltip="<?php _e('分享到豆瓣', 'argon'); ?>" href="<?php echo $share_links['douban']; ?>">
				<button class="btn btn-icon btn-primary" style="background: #209261;border: none;">
					<span aria-hidden="true">豆</span>
				</button>
			</a>
			<a target="_blank" class="no-pjax icon-qq" tooltip="<?php _e('分享到 QQ', 'argon'); ?>" href="<?php echo $share_links['qq']; ?>">
				<button class="btn btn-icon btn-primary" style="background: #2196f3;border: none;">
					<span class="btn-inner--icon"><i class="fa fa-qq"></i></span>
				</button>
			</a>
			<a target="_blank" class="no-pjax icon-qzone" tooltip="<?php _e('分享到 QQ 空间', 'argon'); ?>" href="<?php echo $share_links['qzone']; ?>">
				<button class="btn btn-icon btn-primary" style="background: #ffc107;border: none;">
					<span class="btn-inner--icon"><i class="fa fa-star"></i></span>
				</button>
			</a>
			<a target="_blank" class="no-pjax icon-weibo" tooltip="<?php _e('分享到微博', 'argon'); ?>" href="<?php echo $share_links['weibo']; ?>">
				<button class="btn btn-icon btn-warning">
					<span class="btn-inner--icon"><i class="fa fa-weibo"></i></span>
				</button>
			</a>
		<?php } if ($options->get('show_sharebtn') != 'domestic') { ?>
		<a target="_blank" class="no-pjax icon-facebook" tooltip="<?php _e('分享到 Facebook', 'argon'); ?>" href="<?php echo $share_links['facebook']; ?>">
			<button class="btn btn-icon btn-primary" style="background: #283593;border: none;">
				<span class="btn-inner--icon"><i class="fa fa-facebook"></i></span>
			</button>
		</a>
		<a target="_blank" class="no-pjax icon-twitter" tooltip="<?php _e('分享到 Twitter', 'argon'); ?>" href="<?php echo $share_links['twitter']; ?>">
			<button class="btn btn-icon btn-primary" style="background: #03a9f4;border: none;">
				<span class="btn-inner--icon"><i class="fa fa-twitter"></i></span>
			</button>
		</a>
		<a target="_blank" class="no-pjax icon-telegram" href="https://telegram.me/share/url?url=<?php echo urlencode(get_permalink());?>&text=<?php echo urlencode(html_entity_decode(get_the_title()));?>" tooltip="<?php _e('分享到 Telegram', 'argon'); ?>">
			<button class="btn btn-icon btn-primary" style="background: #42a5f5;border: none;">
				<span class="btn-inner--icon"><i class="fa fa-telegram"></i></span>
			</button>
		</a>
		<?php } ?>
		<a target="_blank" class="no-pjax icon-copy-link" id="share_copy_link" tooltip="<?php _e('复制链接', 'argon'); ?>">
			<button class="btn btn-icon btn-default">
				<span class="btn-inner--icon"><i class="fa fa-link"></i></span>
			</button>
		</a>
	</div>
	<button id="share_show" class="btn btn-icon btn-primary" tooltip="<?php _e('分享', 'argon'); ?>">
		<span class="btn-inner--icon"><i class="fa fa-share"></i></span>
	</button>
</div>
<style>
.wechat-qrcode-wrapper {
	position: absolute;
	bottom: 100%;
	left: 50%;
	transform: translateX(-50%);
	background: #fff;
	padding: 10px;
	border-radius: 4px;
	box-shadow: 0 4px 12px rgba(0,0,0,0.15);
	z-index: 1000;
	margin-bottom: 15px;
}
.wechat-qrcode { text-align: center; width: 170px; }
.wechat-qrcode h4 { font-size: 14px; margin-bottom: 8px; color: #333; }
.wechat-qrcode .qrcode img { display: block; margin: 0 auto; }
.wechat-qrcode .help { font-size: 12px; color: #666; margin-top: 8px; }
</style>
<script type="text/javascript">
	window.toggleWechatQr = function() {
		var popup = document.getElementById("wechat_qr_popup");
		popup.style.display = (popup.style.display === "none" ? "block" : "none");
	};
	document.getElementById("share_show").onclick = function(){
		document.getElementById("share_container").classList.add("opened");
	};
	document.getElementById("share_copy_link").onclick = function(){
		let input = document.createElement('input');
		document.body.appendChild(input);
		input.setAttribute("value", window.location.href);
		input.setAttribute("readonly", "readonly");
		input.setAttribute("style", "opacity: 0;pointer-events:none;");
		input.select();
		if (document.execCommand('copy')){
			if (typeof iziToast !== 'undefined') {
				iziToast.show({
					title: '<?php _e('链接已复制', 'argon');?>',
					message: "<?php _e('链接已复制到剪贴板', 'argon');?>",
					class: 'shadow',
					position: 'topRight',
					backgroundColor: '#2dce89',
					titleColor: '#ffffff',
					messageColor: '#ffffff',
					iconColor: '#ffffff',
					progressBarColor: '#ffffff',
					icon: 'fa fa-check',
					timeout: 5000
				});
			} else {
				alert('<?php _e('链接已复制', 'argon');?>');
			}
		}else{
			alert("<?php _e('请手动复制链接', 'argon');?>");
		}
		document.body.removeChild(input);
	};
</script>
