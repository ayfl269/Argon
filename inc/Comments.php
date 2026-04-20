<?php

namespace ArgonModern;

class Comments {
	use Singleton;

	protected function setup() {
		// AJAX Handlers
		add_action( 'wp_ajax_get_comment_edit_history', [ $this, 'get_comment_edit_history' ] );
		add_action( 'wp_ajax_nopriv_get_comment_edit_history', [ $this, 'get_comment_edit_history' ] );

		add_action( 'wp_ajax_upvote_comment', [ $this, 'upvote_comment' ] );
		add_action( 'wp_ajax_nopriv_upvote_comment', [ $this, 'upvote_comment' ] );

		add_action( 'wp_ajax_get_captcha', [ $this, 'ajax_get_captcha' ] );
		add_action( 'wp_ajax_nopriv_get_captcha', [ $this, 'ajax_get_captcha' ] );

		add_action( 'wp_ajax_ajax_post_comment', [ $this, 'ajax_post_comment' ] );
		add_action( 'wp_ajax_nopriv_ajax_post_comment', [ $this, 'ajax_post_comment' ] );

		add_action( 'wp_ajax_user_edit_comment', [ $this, 'user_edit_comment' ] );
		add_action( 'wp_ajax_nopriv_user_edit_comment', [ $this, 'user_edit_comment' ] );

		add_action( 'wp_ajax_pin_comment', [ $this, 'pin_comment' ] );
		add_action( 'wp_ajax_nopriv_pin_comment', [ $this, 'pin_comment' ] );

		// Filters & Actions
		add_filter( 'preprocess_comment', [ $this, 'check_comment_captcha' ] );
		add_filter( 'preprocess_comment', [ $this, 'post_comment_preprocessing' ], 20 );
		add_action( 'comment_post', [ $this, 'post_comment_updatemetas' ] );
		add_action( 'comment_unapproved_to_approved', [ $this, 'comment_mail_notify' ] );
		add_filter( 'get_avatar', [ $this, 'get_avatar_by_qqnumber' ], 10, 1 );

		// Admin side pinning
		add_filter( 'comment_row_actions', [ $this, 'add_comment_row_actions' ], 10, 2 );
		add_action( 'admin_footer', [ $this, 'admin_comment_pin_script' ] );
	}

	/**
	 * Add "Pin" action to admin comment list
	 */
	public function add_comment_row_actions( $actions, $comment ) {
		$options = Options::instance();
		if ( $options->get( "argon_enable_comment_pinning" ) == "true" ) {
			if ( $comment->comment_parent == 0 ) {
				if ( get_comment_meta( $comment->comment_ID, "pinned", true ) == "true" ) {
					$actions['unpin'] = '<a href="javascript:void(0);" onclick="toogleCommentPin(' . $comment->comment_ID . ', false);">' . __( "取消置顶", 'argon' ) . '</a>';
				} else {
					$actions['pin'] = '<a href="javascript:void(0);" onclick="toogleCommentPin(' . $comment->comment_ID . ', true);">' . __( "置顶", 'argon' ) . '</a>';
				}
			}
		}
		return $actions;
	}

	/**
	 * Inline JS for admin comment pinning
	 */
	public function admin_comment_pin_script() {
		global $pagenow;
		if ( $pagenow == 'edit-comments.php' ) {
			?>
			<script>
				function toogleCommentPin(commentID, pinned) {
					var data = {
						action: 'pin_comment',
						nonce: '<?php echo wp_create_nonce( 'argon_nonce' ); ?>',
						id: commentID,
						pinned: pinned ? 'true' : 'false'
					};
					jQuery.post(ajaxurl, data, function(response) {
						if (response.status == 'success') {
							window.location.reload();
						} else {
							alert(response.msg);
						}
					});
				}
			</script>
			<?php
		}
	}

	/**
	 * AJAX: Get comment edit history
	 */
	public function get_comment_edit_history() {
		check_ajax_referer( 'argon_nonce', 'nonce' );
		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		if ( ! $id || ! function_exists( 'can_visit_comment_edit_history' ) || ! can_visit_comment_edit_history( $id ) ) {
			wp_send_json( [
				'id'      => $id,
				'history' => ""
			] );
		}

		$editHistory = json_decode( get_comment_meta( $id, "comment_edit_history", true ) );
		if ( ! is_array( $editHistory ) ) {
			$editHistory = [];
		}
		$editHistory = array_reverse( $editHistory );
		$res         = "";
		$position    = count( $editHistory ) + 1;

		date_default_timezone_set( get_option( 'timezone_string' ) );
		foreach ( $editHistory as $edition ) {
			$position -= 1;
			$edition_content = isset( $edition->content ) ? nl2br( esc_html( $edition->content ) ) : '';
			$res      .= "<div class='comment-edit-history-item'>
						<div class='comment-edit-history-title'>
							<div class='comment-edit-history-id'>
								#" . $position . "
							</div>
							" . ( ( isset( $edition->isfirst ) && $edition->isfirst ) ? "<span class='badge badge-primary badge-admin'>" . __( "最初版本", 'argon' ) . "</span>" : "" ) . "
						</div>
						<div class='comment-edit-history-time'>" . date( 'Y-m-d H:i:s', $edition->time ) . "</div>
						<div class='comment-edit-history-content'>" . $edition_content . "</div>
					</div>";
		}

		wp_send_json( [
			'id'      => $id,
			'history' => $res
		] );
	}

	/**
	 * AJAX: Upvote comment
	 */
	public function upvote_comment() {
		check_ajax_referer( 'argon_nonce', 'nonce' );
		$options = Options::instance();
		if ( $options->get( "argon_enable_comment_upvote", "false" ) != "true" ) {
			return;
		}

		$ID      = isset( $_POST["comment_id"] ) ? intval( $_POST["comment_id"] ) : 0;
		$comment = get_comment( $ID );
		if ( $comment == null ) {
			wp_send_json( [
				'status'       => 'failed',
				'msg'          => __( '评论不存在', 'argon' ),
				'total_upvote' => 0
			] );
		}

		$upvotedList = isset( $_COOKIE['argon_comment_upvoted'] ) ? $_COOKIE['argon_comment_upvoted'] : '';
		if ( in_array( $ID, explode( ',', $upvotedList ) ) ) {
			wp_send_json( [
				'status'       => 'failed',
				'msg'          => __( '该评论已被赞过', 'argon' ),
				'total_upvote' => self::get_comment_upvotes( $ID )
			] );
		}

		self::set_comment_upvotes( $ID );
		setcookie( 'argon_comment_upvoted', $upvotedList . $ID . ",", time() + 31536000, '/', '', is_ssl(), true );

		wp_send_json( [
			'ID'           => $ID,
			'status'       => 'success',
			'msg'          => __( '点赞成功', 'argon' ),
			'total_upvote' => Utils::format_number_in_kilos( self::get_comment_upvotes( $ID ) )
		] );
	}

