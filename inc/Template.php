<?php

namespace ArgonModern;

class Template {
	use Singleton;

	protected function setup() {
		add_action( 'wp_head', [ $this, 'render_head_scripts' ] );
		add_action( 'wp_footer', [ $this, 'render_footer_scripts' ] );
		add_filter( 'the_content', [ $this, 'the_content_filter' ], 20 );
		add_filter( 'body_class', [ $this, 'body_class_filter' ] );
		add_shortcode( 'friendlinks', [ $this, 'shortcode_friend_link' ] );
		add_filter( 'pre_get_posts', [ $this, 'search_filter' ] );
		add_filter( 'show_admin_bar', function( $show ) {
			return Options::instance()->get( 'show_admin_bar' ) != 'false';
		} );
	}

	public function shortcode_friend_link( $attr, $content = "" ) {
		$sort        = isset( $attr['sort'] ) ? $attr['sort'] : 'name';
		$order       = isset( $attr['order'] ) ? $attr['order'] : 'ASC';
		$friendlinks = get_bookmarks( [
			'orderby' => $sort,
			'order'   => $order
		] );
		$style       = isset( $attr['style'] ) ? $attr['style'] : '1';
		switch ( $style ) {
			case '1':
				$class = "friend-links-style1";
				break;
			case '1-square':
				$class = "friend-links-style1 friend-links-style1-square";
				break;
			case '2':
				$class = "friend-links-style2";
				break;
			case '2-big':
				$class = "friend-links-style2 friend-links-style2-big";
				break;
			default:
				$class = "friend-links-style1";
				break;
		}
		$out = "<div class='friend-links " . $class . "'><div class='row'>";
		foreach ( $friendlinks as $friendlink ) {
			$out .= "
				<div class='link mb-2 col-lg-6 col-md-6'>
					<div class='card shadow-sm friend-link-container" . ( $friendlink->link_image == "" ? " no-avatar" : "" ) . "'>";
			if ( $friendlink->link_image != '' ) {
				$out .= "
						<img src='" . $friendlink->link_image . "' class='friend-link-avatar bg-gradient-secondary'>";
			}
			$out .= "	<div class='friend-link-content'>
							<div class='friend-link-title title text-primary'>
								<a target='_blank' href='" . esc_url( $friendlink->link_url ) . "'>" . esc_html( $friendlink->link_name ) . "</a>
							</div>
							<div class='friend-link-description'>" . esc_html( $friendlink->link_description ) . "</div>";
			$out .= "		<div class='friend-link-links'>";
			foreach ( explode( "\n", $friendlink->link_notes ) as $line ) {
				$item = explode( "|", trim( $line ) );
				if ( stripos( $item[0], "fa-" ) !== 0 ) {
					continue;
				}
				$out .= "<a href='" . esc_url( $item[1] ) . "' target='_blank'><i class='fa " . sanitize_html_class( $item[0] ) . "'></i></a>";
			}
			$out .= "<a href='" . esc_url( $friendlink->link_url ) . "' target='_blank' style='float:right; margin-right: 10px;'><i class='fa fa-angle-right' style='font-weight: bold;'></i></a>";
			$out .= "
							</div>
						</div>
					</div>
				</div>";
		}
		$out .= "</div></div>";

		return $out;
	}

	public function body_class_filter( $classes ) {
		$options = Options::instance();
		$page_layout = $options->get( 'page_layout', 'double' );

		if ( $page_layout == 'single' ) {
			$classes[] = 'single-column';
		} elseif ( $page_layout == 'triple' ) {
			$classes[] = 'triple-column';
		} elseif ( $page_layout == 'double-reverse' ) {
			$classes[] = 'double-column-reverse';
		}

		return $classes;
	}

	public static function get_reference_list() {
		global $post_references, $post_reference_keys_first_index, $post_reference_contents_first_index;
		$options = Options::instance();
		if ( empty( $post_references ) ) {
			return "";
		}
		$title = $options->get( 'reference_list_title' );
		if ( $title == "" ) {
			$title = __( '参考资料', 'argon' );
		}
		$res = "<div class='post-references card shadow-sm'><div class='card-body'><div class='post-references-title h5'><i class='fa fa-bookmark' aria-hidden='true'></i> " . $title . "</div><ul class='post-references-list'>";
		foreach ( $post_references as $index => $reference ) {
			$res .= "<li id='ref-" . ( $index + 1 ) . "'><a href='" . $reference['url'] . "' target='_blank' rel='nofollow'>" . $reference['title'] . "</a></li>";
		}
		$res .= "</ul></div></div>";
		return $res;
	}

