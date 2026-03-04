<?php
/**
 * Argon Modern theme functions and definitions
 *
 * @package ArgonModern
 * @version 2.0.0
 */

namespace ArgonModern {

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Basic Theme Constants
define( 'ARGON_MODERN_VERSION', '2.0.0' );
define( 'ARGON_MODERN_PATH', get_template_directory() );
define( 'ARGON_MODERN_URL', get_template_directory_uri() );

/**
 * Autoloader for theme classes
 */
spl_autoload_register( function ( $class ) {
	$prefix = 'ArgonModern\\';
	$base_dir = ARGON_MODERN_PATH . '/inc/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

	if ( file_exists( $file ) ) {
		require $file;
	}
} );

/**
 * Initialize the theme
 */
function init() {
	// Add theme support
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'automatic-feed-links' );

	// Initialize classes
	Core::instance();
	Assets::instance();
	Template::instance();
	Shuoshuo::instance();
	Options::instance();
	UserAgent::instance();
	Comments::instance();
	Shortcodes::instance();

	// Flush rewrite rules on theme activation
	add_action( 'after_switch_theme', 'flush_rewrite_rules' );
}
add_action( 'after_setup_theme', __NAMESPACE__ . '\\init' );

}

namespace {
// Include Legacy Settings Page
require_once( ARGON_MODERN_PATH . '/inc/legacy-settings.php' );

// Include Emotions
require_once( ARGON_MODERN_PATH . '/emotions.php' );

	// Define legacy global variables
	$GLOBALS['assets_path'] = \ArgonModern\Assets::get_asset_uri();
	$GLOBALS['theme_version'] = ARGON_MODERN_VERSION;

	// Global functions required by legacy templates (sidebar.php, footer.php)

	function argon_locate_filter($locate){
		return \ArgonModern\Template::locate_filter($locate);
	}

	function argon_get_locate(){
		return \ArgonModern\Template::get_locate();
	}

	function argon_has_post_thumbnail($postID = 0){
		return \ArgonModern\Template::has_post_thumbnail($postID);
	}

	function argon_get_post_thumbnail($postID = 0){
		return \ArgonModern\Template::get_post_thumbnail($postID);
	}

	function get_article_meta($type){
		return \ArgonModern\Template::get_article_meta($type);
	}

	function get_article_reading_time_meta($content){
		return \ArgonModern\Template::get_article_reading_time_meta($content);
	}

	function get_shuoshuo_upvotes($post_id){
		return \ArgonModern\Shuoshuo::get_upvotes($post_id);
	}

	function is_readingtime_meta_hidden(){
		return \ArgonModern\Template::is_readingtime_meta_hidden();
	}

	// Comment Bridge Functions
	function get_comment_upvotes($id) {
		return \ArgonModern\Comments::get_comment_upvotes($id);
	}

	function set_comment_upvotes($id) {
		return \ArgonModern\Comments::set_comment_upvotes($id);
	}

	function is_comment_upvoted($id) {
		return \ArgonModern\Comments::is_comment_upvoted($id);
	}

	function get_comment_captcha_seed($refresh = false) {
		return \ArgonModern\Comments::get_comment_captcha_seed($refresh);
	}

	function get_comment_captcha($seed = null) {
		return \ArgonModern\Comments::get_comment_captcha($seed);
	}

	function get_comment_captcha_answer($seed = null) {
		return \ArgonModern\Comments::get_comment_captcha_answer($seed);
	}

	function argon_get_comment_text($comment_ID = 0, $args = array()) {
		// This one is a bit more complex as it might be used as a filter or directly
		// For now, let's keep it simple or delegate to a method if we implement it
		return get_comment_text($comment_ID, $args);
	}

	function argon_get_comments() {
		return \ArgonModern\Comments::argon_get_comments();
	}

	function get_argon_formatted_comment_paginate_links_for_all_platforms() {
		return \ArgonModern\Comments::get_argon_formatted_comment_paginate_links_for_all_platforms();
	}

	function get_argon_comment_paginate_links_prev_url() {
		return \ArgonModern\Comments::get_argon_comment_paginate_links_prev_url();
	}

	function check_comment_token($id) {
		return \ArgonModern\Comments::check_comment_token($id);
	}

	function check_login_user_same($userid) {
		return \ArgonModern\Comments::check_login_user_same($userid);
	}