	/**
	 * AJAX: Get captcha
	 */
	public function ajax_get_captcha() {
		$options = Options::instance();
		if ( $options->get( 'argon_get_captcha_by_ajax', 'false' ) != 'true' ) {
			return;
		}
		wp_send_json( [
			'captcha' => self::get_comment_captcha()
		] );
	}

	/**
	 * AJAX: Post comment
	 */
	public function ajax_post_comment() {
		check_ajax_referer( 'argon_nonce', 'nonce' );
		$parentID = isset( $_POST['comment_parent'] ) ? intval( $_POST['comment_parent'] ) : 0;
		if ( function_exists( 'is_comment_private_mode' ) && is_comment_private_mode( $parentID ) ) {
			if ( ! function_exists( 'user_can_view_comment' ) || ! user_can_view_comment( $parentID ) ) {
				wp_send_json( [
					'status'  => 'failed',
					'msg'     => __( '不能回复其他人的悄悄话评论', 'argon' ),
					'isAdmin' => current_user_can( 'manage_options' )
				] );
			}
		}

		$options = Options::instance();
		if ( $options->get( 'argon_comment_enable_qq_avatar' ) == 'true' ) {
			if ( $this->is_valid_qq_number( isset( $_POST['email'] ) ? wp_unslash( $_POST['email'] ) : '' ) ) {
				$_POST['qq']    = $_POST['email'];
				$_POST['email'] .= "@qq.com";
			} else {
				$_POST['qq'] = "";
			}
		}

		$comment = wp_handle_comment_submission( wp_unslash( $_POST ) );
		if ( is_wp_error( $comment ) ) {
			$msg = $comment->get_error_data();
			if ( ! empty( $msg ) ) {
				$msg = $comment->get_error_message();
			}
			wp_send_json( [
				'status'  => 'failed',
				'msg'     => $msg,
				'isAdmin' => current_user_can( 'manage_options' )
			] );
		}

		$user = wp_get_current_user();
		do_action( 'set_comment_cookies', $comment, $user );

		if ( isset( $_POST['qq'] ) ) {
			if ( ! empty( $_POST['qq'] ) && $options->get( 'argon_comment_enable_qq_avatar' ) == 'true' ) {
				$_comment                        = clone $comment;
				$_comment->comment_author_email = $_POST['qq'] . "@avatarqq.com";
				do_action( 'set_comment_cookies', $_comment, $user );
			}
		}

		$html = wp_list_comments(
			[
				'type'     => 'comment',
				'callback' => 'argon_comment_format',
				'echo'     => false
			],
			[ $comment ]
		);

		$newCaptchaSeed = self::get_comment_captcha_seed( true );
		$newCaptcha     = self::get_comment_captcha( $newCaptchaSeed );
		if ( current_user_can( 'manage_options' ) ) {
			$newCaptchaAnswer = self::get_comment_captcha_answer( $newCaptchaSeed );
		} else {
			$newCaptchaAnswer = "";
		}

		wp_send_json( [
			'status'           => 'success',
			'html'             => $html,
			'id'               => $comment->comment_ID,
			'parentID'         => $comment->comment_parent,
			'commentOrder'     => ( get_option( "comment_order" ) == "" ? "desc" : get_option( "comment_order" ) ),
			'newCaptchaSeed'   => $newCaptchaSeed,
			'newCaptcha'       => $newCaptcha,
			'newCaptchaAnswer' => $newCaptchaAnswer,
			'isAdmin'          => current_user_can( 'manage_options' ),
			'isLogin'          => is_user_logged_in()
		] );
	}

	/**
	 * AJAX: Edit comment
	 */
	public function user_edit_comment() {
		check_ajax_referer( 'argon_nonce', 'nonce' );
		$options = Options::instance();
		if ( $options->get( "argon_comment_allow_editing" ) == "false" ) {
			wp_send_json( [
				'status' => 'failed',
				'msg'    => __( '博主关闭了编辑评论功能', 'argon' )
			] );
		}

		$id            = isset( $_POST["id"] ) ? intval( $_POST["id"] ) : 0;
		$contentSource = isset( $_POST["comment"] ) ? $_POST["comment"] : '';

		if ( ! function_exists( 'check_comment_token' ) || ! function_exists( 'check_login_user_same' ) || ! function_exists( 'get_comment_user_id_by_id' ) ) {
			wp_send_json( [ 'status' => 'failed', 'msg' => 'Internal logic missing' ] );
		}

		if ( ! check_comment_token( $id ) && ! check_login_user_same( get_comment_user_id_by_id( $id ) ) ) {
			wp_send_json( [
				'status' => 'failed',
				'msg'    => __( '您不是这条评论的作者或 Token 已过期', 'argon' )
			] );
		}

		if ( $contentSource == "" ) {
			wp_send_json( [
				'status' => 'failed',
				'msg'    => __( '新的评论为空', 'argon' )
			] );
		}

		$content = $contentSource;
		if ( get_comment_meta( $id, "use_markdown", true ) == "true" ) {
			$content = self::comment_markdown_parse( $content );
		} else {
			$content = wp_kses_post( $content );
		}

		$res = wp_update_comment( [
			'comment_ID'      => $id,
			'comment_content' => $content
		] );

		if ( $res == 1 ) {
			update_comment_meta( $id, "comment_content_source", $contentSource );
			update_comment_meta( $id, "edited", "true" );
			// 保存编辑历史
			$editHistory = json_decode( get_comment_meta( $id, "comment_edit_history", true ) );
			if ( is_null( $editHistory ) ) {
				$editHistory = [];
			}
			array_push( $editHistory, [
				'content' => htmlspecialchars( stripslashes( $contentSource ) ),
				'time'    => time(),
				'isfirst' => false
			] );
			update_comment_meta( $id, "comment_edit_history", wp_slash( wp_json_encode( $editHistory, JSON_UNESCAPED_UNICODE ) ) );

			wp_send_json( [
				'status'                 => 'success',
				'msg'                    => __( '编辑评论成功', 'argon' ),
				'new_comment'            => apply_filters( 'comment_text', function_exists( 'argon_get_comment_text' ) ? argon_get_comment_text( $id ) : get_comment_text( $id ), $id ),
				'new_comment_source'     => htmlspecialchars( stripslashes( $contentSource ) ),
				'can_visit_edit_history' => can_visit_comment_edit_history( $id )
			] );
		} else {
			wp_send_json( [
				'status' => 'failed',
				'msg'    => __( '编辑评论失败，可能原因: 与原评论相同', 'argon' ),
			] );
		}
	}