	public static function get_related_posts() {
		$options = Options::instance();
		$relatedPosts = $options->get( 'related_post', 'disabled' );
		if ( $relatedPosts == 'disabled' || $relatedPosts == 'false' ) {
			return "";
		}
		global $post;
		$post_id = $post->ID;

		$cat_array = [];
		if ( strpos( $relatedPosts, 'category' ) !== false ) {
			$cats = get_the_category( $post_id );
			if ( $cats ) {
				foreach ( $cats as $cat ) {
					$cat_array[] = $cat->slug;
				}
			}
		}
		$tag_array = [];
		if ( strpos( $relatedPosts, 'tag' ) !== false ) {
			$tags = get_the_tags( $post_id );
			if ( $tags ) {
				foreach ( $tags as $tag ) {
					$tag_array[] = $tag->slug;
				}
			}
		}

		$cache_key = 'argon_related_posts_' . $post_id;
		$cached_res = get_transient( $cache_key );
		if ( $cached_res !== false ) {
			return $cached_res;
		}

		$args = [
			'posts_per_page'      => (int) $options->get( 'related_post_limit', 10 ),
			'order'               => $options->get( 'related_post_sort_order', 'DESC' ),
			'orderby'             => $options->get( 'related_post_sort_orderby', 'date' ),
			'post__not_in'        => [ $post_id ],
			'ignore_sticky_posts' => 1,
			'tax_query'           => [
				'relation' => 'OR',
			]
		];

		if ( ! empty( $cat_array ) ) {
			$args['tax_query'][] = [
				'taxonomy'         => 'category',
				'field'            => 'slug',
				'terms'            => $cat_array,
				'include_children' => false
			];
		}
		if ( ! empty( $tag_array ) ) {
			$args['tax_query'][] = [
				'taxonomy' => 'post_tag',
				'field'    => 'slug',
				'terms'    => $tag_array,
			];
		}

		$my_query = new \WP_Query( $args );
		if ( $my_query->have_posts() ) {
			$res = '<div class="related-posts card shadow-sm">
						<h2 class="post-comment-title" style="margin-top: 1.2rem;margin-left: 1.5rem;margin-right: 1.5rem;">
							<i class="fa fa-book"></i>
							<span>' . __( "推荐文章", 'argon' ) . '</span>
						</h2>
						<div style="overflow-x: auto;padding: 1.5rem;padding-top: 0.8rem;padding-bottom: 0.8rem;">
							<div style="display: flex; flex-wrap: nowrap;">';
			while ( $my_query->have_posts() ) {
				$my_query->the_post();
				$hasThumbnail = self::has_post_thumbnail( get_the_ID() );
				$res .= '<a class="related-post-card" href="' . get_the_permalink() . '">';
				$res .= '<div class="related-post-card-container' . ( $hasThumbnail ? ' has-thumbnail' : '' ) . '">
							<div class="related-post-title clamp" clamp-line="3">' . get_the_title() . '</div>
							<i class="related-post-arrow fa fa-chevron-right" aria-hidden="true"></i>
						</div>';
				if ( $hasThumbnail ) {
					$thumbnail_url = self::get_post_thumbnail( get_the_ID() );
					$placeholder = self::get_thumbnail_placeholder( get_the_ID() );
					$res .= '<div class="related-post-thumbnail-wrapper" style="aspect-ratio: 16/9; overflow: hidden; border-radius: 0.25rem;">';
					$res .= '<img class="related-post-thumbnail" src="' . $thumbnail_url . '" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;"/>';
					$res .= '</div>';
				}
				$res .= '</a>';
			}
			$res .= '</div></div></div>';
			wp_reset_postdata();
			set_transient( $cache_key, $res, HOUR_IN_SECONDS );
			return $res;
		}
		set_transient( $cache_key, "", HOUR_IN_SECONDS );
		return "";
	}

	public static function render_post_navigation() {
		if ( ! is_singular( 'post' ) ) {
			return;
		}
		$prev_post = get_previous_post();
		$next_post = get_next_post();
		if ( $prev_post || $next_post ) {
			echo '<div class="post-navigation card shadow-sm">';
			if ( $prev_post ) {
				previous_post_link( '<div class="post-navigation-item post-navigation-pre"><span class="page-navigation-extra-text"><i class="fa fa-arrow-circle-o-left" aria-hidden="true"></i>' . __( "上一篇", 'argon' ) . '</span>%link</div>', '%title' );
			} else {
				echo '<div class="post-navigation-item post-navigation-pre"></div>';
			}
			if ( $next_post ) {
				next_post_link( '<div class="post-navigation-item post-navigation-next"><span class="page-navigation-extra-text">' . __( "下一篇", 'argon' ) . ' <i class="fa fa-arrow-circle-o-right" aria-hidden="true"></i></span>%link</div>', '%title' );
			} else {
				echo '<div class="post-navigation-item post-navigation-next"></div>';
			}
			echo '</div>';
		}
	}