	function get_comment_user_id_by_id($comment_ID) {
		return \ArgonModern\Comments::get_comment_user_id_by_id($comment_ID);
	}

	function is_comment_private_mode($id) {
		return \ArgonModern\Comments::is_comment_private_mode($id);
	}

	function user_can_view_comment($id) {
		return \ArgonModern\Comments::user_can_view_comment($id);
	}

	function get_comment_parent_info($comment) {
		return \ArgonModern\Comments::get_comment_parent_info($comment);
	}

	function can_visit_comment_edit_history($id) {
		return \ArgonModern\Comments::can_visit_comment_edit_history($id);
	}

	function is_comment_pinable($id) {
		return \ArgonModern\Comments::is_comment_pinable($id);
	}

	function send_mail($to, $subject, $content) {
		return \ArgonModern\Utils::send_mail($to, $subject, $content);
	}

	function check_email_address($email) {
		return \ArgonModern\Utils::check_email_address($email);
	}

	function format_number_in_kilos($num) {
		return \ArgonModern\Utils::format_number_in_kilos($num);
	}

	function rgb2hsl($R,$G,$B){
		return \ArgonModern\Utils::rgb2hsl($R,$G,$B);
	}

	function hsl2rgb($h,$s,$l){
		return \ArgonModern\Utils::hsl2rgb($h,$s,$l);
	}

	function rgb2hex($r,$g,$b){
		return \ArgonModern\Utils::rgb2hex($r,$g,$b);
	}

	function hexstr2rgb($hex){
		return \ArgonModern\Utils::hexstr2rgb($hex);
	}

	function rgb2str($rgb){
		return \ArgonModern\Utils::rgb2str($rgb);
	}

	function hex2str($hex){
		return \ArgonModern\Utils::hex2str($hex);
	}

	function rgb2gray($R,$G,$B){
		return \ArgonModern\Utils::rgb2gray($R,$G,$B);
	}

	function hex2gray($hex){
		return \ArgonModern\Utils::hex2gray($hex);
	}

	function checkHEX($hex){
		return \ArgonModern\Utils::checkHEX($hex);
	}

	function get_banner_background_url() {
		return \ArgonModern\Template::get_banner_background_url();
	}

	function argon_get_post_outdated_info() {
		return \ArgonModern\Template::get_post_outdated_info();
	}

	function argon_comment_format($comment, $args, $depth) {
		return \ArgonModern\Comments::comment_format($comment, $args, $depth);
	}

	function argon_comment_shuoshuo_preview_format($comment, $args, $depth) {
		return \ArgonModern\Comments::comment_shuoshuo_preview_format($comment, $args, $depth);
	}

	function get_reference_list() {
		return \ArgonModern\Shortcodes::get_reference_list();
	}

	function is_meta_simple(){
		return \ArgonModern\Template::is_meta_simple();
	}

	function get_seo_description(){
		return \ArgonModern\Template::get_seo_description();
	}

	function get_seo_keywords(){
		return \ArgonModern\Template::get_seo_keywords();
	}

	function get_og_image(){
		return \ArgonModern\Template::get_og_image();
	}

	function array_remove(&$arr, $item){
		$pos = array_search($item, $arr);
		if ($pos !== false){
			array_splice($arr, $pos, 1);
		}
	}

	function argon_get_asset_uri($path = ''){
		return \ArgonModern\Assets::get_asset_uri($path);
	}

	function have_catalog(){
		return \ArgonModern\Template::have_catalog();
	}

	function parse_ua_and_icon($userAgent){
		return \ArgonModern\UserAgent::render_ua_info($userAgent);
	}

	if ( ! class_exists( 'toolbarMenuWalker' ) ) {
		class toolbarMenuWalker extends \ArgonModern\ToolbarMenuWalker {}
	}
	if ( ! class_exists( 'leftbarMenuWalker' ) ) {
		class leftbarMenuWalker extends \ArgonModern\LeftbarMenuWalker {}
	}
	if ( ! class_exists( 'leftbarAuthorLinksWalker' ) ) {
		class leftbarAuthorLinksWalker extends \ArgonModern\AuthorLinksWalker {}
	}
	if ( ! class_exists( 'leftbarFriendLinksWalker' ) ) {
		class leftbarFriendLinksWalker extends \ArgonModern\FriendLinksWalker {}
	}
}