	/**
	 * AJAX: Pin comment
	 */
	public function pin_comment() {
		check_ajax_referer( 'argon_nonce', 'nonce' );
		$options = Options::instance();
		if ( $options->get( "argon_enable_comment_pinning" ) == "false" ) {
			wp_send_json( [
				'status' => 'failed',
				'msg'    => __( '博主关闭了评论置顶功能', 'argon' )
			] );
		}

		if ( ! current_user_can( "moderate_comments" ) ) {
			wp_send_json( [
				'status' => 'failed',
				'msg'    => __( '您没有权限进行此操作', 'argon' )
			] );
		}

		$id             = isset( $_POST["id"] ) ? intval( $_POST["id"] ) : 0;
		$newPinnedStat  = isset( $_POST["pinned"] ) && $_POST["pinned"] == "true";
		$origPinnedStat = get_comment_meta( $id, "pinned", true ) == "true";

		if ( $newPinnedStat == $origPinnedStat ) {
			wp_send_json( [
				'status' => 'failed',
				'msg'    => $newPinnedStat ? __( '评论已经是置顶状态', 'argon' ) : __( '评论已经是取消置顶状态', 'argon' )
			] );
		}

		if ( get_comment( $id )->comment_parent != 0 ) {
			wp_send_json( [
				'status' => 'failed',
				'msg'    => __( '不能置顶子评论', 'argon' )
			] );
		}

		if ( function_exists( 'is_comment_private_mode' ) && is_comment_private_mode( $id ) ) {
			wp_send_json( [
				'status' => 'failed',
				'msg'    => __( '不能置顶悄悄话', 'argon' )
			] );
		}

		update_comment_meta( $id, "pinned", $newPinnedStat ? "true" : "false" );
		wp_send_json( [
			'status' => 'success',
			'msg'    => $newPinnedStat ? __( '置顶评论成功', 'argon' ) : __( '取消置顶成功', 'argon' ),
		] );
	}

	// --- Captcha Logic ---

	public static function get_comment_captcha_seed( $refresh = false ) {
		if ( ! session_id() ) {
			if ( ! headers_sent() ) {
				session_set_cookie_params( [
					'lifetime' => 0,
					'path'     => '/',
					'domain'   => $_SERVER['HTTP_HOST'],
					'secure'   => is_ssl(),
					'httponly' => true,
					'samesite' => 'Strict',
				] );
				session_start();
			}
		}
		if ( isset( $_SESSION['captchaSeed'] ) && ! $refresh ) {
			return $_SESSION['captchaSeed'];
		}
		$captchaSeed             = bin2hex( random_bytes( 16 ) );
		$_SESSION['captchaSeed'] = $captchaSeed;
		
		// Pre-calculate answer and store it in session for better security
		$captcha = new CaptchaHelper( $captchaSeed );
		$_SESSION['captcha_answer'] = (string) $captcha->getAnswer();
		
		session_write_close();
		return $captchaSeed;
	}

	public static function get_comment_captcha( $seed = null ) {
		if ( is_null( $seed ) ) {
			$seed = self::get_comment_captcha_seed();
		}
		$captcha = new CaptchaHelper( $seed );
		return $captcha->getChallenge();
	}

	public static function get_comment_captcha_answer( $seed = null ) {
		if ( is_null( $seed ) ) {
			$seed = self::get_comment_captcha_seed();
		}
		$captcha = new CaptchaHelper( $seed );
		return $captcha->getAnswer();
	}

	public function check_comment_captcha( $comment ) {
		$options = Options::instance();
		if ( $options->get( 'argon_comment_need_captcha' ) == 'false' ) {
			return $comment;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return $comment;
		}

		$user_ip = $_SERVER['REMOTE_ADDR'];
		$ip_lock_key = 'argon_captcha_lock_' . md5( $user_ip );

		// Initialize session if not already done
		if ( ! session_id() && ! headers_sent() ) {
			session_start();
		}

		// Check Lock Status (Session or IP-based transient)
		$is_locked = ( isset( $_SESSION['captcha_lock_until'] ) && $_SESSION['captcha_lock_until'] > time() );
		$ip_lock_until = get_transient( $ip_lock_key );
		
		if ( $is_locked || $ip_lock_until ) {
			$remaining = 0;
			if ( $is_locked ) {
				$remaining = $_SESSION['captcha_lock_until'] - time();
			} else {
				$remaining = $ip_lock_until - time();
			}
			
			// Refresh captcha even on lock to show they are stalled
			$newSeed = self::get_comment_captcha_seed( true );
			wp_send_json( [
				'status'           => 'failed',
				'msg'              => sprintf( __( '验证码错误次数过多，请在 %d 秒后重试', 'argon' ), $remaining ),
				'isAdmin'          => current_user_can( 'manage_options' ),
				'newCaptchaSeed'   => $newSeed,
				'newCaptcha'       => self::get_comment_captcha( $newSeed )
			] );
		}

		$answer = isset( $_POST['comment_captcha'] ) ? trim( (string) $_POST['comment_captcha'] ) : '';
		$correct_answer = isset( $_SESSION['captcha_answer'] ) ? (string) $_SESSION['captcha_answer'] : '';

		if ( $answer === '' || $correct_answer === '' || $answer !== $correct_answer ) {
			$fail_count = isset( $_SESSION['captcha_fail_count'] ) ? (int) $_SESSION['captcha_fail_count'] : 0;
			$fail_count++;
			$_SESSION['captcha_fail_count'] = $fail_count;

			if ( $fail_count >= 5 ) {
				$lock_duration = 300; // 5 minutes
				$_SESSION['captcha_lock_until'] = time() + $lock_duration;
				set_transient( $ip_lock_key, time() + $lock_duration, $lock_duration );
				$_SESSION['captcha_fail_count'] = 0;
				
				$newSeed = self::get_comment_captcha_seed( true );
				wp_send_json( [
					'status'           => 'failed',
					'msg'              => __( '验证码错误次数过多，评论功能已锁定 5 分钟', 'argon' ),
					'isAdmin'          => current_user_can( 'manage_options' ),
					'newCaptchaSeed'   => $newSeed,
					'newCaptcha'       => self::get_comment_captcha( $newSeed )
				] );
			}

			$newSeed = self::get_comment_captcha_seed( true );
			wp_send_json( [
				'status'           => 'failed',
				'msg'              => sprintf( __( '验证码错误 (还剩 %d 次机会)', 'argon' ), 5 - $fail_count ),
				'isAdmin'          => current_user_can( 'manage_options' ),
				'newCaptchaSeed'   => $newSeed,
				'newCaptcha'       => self::get_comment_captcha( $newSeed )
			] );
		}

		// Success: Reset fail counter and remove locks
		$_SESSION['captcha_fail_count'] = 0;
		if ( isset( $_SESSION['captcha_lock_until'] ) ) {
			unset( $_SESSION['captcha_lock_until'] );
		}
		delete_transient( $ip_lock_key );

		return $comment;
	}