	public function the_content_filter( $content ) {
		$options = Options::instance();
		if ( $options->get( 'enable_lazyload' ) != 'false' ) {
			$content = self::argon_lazyload( $content );
		}
		if ( $options->get( 'enable_fancybox' ) != 'false' && $options->get( 'enable_zoomify' ) == 'false' ) {
			$content = self::argon_fancybox( $content );
		}
		global $post;
		if ( isset( $post->ID ) ) {
			$custom_css = get_post_meta( $post->ID, 'argon_custom_css', true );
			if ( ! empty( $custom_css ) ) {
				$content .= "<style>" . wp_strip_all_tags( $custom_css ) . "</style>";
			}
		}

		if ( is_singular( 'post' ) && ! is_front_page() && ! is_feed() ) {
			$additional_content = $options->get( 'additional_content_after_post' );
			if ( ! empty( $additional_content ) ) {
				global $post;
				$permalink = get_permalink( $post->ID );
				$additional_content = str_replace( "%url%", $permalink, $additional_content );
				$additional_content = str_replace( "%link%", "<a href='" . $permalink . "'>" . $permalink . "</a>", $additional_content );
				$additional_content = str_replace( "%title%", get_the_title( $post->ID ), $additional_content );
				$additional_content = str_replace( "%author%", get_the_author_meta( 'display_name', $post->post_author ), $additional_content );
				$additional_content = str_replace( "%date%", get_the_time( get_option( 'date_format' ), $post->ID ), $additional_content );
				$additional_content = str_replace( "%time%", get_the_time( get_option( 'time_format' ), $post->ID ), $additional_content );
				$additional_content = str_replace( "%modify_date%", get_the_modified_time( get_option( 'date_format' ), $post->ID ), $additional_content );
				$additional_content = str_replace( "%modify_time%", get_the_modified_time( get_option( 'time_format' ), $post->ID ), $additional_content );
				$content .= "<div class='additional-content-after-post mt-4'>" . wpautop( do_shortcode( $additional_content ) ) . "</div>";
			}
		}

		return $content;
	}

	public static function get_lazyload_placeholder( $width = 1, $height = 1 ) {
		return "data:image/svg+xml;base64," . base64_encode( "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 $width $height'></svg>" );
	}

	public static function get_thumbnail_placeholder( $post_id ) {
		$thumbnail_id = get_post_thumbnail_id( $post_id );
		if ( $thumbnail_id ) {
			$meta = wp_get_attachment_metadata( $thumbnail_id );
			if ( isset( $meta['width'], $meta['height'] ) ) {
				return self::get_lazyload_placeholder( $meta['width'], $meta['height'] );
			}
		}
		return self::get_lazyload_placeholder( 16, 9 ); // Fallback to 16:9
	}

	public static function argon_lazyload( $content ) {
		$options = Options::instance();
		$lazyload_loading_style = $options->get( 'lazyload_loading_style' );
		if ( $lazyload_loading_style == '' ) {
			$lazyload_loading_style = 'none';
		}
		$lazyload_loading_style = "lazyload-style-" . $lazyload_loading_style;

		if ( ! is_feed() && ! is_robots() && ! is_home() ) {
			$content = preg_replace_callback( '/<img(.*?)src=[\'"](.*?)[\'"](.*?)((\/>)|(<\/img>))/i', function($matches) use ($lazyload_loading_style) {
				$before = $matches[1];
				$src = $matches[2];
				$after = $matches[3];
				$closing = $matches[4];
				
				$width = '';
				$height = '';
				if (preg_match('/width=[\'"](\d+)[\'"]/i', $before . $after, $w_matches)) {
					$width = $w_matches[1];
				}
				if (preg_match('/height=[\'"](\d+)[\'"]/i', $before . $after, $h_matches)) {
					$height = $h_matches[1];
				}
				
				$placeholder = "data:image/svg+xml;base64,PCEtLUFyZ29uTG9hZGluZy0tPgo8c3ZnIHdpZHRoPSIxIiBoZWlnaHQ9IjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgc3Ryb2tlPSIjZmZmZmZmMDAiPjxnPjwvZz4KPC9zdmc+";
				$style_attr = '';
				if ($width && $height) {
					$style_attr = " style=\"aspect-ratio: $width / $height; width: 100%; height: auto;\"";
				}
				
				return "<img class=\"lazyload-native " . $lazyload_loading_style . "\" src=\"$src\" $before loading=\"lazy\" $after$style_attr$closing";
			}, $content );
			$content = preg_replace( '/<img(.*?)data-full-url=[\'"]([^\'"]+)[\'"](.*)>/i', "<img$1data-full-url=\"$2\"$3>", $content );
		}
		return $content;
	}