	// --- Comment Processing ---

	public static function comment_markdown_parse( $comment_content ) {
		if ( ! class_exists( '_Parsedown' ) ) {
			$parsedown_path = ARGON_MODERN_PATH . '/parsedown.php';
			if ( file_exists( $parsedown_path ) ) {
				require_once $parsedown_path;
			}
		}

		if ( class_exists( '_Parsedown' ) ) {
			$parsedown = new \_Parsedown();
			$parsedown->setSafeMode( true );
			$parsedown->setMarkupEscaped( true );
			$res = $parsedown->text( $comment_content );
		} else {
			$res = $comment_content;
		}

		// Strictly filter HTML
		$allowed_html = wp_kses_allowed_html( 'post' );
		$res = wp_kses( $res, $allowed_html );

		// Safely add target="_blank" and rel attributes
		$res = preg_replace(
			'/<a([^>]*?)>/i',
			'<a$1 rel="noopener noreferrer nofollow" target="_blank">',
			$res
		);

		return $res;
	}

	public function post_comment_preprocessing( $comment ) {
		$_POST['comment_content_source'] = $comment['comment_content'];
		$options                         = Options::instance();
		if ( isset( $_POST['use_markdown'] ) && $_POST['use_markdown'] == 'true' && $options->get( "argon_comment_allow_markdown" ) != "false" ) {
			$comment['comment_content'] = self::comment_markdown_parse( $comment['comment_content'] );
		}
		return $comment;
	}

	public function post_comment_updatemetas( $id ) {
		$parentID    = isset( $_POST['comment_parent'] ) ? intval( $_POST['comment_parent'] ) : 0;
		$comment     = get_comment( $id );
		$options     = Options::instance();

		// 评论 Markdown 源码
		update_comment_meta( $id, "comment_content_source", isset( $_POST['comment_content_source'] ) ? $_POST['comment_content_source'] : '' );

		// 评论者 Token
		if ( function_exists( 'set_user_token_cookie' ) ) {
			set_user_token_cookie();
		}
		if ( isset( $_COOKIE["argon_user_token"] ) ) {
			update_comment_meta( $id, "user_token", $_COOKIE["argon_user_token"] );
		}

		// 保存初次编辑记录
		$editHistory = [ [
			'content' => isset( $_POST['comment_content_source'] ) ? $_POST['comment_content_source'] : '',
			'time'    => time(),
			'isfirst' => true
		] ];
		update_comment_meta( $id, "comment_edit_history", wp_slash( wp_json_encode( $editHistory, JSON_UNESCAPED_UNICODE ) ) );

		// 是否启用 Markdown
		if ( isset( $_POST['use_markdown'] ) && $_POST['use_markdown'] == 'true' && $options->get( "argon_comment_allow_markdown" ) != "false" ) {
			update_comment_meta( $id, "use_markdown", "true" );
		} else {
			update_comment_meta( $id, "use_markdown", "false" );
		}

		// 是否启用悄悄话模式
		if ( isset( $_POST['private_mode'] ) && $_POST['private_mode'] == 'true' && $options->get( "argon_comment_allow_privatemode" ) == "true" ) {
			update_comment_meta( $id, "private_mode", isset( $_COOKIE["argon_user_token"] ) ? $_COOKIE["argon_user_token"] : 'false' );
		} else {
			update_comment_meta( $id, "private_mode", "false" );
		}

		if ( function_exists( 'is_comment_private_mode' ) ) {
			if ( is_comment_private_mode( $parentID ) ) {
				update_comment_meta( $id, "private_mode", get_comment_meta( $parentID, "private_mode", true ) );
			}
			if ( $parentID != 0 && ! is_comment_private_mode( $parentID ) ) {
				update_comment_meta( $id, "private_mode", "false" );
			}
		}

		// 是否启用邮件通知
		if ( isset( $_POST['enable_mailnotice'] ) && $_POST['enable_mailnotice'] == 'true' && $options->get( "argon_comment_allow_mailnotice" ) == "true" ) {
			update_comment_meta( $id, "enable_mailnotice", "true" );
			if ( function_exists( 'get_random_token' ) ) {
				update_comment_meta( $id, "mailnotice_unsubscribe_key", get_random_token() );
			}
		} else {
			update_comment_meta( $id, "enable_mailnotice", "false" );
		}

		// 向父级评论发送邮件
		if ( $comment->comment_approved == 1 ) {
			$this->comment_mail_notify( $comment );
		}

		// 保存 QQ 号
		if ( $options->get( 'argon_comment_enable_qq_avatar' ) == 'true' ) {
			if ( ! empty( $_POST['qq'] ) ) {
				$qq_number = preg_replace( '/\D+/', '', wp_unslash( $_POST['qq'] ) );
				if ( $qq_number !== '' ) {
					update_comment_meta( $id, "qq_number", $qq_number );
				}
			}
		}
	}

	public function comment_mail_notify( $comment ) {
		// 速率限制，防止垃圾邮件滥发请求
		$rate_limit_key = 'argon_mail_notice_limit_' . md5( $_SERVER['REMOTE_ADDR'] );
		if ( get_transient( $rate_limit_key ) ) {
			return;
		}
		set_transient( $rate_limit_key, 1, 10 ); // 10 秒限制
		
		$options = Options::instance();
		if ( $options->get( "argon_comment_allow_mailnotice" ) != "true" ) {
			return;
		}
		if ( is_numeric( $comment ) ) {
			$comment = get_comment( $comment );
		}
		if ( $comment == null ) {
			return;
		}
		$id            = $comment->comment_ID;
		$commentPostID = $comment->comment_post_ID;
		$commentAuthor = $comment->comment_author;
		$parentID      = $comment->comment_parent;
		if ( $parentID == 0 ) {
			return;
		}
		$parentComment = get_comment( $parentID );
		$parentEmail   = $parentComment->comment_author_email;
		$parentName    = $parentComment->comment_author;
		$emailTo       = "$parentName <$parentEmail>";

		if ( get_comment_meta( $parentID, "enable_mailnotice", true ) == "true" ) {
			if ( function_exists( 'check_email_address' ) && check_email_address( $parentEmail ) ) {
				$post_title = function_exists( 'get_post_title_by_id' ) ? get_post_title_by_id( $commentPostID ) : get_the_title( $commentPostID );
				$title      = __( "您在", 'argon' ) . " 「" . wp_trim_words( $post_title, 20 ) . "」 " . __( "的评论有了新的回复", 'argon' );
				$fullTitle  = __( "您在", 'argon' ) . " 「" . $post_title . "」 " . __( "的评论有了新的回复", 'argon' );
				$content    = htmlspecialchars( get_comment_meta( $id, "comment_content_source", true ) );
				$link       = get_permalink( $commentPostID ) . "#comment-" . $id;
				$unsubscribeLink = site_url( "unsubscribe-comment-mailnotice?comment=" . $parentID . "&token=" . get_comment_meta( $parentID, "mailnotice_unsubscribe_key", true ) );

				$html = '
						<!DOCTYPE html>
						<html>
							<head>
								<meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
							</head>
							<body>
								<div style="background: #fff;box-shadow: 0 15px 35px rgba(50,50,93,.1), 0 5px 15px rgba(0,0,0,.07);border-radius: 6px;margin: 15px auto 50px auto;padding: 35px 30px;max-width: min(calc(100% - 100px), 1200px);">
									<div style="font-size:30px;text-align:center;margin-bottom:15px;">' . htmlspecialchars( $fullTitle ) . '</div>
									<div style="background: rgba(0, 0, 0, .15);height: 1px;width: 300px;margin: auto;margin-bottom: 35px;"></div>
									<div style="font-size: 18px;border-left: 4px solid rgba(0, 0, 0, .15);width: max-content;width: -moz-max-content;margin: auto;padding: 20px 30px;background: rgba(0,0,0,.08);border-radius: 6px;box-shadow: 0 2px 4px rgba(0,0,0,.075)!important;min-width: 60%;max-width: 90%;margin-bottom: 40px;">
										<div style="margin-bottom: 10px;"><strong><span style="color: #5e72e4;">@' . htmlspecialchars( $commentAuthor ) . '</span> ' . __( '回复了你', "argon" ) . ':</strong></div>
										' . str_replace( '\n', '<div></div>', $content ) . ' 
									</div>
									<table width="100%" style="border-collapse:collapse;border:none;empty-cells:show;max-width:100%;box-sizing:border-box" cellspacing="0" cellpadding="0">
										<tbody style="box-sizing:border-box">
											<tr style="box-sizing:border-box" align="center">
												<td style="min-width:5px;box-sizing:border-box">
													<table style="border-collapse:collapse;border:none;empty-cells:show;max-width:100%;box-sizing:border-box" cellspacing="0" cellpadding="0">
														<tbody style="box-sizing:border-box">
															<tr style="box-sizing:border-box">
																<td style="box-sizing:border-box">
																	<a href="' . $link . '" style="display: block; line-height: 1; color: #fff;background-color: #5e72e4;border-color: #5e72e4;box-shadow: 0 4px 6px rgba(50,50,93,.11), 0 1px 3px rgba(0,0,0,.08);padding: 15px 25px;font-size: 18px;border-radius: 4px;text-decoration: none; margin: 10px;">' . __( '前往查看', "argon" ) . '</a>
																</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
									<table width="100%" style="border-collapse:collapse;border:none;empty-cells:show;max-width:100%;box-sizing:border-box" cellspacing="0" cellpadding="0">
										<tbody style="box-sizing:border-box">
											<tr style="box-sizing:border-box" align="center">
												<td style="min-width:5px;box-sizing:border-box">
													<table style="border-collapse:collapse;border:none;empty-cells:show;max-width:100%;box-sizing:border-box" cellspacing="0" cellpadding="0">
														<tbody style="box-sizing:border-box">
															<tr style="box-sizing:border-box">
																<td style="box-sizing:border-box">
																	<a href="' . $unsubscribeLink . '" style="display: block; line-height: 1;color: #5e72e4;font-size: 16px;text-decoration: none; margin: 10px;">' . __( '退订该评论的邮件提醒', "argon" ) . '</a>
																</td>
															</tr>
														</tbody>
													</table>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</body>
						</html>';
				$html = apply_filters( "argon_comment_mail_notification_content", $html );
				if ( function_exists( 'send_mail' ) ) {
					send_mail( $emailTo, $title, $html );
				}
			}
		}
	}

	public function get_avatar_by_qqnumber( $avatar ) {
		global $comment;
		if ( ! isset( $comment ) || ! isset( $comment->comment_ID ) ) {
			return $avatar;
		}
		$qqnumber = preg_replace( '/\D+/', '', (string) get_comment_meta( $comment->comment_ID, 'qq_number', true ) );
		if ( ! empty( $qqnumber ) ) {
			preg_match_all( '/width=\'(.*?)\'/', $avatar, $preg_res );
			$size = isset( $preg_res[1][0] ) ? absint( $preg_res[1][0] ) : 40;
			if ( $size <= 0 ) {
				$size = 40;
			}
			$avatar_url = esc_url( 'https://q1.qlogo.cn/g?b=qq&s=640&nk=' . $qqnumber );
			return "<img src='" . $avatar_url . "' class='avatar avatar-" . esc_attr( (string) $size ) . " photo' width='" . esc_attr( (string) $size ) . "' height='" . esc_attr( (string) $size ) . "'>";
		}
		return $avatar;
	}

	private function is_valid_qq_number( $value ) {
		$value = trim( (string) $value );

		if ( function_exists( 'check_qqnumber' ) ) {
			return check_qqnumber( $value );
		}

		return preg_match( '/^[1-9][0-9]{4,10}$/', $value ) === 1;
	}

	// --- Internal Helpers ---

	public static function get_comment_upvotes( $id ) {
		$comment = get_comment( $id );
		if ( $comment == null ) {
			return 0;
		}
		$upvotes = get_comment_meta( $comment->comment_ID, "upvotes", true );
		if ( $upvotes == null ) {
			$upvotes = 0;
		}
		return $upvotes;
	}

	public static function set_comment_upvotes( $id ) {
		$comment = get_comment( $id );
		if ( $comment == null ) {
			return 0;
		}
		$upvotes = get_comment_meta( $comment->comment_ID, "upvotes", true );
		if ( $upvotes == null ) {
			$upvotes = 0;
		}
		$upvotes++;
		update_comment_meta( $comment->comment_ID, "upvotes", $upvotes );
		return $upvotes;
	}

	public static function check_comment_token( $id ) {
		if ( ! isset( $_COOKIE['argon_user_token'] ) || strlen( $_COOKIE['argon_user_token'] ) != 32 ) {
			return false;
		}
		if ( $_COOKIE['argon_user_token'] != get_comment_meta( $id, "user_token", true ) ) {
			return false;
		}
		return true;
	}

	public static function check_login_user_same( $userid ) {
		if ( $userid == 0 ) {
			return false;
		}
		if ( $userid != ( wp_get_current_user()->ID ) ) {
			return false;
		}
		return true;
	}

	public static function get_comment_user_id_by_id( $comment_ID ) {
		$comment = get_comment( $comment_ID );
		return $comment ? $comment->user_id : 0;
	}