	public static function argon_fancybox( $content ) {
		$options = Options::instance();
		if ( ! is_feed() && ! is_robots() && ! is_home() ) {
			$content = preg_replace( '/<img(.*?)src=[\'"](.*?)[\'"](.*?)((\/>)|>|(<\/img>))/i', "<div class='fancybox-wrapper' data-fancybox='post-images' href='$2'>$0</div>", $content );
		}
		return $content;
	}

	public static function get_banner_background_url() {
		$options = Options::instance();
		$url = $options->get( "banner_background_url" );
		if ( $url == "--bing--" ) {
			$lastUpdated = $options->get( "bing_banner_background_last_updated_time" );
			if ( $lastUpdated == "" ) {
				$lastUpdated = 0;
			}
			$now = time();
			if ( $now - $lastUpdated < 3600 ) {
				return $options->get( "bing_banner_background_last_updated_url" );
			} else {
				$response = wp_remote_get( 'https://www.bing.com/HPImageArchive.aspx?format=js&idx=0&n=1', [
					'timeout' => 10,
					'headers' => [ 'User-Agent' => 'ArgonThemeModern' ],
				] );

				if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
					return $options->get( "bing_banner_background_last_updated_url" );
				}

				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );

				if ( isset( $data['images'][0]['url'] ) ) {
					$url = "//bing.com" . $data['images'][0]['url'];
					update_option( "argon_bing_banner_background_last_updated_time", $now );
					update_option( "argon_bing_banner_background_last_updated_url", $url );
					return $url;
				}
				return $options->get( "bing_banner_background_last_updated_url" );
			}
		} else {
			return $url;
		}
	}

	public static function get_post_outdated_info() {
		$options = Options::instance();
		global $post;
		$post_show_outdated_info_status = strval( get_post_meta( $post->ID, 'argon_show_post_outdated_info', true ) );
		if ( $options->get( "outdated_info_tip_type" ) == "toast" ) {
			$before = "<div id='post_outdate_toast' style='display:none;' data-text='";
			$after  = "'></div>";
		} else {
			$before = "<div class='post-outdated-info'><i class='fa fa-info-circle' aria-hidden='true'></i>";
			$after  = "</div>";
		}
		$content = $options->get( 'outdated_info_tip_content' ) == '' ? '本文最后更新于 %date_delta% 天前，其中的信息可能已经有所发展或是发生改变。' : $options->get( 'outdated_info_tip_content' );
		$delta   = $options->get( 'outdated_info_days' ) == '' ? ( - 1 ) : $options->get( 'outdated_info_days' );
		if ( $delta == - 1 ) {
			$delta = 2147483647;
		}
		$post_date_delta   = floor( ( current_time( 'timestamp' ) - get_the_time( "U" ) ) / ( 60 * 60 * 24 ) );
		$modify_date_delta = floor( ( current_time( 'timestamp' ) - get_the_modified_time( "U" ) ) / ( 60 * 60 * 24 ) );
		if ( $options->get( "outdated_info_time_type" ) == "createdtime" ) {
			$date_delta = $post_date_delta;
		} else {
			$date_delta = $modify_date_delta;
		}
		if ( ( $date_delta <= $delta && $post_show_outdated_info_status != 'always' ) || $post_show_outdated_info_status == 'never' ) {
			return "";
		}
		$content = str_replace( "%date_delta%", $date_delta, $content );
		$content = str_replace( "%modify_date_delta%", $modify_date_delta, $content );
		$content = str_replace( "%post_date_delta%", $post_date_delta, $content );
		return $before . $content . $after;
	}

    public function render_head_scripts() {

    }

	public function render_footer_scripts() {
		// Port footer scripts here
	}

	public static function get_post_views( $post_id ) {
		$count_key = 'views';
		$count = get_post_meta( $post_id, $count_key, true );
		if ( $count == '' ) {
			return '0';
		}
		return number_format_i18n( (int) $count );
	}

	public static function has_post_thumbnail( $post_id = 0 ) {
		$options = Options::instance();
		if ( $post_id == 0 ) {
			global $post;
			$post_id = isset( $post->ID ) ? $post->ID : 0;
		}
		if ( ! $post_id ) return false;

		if ( has_post_thumbnail( $post_id ) ) {
			return true;
		}

		$first_image_as_thumbnail = get_post_meta( $post_id, 'argon_first_image_as_thumbnail', true );
		if ( $first_image_as_thumbnail == "" ) {
			$first_image_as_thumbnail = "default";
		}

		if ( $first_image_as_thumbnail == "true" || ( $first_image_as_thumbnail == "default" && $options->get( "first_image_as_thumbnail_by_default", "false" ) == "true" ) ) {
			if ( self::get_first_image_of_article( $post_id ) != false ) {
				return true;
			}
		}
		return false;
	}

	public static function get_post_thumbnail( $post_id = 0 ) {
		if ( $post_id == 0 ) {
			global $post;
			$post_id = isset( $post->ID ) ? $post->ID : 0;
		}
		if ( ! $post_id ) return '';

		if ( has_post_thumbnail( $post_id ) ) {
			return apply_filters( "argon_post_thumbnail", wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), "full" )[0] );
		}
		return apply_filters( "argon_post_thumbnail", self::get_first_image_of_article( $post_id ) );
	}

	public static function get_first_image_of_article( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || post_password_required( $post ) ) {
			return false;
		}
		$post_content_full = apply_filters( 'the_content', preg_replace( '<!--more(.*?)-->', '', $post->post_content ) );
		preg_match( '/<img(.*?)(src|data-original)=[\"\']((http:|https:)?\/\/(.*?))[\"\'](.*?)\/?>/', $post_content_full, $match );
		if ( isset( $match[3] ) ) {
			return $match[3];
		}
		return false;
	}

	public static function get_article_meta( $type ) {
		$options = Options::instance();
		switch ( $type ) {
			case 'time':
				return '<span class="post-meta-detail post-meta-detail-time">
							<i class="fa fa-clock-o" aria-hidden="true"></i>
							<time title="' . __( '发布于', 'argon' ) . ' ' . get_the_time( 'Y-n-d G:i:s' ) . ' | ' . __( '编辑于', 'argon' ) . ' ' . get_the_modified_time( 'Y-n-d G:i:s' ) . '">' .
								get_the_time( 'Y-n-d G:i' ) .
							'</time>
						</span>';
			case 'edittime':
				return '<span class="post-meta-detail post-meta-detail-edittime">
							<i class="fa fa-clock-o" aria-hidden="true"></i>
							<time title="' . __( '发布于', 'argon' ) . ' ' . get_the_time( 'Y-n-d G:i:s' ) . ' | ' . __( '编辑于', 'argon' ) . ' ' . get_the_modified_time( 'Y-n-d G:i:s' ) . '">' .
								get_the_modified_time( 'Y-n-d G:i' ) .
							'</time>
						</span>';
			case 'views':
				return '<span class="post-meta-detail post-meta-detail-views">
							<i class="fa fa-eye" aria-hidden="true"></i> ' .
							self::get_post_views( get_the_ID() ) .
						'</span>';
			case 'comments':
				return '<span class="post-meta-detail post-meta-detail-comments">
							<i class="fa fa-comments-o" aria-hidden="true"></i> ' .
							get_comments_number() .
						'</span>';
			case 'author':
				$author_id = get_the_author_meta( 'ID' );
				$author_nicename = get_the_author_meta( 'user_nicename' );
				return '<span class="post-meta-detail post-meta-detail-author">
							<i class="fa fa-user-circle-o" aria-hidden="true"></i>
							<a href="' . get_author_posts_url( $author_id, $author_nicename ) . '" target="_blank">' . get_the_author() . '</a>
						</span>';
			case 'sticky':
				return '<span class="post-meta-detail post-meta-detail-words">
							<i class="fa fa-thumb-tack" aria-hidden="true"></i> ' .
							_x( '置顶', 'pinned', 'argon' ) .
						'</span>';
			case 'needpassword':
				return '<span class="post-meta-detail post-meta-detail-words">
							<i class="fa fa-lock" aria-hidden="true"></i> ' .
							__( '受保护', 'argon' ) .
						'</span>';
			case 'categories':
				$categories = get_the_category();
				if ( ! $categories ) return '';
				$res = '<span class="post-meta-detail post-meta-detail-categories">
							<i class="fa fa-bookmark-o" aria-hidden="true"></i> ';
				foreach ( $categories as $index => $category ) {
					$res .= '<a href="' . get_category_link( $category->term_id ) . '" target="_blank" class="post-meta-detail-catagory-link">' . $category->cat_name . '</a>';
					if ( $index != count( $categories ) - 1 ) {
						$res .= '<span class="post-meta-detail-catagory-space">,</span>';
					}
				}
				$res .= '</span>';
				return $res;
			case 'readingtime':
				if ( $options->get( 'show_readingtime' ) == 'false' ) {
					return '';
				}
				if ( self::is_readingtime_meta_hidden() ) {
					return '';
				}
				$post = get_post();
				$words = self::get_article_words( $post->post_content );
				$res = '<span class="post-meta-detail post-meta-detail-words">
							<i class="fa fa-file-word-o" aria-hidden="true"></i>';
				if ( $words['code'] > 0 ) {
					$res .= '<span title="' . sprintf( __( '包含 %d 行代码', 'argon' ), $words['code'] ) . '">';
				} else {
					$res .= '<span>';
				}
				$res .= ' ' . ( $words['cn'] + $words['en'] + $words['code'] ) . " " . __( '字', 'argon' );
				$res .= '</span></span>
						<span class="post-meta-detail post-meta-detail-readingtime">
							<i class="fa fa-hourglass-end" aria-hidden="true"></i>
							' . self::get_reading_time( $words ) . '
						</span>';
				return $res;
			default:
				return '';
		}
	}

	public static function render_article_meta() {
		$options = Options::instance();
		$template = self::instance();

		$metaList = explode( '|', $options->get( 'article_meta', 'time|views|comments|categories' ) );

		if ( is_sticky() && is_home() && ! is_paged() ) {
			array_unshift( $metaList, "sticky" );
		}

		if ( post_password_required() ) {
			array_unshift( $metaList, "needpassword" );
		}

		if ( $template::is_meta_simple() ) {
			$template::array_remove( $metaList, "time" );
			$template::array_remove( $metaList, "edittime" );
			$template::array_remove( $metaList, "categories" );
			$template::array_remove( $metaList, "author" );
		}

		if ( count( get_the_category() ) == 0 ) {
			$template::array_remove( $metaList, "categories" );
		}

		// 文章内始终显示阅读时间（如果开启了），文章预览则根据设置
		if ( ! in_array( 'readingtime', $metaList ) ) {
			if ( is_singular() ) {
				$metaList[] = 'readingtime';
			}
		}

		$meta_items = [];
		foreach ( $metaList as $type ) {
			$meta_html = $template::get_article_meta( $type );
			if ( ! empty( $meta_html ) ) {
				$meta_items[] = $meta_html;
			}
		}

		echo implode( ' <div class="post-meta-devide">|</div> ', $meta_items );
	}

	public static function is_readingtime_meta_hidden() {
		if ( strpos( get_the_content(), "[hide_reading_time][/hide_reading_time]" ) !== false ) {
			return true;
		}
		global $post;
		if ( get_post_meta( $post->ID, 'argon_hide_readingtime', true ) == 'true' ) {
			return true;
		}
		return false;
	}

	public static function get_article_words( $str ) {
		preg_match_all( '/<pre(.*?)>[\S\s]*?<code(.*?)>([\S\s]*?)<\/code>[\S\s]*?<\/pre>/im', $str, $codeSegments, PREG_PATTERN_ORDER );
		$codeSegments = $codeSegments[3];
		$codeTotal = 0;
		foreach ( $codeSegments as $codeSegment ) {
			$codeLines = preg_split( '/\r\n|\n|\r/', $codeSegment );
			foreach ( $codeLines as $line ) {
				if ( strlen( trim( $line ) ) > 0 ) {
					$codeTotal++;
				}
			}
		}

		$str = preg_replace( '/<code(.*?)>[\S\s]*?<\/code>/im', '', $str );
		$str = preg_replace( '/<pre(.*?)>[\S\s]*?<\/pre>/im', '', $str );
		$str = preg_replace( '/<style(.*?)>[\S\s]*?<\/style>/im', '', $str );
		$str = preg_replace( '/<script(.*?)>[\S\s]*?<\/script>/im', '', $str );
		$str = preg_replace( '/<[^>]+?>/', ' ', $str );
		$str = html_entity_decode( strip_tags( $str ) );
		preg_match_all( '/[\x{4e00}-\x{9fa5}]/u', $str, $cnRes );
		$cnTotal = count( $cnRes[0] );
		preg_match_all( '/[a-zA-Z0-9_\x{0392}-\x{03c9}\x{0400}-\x{04FF}]+|[\x{4E00}-\x{9FFF}\x{3400}-\x{4dbf}\x{f900}-\x{faff}\x{3040}-\x{309f}\x{ac00}-\x{d7af}\x{0400}-\x{04FF}]+|[\x{00E4}\x{00C4}\x{00E5}\x{00C5}\x{00F6}\x{00D6}]+|\w+/u', $str, $enRes );
		$enTotal = count( $enRes[0] );
		return [
			'cn'   => $cnTotal,
			'en'   => $enTotal,
			'code' => $codeTotal,
		];
	}

	public static function get_reading_time( $len ) {
		$options = Options::instance();
		$speedcn = (int) $options->get( 'reading_speed', 300 );
		$speeden = (int) $options->get( 'reading_speed_en', 160 );
		$speedcode = (int) $options->get( 'reading_speed_code', 20 );
		$reading_time = $len['cn'] / $speedcn + $len['en'] / $speeden + $len['code'] / $speedcode;
		if ( $reading_time < 0.3 ) {
			return __( "几秒读完", 'argon' );
		}
		if ( $reading_time < 1 ) {
			return __( "1 分钟内", 'argon' );
		}
		if ( $reading_time < 60 ) {
			return ceil( $reading_time ) . " " . __( "分钟", 'argon' );
		}
		return round( $reading_time / 60, 1 ) . " " . __( "小时", 'argon' );
	}

	public static function get_formatted_paginate_links( $maxPageNumbers, $extraClasses = '' ) {
		$args = [
			'prev_text'          => '',
			'next_text'          => '',
			'before_page_number' => '',
			'after_page_number'  => '',
			'show_all'           => true
		];
		$res  = paginate_links( $args );
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
			$html .= '<li class="page-item"><a aria-label="First Page" class="page-link" href="' . $urls[1] . '"><i class="fa fa-angle-double-left" aria-hidden="true"></i></a></li>';
		}
		if ( $current > 1 ) {
			$html .= '<li class="page-item"><a aria-label="Previous Page" class="page-link" href="' . $urls[ $current - 1 ] . '"><i class="fa fa-angle-left" aria-hidden="true"></i></a></li>';
		}
		for ( $i = $from; $i <= $to; $i++ ) {
			if ( $current == $i ) {
				$html .= '<li class="page-item active"><span class="page-link" style="cursor: default;">' . $i . '</span></li>';
			} else {
				$html .= '<li class="page-item"><a class="page-link" href="' . $urls[ $i ] . '">' . $i . '</a></li>';
			}
		}
		if ( $current < $total ) {
			$html .= '<li class="page-item"><a aria-label="Next Page" class="page-link" href="' . $urls[ $current + 1 ] . '"><i class="fa fa-angle-right" aria-hidden="true"></i></a></li>';
		}
		if ( $to < $total ) {
			$html .= '<li class="page-item"><a aria-label="Last Page" class="page-link" href="' . $urls[ $total ] . '"><i class="fa fa-angle-double-right" aria-hidden="true"></i></a></li>';
		}
		return '<nav><ul class="pagination' . $extraClasses . '">' . $html . '</ul></nav>';
	}

	public static function get_formatted_paginate_links_for_all_platforms() {
		return self::get_formatted_paginate_links( 7 ) . self::get_formatted_paginate_links( 5, " pagination-mobile" );
	}

	public static function get_seo_description() {
		$options = Options::instance();
		global $post;
		if ( is_single() || is_page() ) {
			if ( get_the_excerpt() != "" ) {
				return preg_replace( '/ \[&hellip;]$/', '&hellip;', get_the_excerpt() );
			}
			if ( ! post_password_required() ) {
				$content = $post->post_content;
				// Performance: Pre-truncate if content is very long before stripping tags
				if ( mb_strlen( $content ) > 500 ) {
					$content = mb_substr( $content, 0, 500 );
				}
				return htmlspecialchars( mb_substr( str_replace( [ "\n", "\r" ], '', strip_tags( $content ) ), 0, 50 ) ) . "...";
			} else {
				return __( "这是一个加密页面，需要密码来查看", 'argon' );
			}
		} else {
			return $options->get( 'seo_description' );
		}
	}

	public static function get_seo_keywords() {
		$options = Options::instance();
		if ( is_single() ) {
			global $post;
			$tags = get_the_tags( $post->ID );
			if ( $tags != null ) {
				$res = "";
				foreach ( $tags as $tag ) {
					if ( $res != "" ) {
						$res .= ",";
					}
					$res .= $tag->name;
				}
				return $res;
			}
		}
		if ( is_category() ) {
			return single_cat_title( '', false );
		}
		if ( is_tag() ) {
			return single_tag_title( '', false );
		}
		if ( is_author() ) {
			return get_the_author();
		}
		if ( is_post_type_archive() ) {
			return post_type_archive_title( '', false );
		}
		if ( is_tax() ) {
			return single_term_title( '', false );
		}
		return $options->get( 'seo_keywords' );
	}

	public static function get_og_image() {
		global $post;
		if ( ! isset( $post->ID ) ) return '';
		$post_id = $post->ID;
		if ( self::has_post_thumbnail( $post_id ) ) {
			return self::get_post_thumbnail( $post_id );
		}
		return '';
	}

	public static function have_catalog() {
		if ( post_password_required() ) {
			return false;
		}
		if ( is_page() && is_page_template( 'templates/archives.php' ) ) {
			return true;
		}
		global $post;
		if ( is_singular() ) {
			if ( ! isset( $post->post_content ) ) return false;
			if ( preg_match( '/<h[1-6](.*?)>/', $post->post_content ) ) {
				return true;
			}
		}
		if ( is_archive() || is_search() ) {
			if ( get_the_archive_description() != '' && preg_match( '/<h[1-6](.*?)>/', get_the_archive_description() ) ) {
				return true;
			}
		}
		return false;
	}

	public static function get_article_reading_time_meta( $content ) {
		$words = self::get_article_words( $content );
		$minutes = self::get_reading_time( $words );
		return '<span class="post-meta-detail post-meta-detail-readingtime">
					<i class="fa fa-hourglass-half" aria-hidden="true"></i> ' .
					sprintf( __( '阅读时间约 %s', 'argon' ), $minutes ) .
				'</span>';
	}

	public static function is_meta_simple() {
		return get_post_meta( get_the_ID(), 'argon_meta_simple', true ) == 'true';
	}

	public static function array_remove( &$arr, $item ) {
		$pos = array_search( $item, $arr );
		if ( $pos !== false ) {
			array_splice( $arr, $pos, 1 );
		}
	}

	public static function locate_filter( $locate ) {
		if ( substr( $locate, 0, 2 ) == 'zh' ) {
			if ( $locate == 'zh_TW' ) {
				return $locate;
			}
			return 'zh_CN';
		}
		if ( substr( $locate, 0, 2 ) == 'en' ) {
			return 'en_US';
		}
		if ( substr( $locate, 0, 2 ) == 'ru' ) {
			return 'ru_RU';
		}
		return 'en_US';
	}

	public static function get_locate() {
		if ( function_exists( "determine_locale" ) ) {
			return self::locate_filter( determine_locale() );
		}
		$determined_locale = get_locale();
		if ( is_admin() ) {
			$determined_locale = get_user_locale();
		}
		return self::locate_filter( $determined_locale );
	}

	public static function get_search_post_type_array() {
		$options = Options::instance();
		$search_filters_type = $options->get( "argon_search_filters_type", "*post,*page,shuoshuo" );
		$search_filters_type = explode( ',', $search_filters_type );
		if ( ! isset( $_GET['post_type'] ) ) {
			$default = array_filter( $search_filters_type, function ( $str ) {
				return $str[0] == '*';
			} );
			$default = array_map( function ( $str ) {
				return substr( $str, 1 );
			}, $default );
			return array_values( $default );
		}
		$search_filters_type = array_map( function ( $str ) {
			return $str[0] == '*' ? substr( $str, 1 ) : $str;
		}, $search_filters_type );
		$post_type           = explode( ',', $_GET['post_type'] );
		$arr                 = [];
		foreach ( $search_filters_type as $type ) {
			if ( in_array( $type, $post_type ) ) {
				array_push( $arr, $type );
			}
		}
		if ( count( $arr ) == 0 ) {
			array_push( $arr, 'none' );
		}
		return $arr;
	}

	public function search_filter( $query ) {
		if ( ! $query->is_search || is_admin() ) {
			return $query;
		}
		$options = Options::instance();
		if ( $options->get( 'argon_enable_search_filters', 'true' ) == 'false' ) {
			return $query;
		}
		$query->set( 'post_type', self::get_search_post_type_array() );
		return $query;
	}
}