	public static function is_comment_private_mode( $id ) {
		$private_mode = get_comment_meta( $id, "private_mode", true );
		if ( ! $private_mode || strlen( $private_mode ) != 32 ) {
			return false;
		}
		return true;
	}

	public static function user_can_view_comment( $id ) {
		if ( ! self::is_comment_private_mode( $id ) ) {
			return true;
		}
		if ( current_user_can( "manage_options" ) ) {
			return true;
		}
		if ( isset( $_COOKIE['argon_user_token'] ) && $_COOKIE['argon_user_token'] == get_comment_meta( $id, "private_mode", true ) ) {
			return true;
		}
		return false;
	}

	public static function get_comment_parent_info( $comment ) {
		$options = Options::instance();
		if ( $options->get( "argon_show_comment_parent_info", "true" ) != "true" ) {
			return "";
		}
		if ( $comment->comment_parent == 0 ) {
			return "";
		}
		$parent_comment = get_comment( $comment->comment_parent );
		if ( ! $parent_comment ) {
			return "";
		}
		return '<div class="comment-parent-info" data-parent-id=' . $parent_comment->comment_ID . '><i class="fa fa-reply" aria-hidden="true"></i> ' . get_comment_author( $parent_comment->comment_ID ) . '</div>';
	}

	public static function can_visit_comment_edit_history( $id ) {
		$options                            = Options::instance();
		$who_can_visit_comment_edit_history = $options->get( "argon_who_can_visit_comment_edit_history" );
		if ( $who_can_visit_comment_edit_history == "" ) {
			$who_can_visit_comment_edit_history = "admin";
		}
		switch ( $who_can_visit_comment_edit_history ) {
			case 'everyone':
				return true;

			case 'commentsender':
				if ( self::check_comment_token( $id ) || self::check_login_user_same( self::get_comment_user_id_by_id( $id ) ) ) {
					return true;
				}
				return false;

			case 'admin':
			default:
				return current_user_can( "manage_options" );
		}
	}

	public static function is_comment_pinable( $id ) {
		$comment = get_comment( $id );
		if ( ! $comment || $comment->comment_approved != "1" ) {
			return false;
		}
		if ( $comment->comment_parent != 0 ) {
			return false;
		}
		if ( self::is_comment_private_mode( $id ) ) {
			return false;
		}
		return true;
	}

	public static function is_comment_upvoted( $id ) {
		$upvotedList = isset( $_COOKIE['argon_comment_upvoted'] ) ? $_COOKIE['argon_comment_upvoted'] : '';
		if ( in_array( $id, explode( ',', $upvotedList ) ) ) {
			return true;
		}
		return false;
	}

	public static function argon_get_comments() {
		global $wp_query;
		$args = [
			'post__in' => [ get_the_ID() ],
			'type'     => 'comment',
			'order'    => 'DESC',
			'orderby'  => 'comment_date_gmt',
			'status'   => 'approve'
		];
		if ( is_user_logged_in() ) {
			$args['include_unapproved'] = [ get_current_user_id() ];
		} else {
			$unapproved_email = wp_get_unapproved_comment_author_email();
			if ( $unapproved_email ) {
				$args['include_unapproved'] = [ $unapproved_email ];
			}
		}

		$comment_query = new \WP_Comment_Query;
		$comments      = $comment_query->query( $args );

		$options = Options::instance();
		if ( $options->get( "argon_enable_comment_pinning", "false" ) == "true" ) {
			usort( $comments, [ __CLASS__, 'argon_comment_cmp' ] );
		} else {
			$comments = array_reverse( $comments );
		}

		// 向评论数组中填充 placeholder comments 以填满第一页
		if ( $options->get( "argon_comment_pagination_type", "feed" ) == "page" ) {
			return $comments;
		}
		if ( ! isset( $_GET['fill_first_page'] ) && isset( $_SERVER['REQUEST_URI'] ) && strpos( parse_url( $_SERVER['REQUEST_URI'] )['path'], 'comment-page-' ) !== false ) {
			return $comments;
		}
		$comments_per_page = get_option( 'comments_per_page' );
		$comments_count    = 0;
		foreach ( $comments as $comment ) {
			if ( $comment->comment_parent == 0 ) {
				$comments_count++;
			}
		}
		$comments_pages = ceil( $comments_count / $comments_per_page );
		if ( $comments_pages > 1 ) {
			$placeholders_count = $comments_pages * $comments_per_page - $comments_count;
			while ( $placeholders_count-- ) {
				array_unshift( $comments, new \WP_Comment( (object) [
					"placeholder" => true
				] ) );
			}
		}
		return $comments;
	}

	public static function argon_comment_cmp( $a, $b ) {
		$a_pinned = get_comment_meta( $a->comment_ID, 'pinned', true );
		$b_pinned = get_comment_meta( $b->comment_ID, 'pinned', true );
		if ( $a_pinned != "true" ) {
			$a_pinned = "false";
		}
		if ( $b_pinned != "true" ) {
			$b_pinned = "false";
		}

		$comment_order = get_option( 'comment_order' );

		if ( $a_pinned == $b_pinned ) {
			return ( $a->comment_date_gmt ) > ( $b->comment_date_gmt );
		} else {
			if ( $a_pinned == "true" ) {
				return ( $comment_order == 'desc' );
			} else {
				return ( $comment_order != 'desc' );
			}
		}
	}

	public static function get_argon_formatted_comment_paginate_links( $maxPageNumbers, $extraClasses = '' ) {
		$args = [
			'prev_text'          => '',
			'next_text'          => '',
			'before_page_number' => '',
			'after_page_number'  => '',
			'show_all'           => true,
			'echo'               => false
		];
		$res  = paginate_comments_links( $args );
		// 单引号转双引号 & 去除上一页和下一页按钮
		$res = preg_replace( '/\'/', '"', $res );
		$res = preg_replace( '/<a class="prev page-numbers" href="(.*?)">(.*?)<\/a>/', '', $res );
		$res = preg_replace( '/<a class="next page-numbers" href="(.*?)">(.*?)<\/a>/', '', $res );
		// 寻找所有页码标签
		preg_match_all( '/<(.*?)>(.*?)<\/(.*?)>/', $res, $pages );
		$total   = count( $pages[0] );
		$current = 0;
		$urls    = [];
		for ( $i = 0; $i < $total; $i++ ) {
			if ( preg_match( '/<span(.*?)>(.*?)<\/span>/', $pages[0][ $i ] ) ) {
				$current = $i + 1;
			} else {
				preg_match( '/<a(.*?)href="(.*?)">(.*?)<\/a>/', $pages[0][ $i ], $tmp );
				$urls[ $i + 1 ] = $tmp[2];
			}
		}

		if ( $total == 0 ) {
			return "";
		}

		// 计算页码起始
		$from = max( $current - ( $maxPageNumbers - 1 ) / 2, 1 );
		$to   = min( $current + $maxPageNumbers - ( $current - $from + 1 ), $total );
		if ( $to - $from + 1 < $maxPageNumbers ) {
			$to   = min( $current + ( $maxPageNumbers - 1 ) / 2, $total );
			$from = max( $current - ( $maxPageNumbers - ( $to - $current + 1 ) ), 1 );
		}
		// 生成新页码
		$html = "";
		if ( $from > 1 ) {
			$html .= '<li class="page-item"><div aria-label="First Page" class="page-link" href="' . $urls[1] . '"><i class="fa fa-angle-double-left" aria-hidden="true"></i></div></li>';
		}
		if ( $current > 1 ) {
			$html .= '<li class="page-item"><div aria-label="Previous Page" class="page-link" href="' . $urls[ $current - 1 ] . '"><i class="fa fa-angle-left" aria-hidden="true"></i></div></li>';
		}
		for ( $i = $from; $i <= $to; $i++ ) {
			if ( $current == $i ) {
				$html .= '<li class="page-item active"><span class="page-link" style="cursor: default;">' . $i . '</span></li>';
			} else {
				$html .= '<li class="page-item"><div class="page-link" href="' . $urls[ $i ] . '">' . $i . '</div></li>';
			}
		}
		if ( $current < $total ) {
			$html .= '<li class="page-item"><div aria-label="Next Page" class="page-link" href="' . $urls[ $current + 1 ] . '"><i class="fa fa-angle-right" aria-hidden="true"></i></div></li>';
		}
		if ( $to < $total ) {
			$html .= '<li class="page-item"><div aria-label="Last Page" class="page-link" href="' . $urls[ $total ] . '"><i class="fa fa-angle-double-right" aria-hidden="true"></i></div></li>';
		}
		return '<nav id="comments_navigation" class="comments-navigation"><ul class="pagination' . $extraClasses . '">' . $html . '</ul></nav>';
	}

	public static function get_argon_formatted_comment_paginate_links_for_all_platforms() {
		return self::get_argon_formatted_comment_paginate_links( 7 ) . self::get_argon_formatted_comment_paginate_links( 5, " pagination-mobile" );
	}

	public static function get_argon_comment_paginate_links_prev_url() {
		$args = [
			'prev_text'          => '',
			'next_text'          => '',
			'before_page_number' => '',
			'after_page_number'  => '',
			'show_all'           => true,
			'echo'               => false
		];
		$str  = paginate_comments_links( $args );
		// 单引号转双引号
		$str = preg_replace( '/\'/', '"', $str );
		// 获取上一页地址
		$url = [];
		preg_match( '/<a class="prev page-numbers" href="(.*?)">(.*?)<\/a>/', $str, $url );
		if ( ! isset( $url[1] ) ) {
			return null;
		}

		if ( isset( $_GET['fill_first_page'] ) || ( isset( $_SERVER['REQUEST_URI'] ) && strpos( parse_url( $_SERVER['REQUEST_URI'] )['path'], 'comment-page-' ) === false ) ) {
			$parsed_url = parse_url( $url[1] );
			if ( ! isset( $parsed_url['query'] ) ) {
				$parsed_url['query'] = 'fill_first_page=true';
			} elseif ( strpos( $parsed_url['query'], 'fill_first_page=true' ) === false ) {
				$parsed_url['query'] .= '&fill_first_page=true';
			}
			return ( isset( $parsed_url['scheme'] ) ? $parsed_url['scheme'] . '://' : '' ) .
			       ( isset( $parsed_url['host'] ) ? $parsed_url['host'] : '' ) .
			       ( isset( $parsed_url['path'] ) ? $parsed_url['path'] : '' ) .
			       '?' . $parsed_url['query'];
		}
		return $url[1];
	}

	public static function comment_format( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		$comment_id         = get_comment_ID();
		$all_meta           = get_comment_meta( $comment_id );
		
		$options            = Options::instance();
		$enable_upvote      = $options->get( "argon_enable_comment_upvote", "false" ) == "true";
		$enable_pinning     = $options->get( "argon_enable_comment_pinning", "false" ) == "true";
		$can_moderate       = current_user_can( 'moderate_comments' );

		// Helper to safely get meta from the pre-fetched array
		$get_meta = function( $key ) use ( $all_meta ) {
			return isset( $all_meta[$key][0] ) ? $all_meta[$key][0] : '';
		};

		if ( ! ( isset( $comment->placeholder ) && $comment->placeholder ) && ( ! function_exists( 'user_can_view_comment' ) || user_can_view_comment( $comment_id ) ) ) {
			?>
			<li class="comment-item" id="comment-<?php comment_ID(); ?>" data-id="<?php comment_ID(); ?>" data-use-markdown="<?php echo $get_meta( "use_markdown" ); ?>">
				<div class="comment-item-left-wrapper">
					<div class="comment-item-avatar">
						<?php if ( function_exists( 'get_avatar' ) && get_option( 'show_avatars' ) ) {
							echo get_avatar( $comment, 40 );
						} ?>
					</div>
					<?php if ( $enable_upvote ) { ?>
						<button class="comment-upvote btn btn-icon btn-outline-primary btn-sm <?php echo ( self::is_comment_upvoted( get_comment_ID() ) ? 'upvoted' : '' ); ?>" type="button" data-id="<?php comment_ID(); ?>">
							<span class="btn-inner--icon"><i class="fa fa-caret-up"></i></span>
							<span class="btn-inner--text">
								<span class="comment-upvote-num"><?php echo Utils::format_number_in_kilos( self::get_comment_upvotes( get_comment_ID() ) ); ?></span>
							</span>
						</button>
					<?php } ?>
				</div>
				<div class="comment-item-inner" id="comment-inner-<?php comment_ID(); ?>">
					<div class="comment-item-title">
						<div class="comment-name">
							<div class="comment-author"><?php echo get_comment_author_link(); ?></div>
							<?php if ( user_can( $comment->user_id, "update_core" ) ) {
								echo '<span class="badge badge-primary badge-admin">' . __( '博主', 'argon' ) . '</span>';
							}
							?>
							<?php if ( function_exists( 'get_comment_parent_info' ) ) {
								echo get_comment_parent_info( $comment );
							} ?>
							<?php if ( $enable_pinning && $get_meta( "pinned" ) == "true" ) {
								echo '<span class="badge badge-danger badge-pinned"><i class="fa fa-thumb-tack" aria-hidden="true"></i> ' . _x( '置顶', 'pinned', 'argon' ) . '</span>';
							} ?>
							<?php if ( function_exists( 'is_comment_private_mode' ) && is_comment_private_mode( $comment_id ) && ( ! function_exists( 'user_can_view_comment' ) || user_can_view_comment( $comment_id ) ) ) {
								echo '<span class="badge badge-success badge-private-comment">' . __( '悄悄话', 'argon' ) . '</span>';
							}
							?>
							<?php if ( $comment->comment_approved == 0 ) {
								echo '<span class="badge badge-warning badge-unapproved">' . __( '待审核', 'argon' ) . '</span>';
							}
							?>
							<?php
							if ( function_exists( 'parse_ua_and_icon' ) ) {
								echo parse_ua_and_icon( $comment->comment_agent );
							}
							?>
						</div>
						<div class="comment-info">
							<?php if ( $get_meta( "edited" ) == "true" ) { ?>
								<div class="comment-edited<?php if ( function_exists( 'can_visit_comment_edit_history' ) && can_visit_comment_edit_history( $comment_id ) ) {
									echo ' comment-edithistory-accessible';
								} ?>">
									<i class="fa fa-pencil" aria-hidden="true"></i><?php _e( '已编辑', 'argon' ) ?>
								</div>
							<?php } ?>
							<div class="comment-time">
								<span class="human-time" data-time="<?php echo get_comment_time( 'U', true ); ?>"><?php echo human_time_diff( get_comment_time( 'U' ), current_time( 'timestamp' ) ) . __( "前", "argon" ); ?></span>
								<div class="comment-time-details"><?php echo get_comment_time( 'Y-n-d G:i:s' ); ?></div>
							</div>
						</div>
					</div>
					<div class="comment-item-text">
						<?php echo function_exists( 'argon_get_comment_text' ) ? argon_get_comment_text() : get_comment_text(); ?>
					</div>
					<div class="comment-item-source" style="display: none;" aria-hidden="true"><?php echo htmlspecialchars( $get_meta( "comment_content_source" ) ); ?></div>

					<div class="comment-operations">
						<?php if ( $enable_pinning && $can_moderate && function_exists( 'is_comment_pinable' ) && is_comment_pinable( $comment_id ) ) {
							if ( $get_meta( "pinned" ) == "true" ) { ?>
								<button class="comment-unpin btn btn-sm btn-outline-primary" data-id="<?php comment_ID(); ?>" type="button" style="margin-right: 2px;"><?php _e( '取消置顶', 'argon' ) ?></button>
							<?php } else { ?>
								<button class="comment-pin btn btn-sm btn-outline-primary" data-id="<?php comment_ID(); ?>" type="button" style="margin-right: 2px;"><?php _ex( '置顶', 'to pin', 'argon' ) ?></button>
							<?php }
						} ?>
						<?php if ( ( ( function_exists( 'check_comment_token' ) && check_comment_token( get_comment_ID() ) ) || ( function_exists( 'check_login_user_same' ) && check_login_user_same( $comment->user_id ) ) ) && ( get_option( "argon_comment_allow_editing" ) != "false" ) ) { ?>
							<button class="comment-edit btn btn-sm btn-outline-primary" data-id="<?php comment_ID(); ?>" type="button" style="margin-right: 2px;"><?php _e( '编辑', 'argon' ) ?></button>
						<?php } ?>
						<button class="comment-reply btn btn-sm btn-outline-primary" data-id="<?php comment_ID(); ?>" type="button"><?php _e( '回复', 'argon' ) ?></button>
					</div>
				</div>
			</li>
			<li class="comment-divider"></li>
			<li>
			<?php
		}
	}

	public static function comment_shuoshuo_preview_format( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment; ?>
		<li class="comment-item" id="comment-<?php comment_ID(); ?>">
			<div class="comment-item-inner " id="comment-inner-<?php comment_ID(); ?>">
				<span class="shuoshuo-comment-item-title">
					<?php echo get_comment_author_link(); ?>
					<?php if ( user_can( $comment->user_id, "update_core" ) ) {
						echo '<span class="badge badge-primary badge-admin">' . __( '博主', 'argon' ) . '</span>';
					}
					?>
					<?php if ( $comment->comment_approved == 0 ) {
						echo '<span class="badge badge-warning badge-unapproved">' . __( '待审核', 'argon' ) . '</span>';
					}
					?>
					:
				</span>
				<span class="shuoshuo-comment-item-text">
					<?php echo strip_tags( get_comment_text() ); ?>
				</span>
			</div>
		</li>
		<li>
		<?php
	}
}

/**
 * Internal Captcha Helper
 */
class CaptchaHelper {
	private $captchaSeed;

	public function __construct( $seed ) {
		$this->captchaSeed = $seed;
	}

	public function getChallenge() {
		if ( ! is_numeric( $this->captchaSeed ) ) {
			// If seed is a hex string, use it to seed mt_rand
			mt_srand( hexdec( substr( $this->captchaSeed, 0, 8 ) ) + 10007 );
		} else {
			mt_srand( $this->captchaSeed + 10007 );
		}
		$oper = mt_rand( 1, 4 );
		switch ( $oper ) {
			case 1:
				$num1 = mt_rand( 1, 20 );
				$num2 = mt_rand( 0, 20 - $num1 );
				return $num1 . " + " . $num2 . " = ";
			case 2:
				$num1 = mt_rand( 10, 20 );
				$num2 = mt_rand( 1, $num1 );
				return $num1 . " - " . $num2 . " = ";
			case 3:
				$num1 = mt_rand( 3, 9 );
				$num2 = mt_rand( 3, 9 );
				return $num1 . " * " . $num2 . " = ";
			case 4:
				$num2 = mt_rand( 2, 9 );
				$num1 = $num2 * mt_rand( 2, 9 );
				return $num1 . " / " . $num2 . " = ";
		}
		return "";
	}

	public function getAnswer() {
		if ( ! is_numeric( $this->captchaSeed ) ) {
			mt_srand( hexdec( substr( $this->captchaSeed, 0, 8 ) ) + 10007 );
		} else {
			mt_srand( $this->captchaSeed + 10007 );
		}
		$oper = mt_rand( 1, 4 );
		switch ( $oper ) {
			case 1:
				$num1 = mt_rand( 1, 20 );
				$num2 = mt_rand( 0, 20 - $num1 );
				return $num1 + $num2;
			case 2:
				$num1 = mt_rand( 10, 20 );
				$num2 = mt_rand( 1, $num1 );
				return $num1 - $num2;
			case 3:
				$num1 = mt_rand( 3, 9 );
				$num2 = mt_rand( 3, 9 );
				return $num1 * $num2;
			case 4:
				$num2 = mt_rand( 2, 9 );
				$num1 = $num2 * mt_rand( 2, 9 );
				return $num1 / $num2;
		}
		return "";
	}

	public function check( $answer ) {
		return (string) $answer === (string) $this->getAnswer();
	}
}
