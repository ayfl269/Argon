<?php 
$options = \ArgonModern\Options::instance();
if ($options->get('page_layout', 'double') == 'single') {
	return;
} ?>
<div id="sidebar_mask"></div>
<aside id="leftbar" class="leftbar widget-area" role="complementary">
		<?php if ($options->get('sidebar_announcement') != '') { ?>
			<div id="leftbar_announcement" class="card bg-white shadow-sm border-0">
				<div class="leftbar-announcement-body">
					<div class="leftbar-announcement-title text-white"><?php _e('公告', 'argon');?></div>
					<div class="leftbar-announcement-content text-white"><?php echo $options->get('sidebar_announcement'); ?></div>
				</div>
			</div>
		<?php } ?>
		<div id="leftbar_part1" class="widget widget_search card bg-white shadow-sm border-0">
			<div class="leftbar-banner card-body">
				<span class="leftbar-banner-title text-white"><?php echo $options->get('sidebar_banner_title') == '' ? get_bloginfo('name') : $options->get('sidebar_banner_title'); ?></span>

				<?php 
					$sidebar_subtitle = $options->get('sidebar_banner_subtitle'); 
					if ($sidebar_subtitle == "--hitokoto--"){
						$sidebar_subtitle = "<span class='hitokoto'></span>";
					}
				?>
				<?php if ($sidebar_subtitle != '') { /*左侧栏子标题/格言(如果选项中开启)*/?>
					<span class="leftbar-banner-subtitle text-white"><?php echo $sidebar_subtitle; ?></span>
				<?php } /*顶栏标题*/?>

			</div>

			<?php
				/*侧栏上部菜单*/
				echo "<ul id='leftbar_part1_menu' class='leftbar-menu'>";
				if ( has_nav_menu('leftbar_menu') ){
					wp_nav_menu( array(
						'container'  => '',
						'theme_location'  => 'leftbar_menu',
						'items_wrap'  => '%3$s',
						'depth' => 0,
						'walker' => new leftbarMenuWalker()
					) );
				}
				echo "</ul>";
			?>
			<div class="card-body text-center leftbar-search-button">
				<button id="leftbar_search_container" class="btn btn-secondary btn-lg active btn-sm btn-block border-0" role="button">
					<i class="menu-item-icon fa fa-search mr-0"></i> <?php _e('搜索', 'argon');?>
					<input id="leftbar_search_input" type="text" placeholder="<?php _e('搜索什么...', 'argon');?>" class="form-control form-control-alternative" autocomplete="off">
				</button>
			</div>
		</div>
		<div id="leftbar_part2" class="widget widget_search card bg-white shadow-sm border-0">
			<div id="leftbar_part2_inner" class="card-body">
				<?php
					$nowActiveTab = 1;/*默认激活的标签*/
					if (have_catalog()){
						$nowActiveTab = 0;
					}
				?>
				<div class="nav-wrapper" style="padding-top: 5px;<?php if (!have_catalog() && !is_active_sidebar('leftbar-tools')) { echo ' display:none;'; }?>">
	                <ul class="nav nav-pills nav-fill" role="tablist">
						<?php if (have_catalog()) { ?>
							<li class="nav-item sidebar-tab-switcher" role="presentation">
								<a class="<?php if ($nowActiveTab == 0) { echo 'active show'; }?>" id="leftbar_tab_catalog_btn" data-toggle="tab" href="#leftbar_tab_catalog" role="tab" aria-controls="leftbar_tab_catalog" aria-selected="<?php echo $nowActiveTab == 0 ? 'true' : 'false'; ?>" no-pjax>
									<?php _e('文章目录', 'argon');?>
								</a>
							</li>
						<?php } ?>
						<li class="nav-item sidebar-tab-switcher" role="presentation">
							<a class="<?php if ($nowActiveTab == 1) { echo 'active show'; }?>" id="leftbar_tab_overview_btn" data-toggle="tab" href="#leftbar_tab_overview" role="tab" aria-controls="leftbar_tab_overview" aria-selected="<?php echo $nowActiveTab == 1 ? 'true' : 'false'; ?>" no-pjax>
								<?php _e('站点概览', 'argon');?>
							</a>
						</li>
						<?php if (is_active_sidebar('leftbar-tools')) { ?>
							<li class="nav-item sidebar-tab-switcher" role="presentation">
								<a class="<?php if ($nowActiveTab == 2) { echo 'active show'; }?>" id="leftbar_tab_tools_btn" data-toggle="tab" href="#leftbar_tab_tools" role="tab" aria-controls="leftbar_tab_tools" aria-selected="<?php echo $nowActiveTab == 2 ? 'true' : 'false'; ?>" no-pjax>
									<?php _e('功能', 'argon');?>
								</a>
							</li>
						<?php } ?>
	                </ul>
				</div>
				<div>
					<div class="tab-content" style="padding: 10px 10px 0 10px;">
						<?php if (have_catalog()) { ?>
							<div class="tab-pane fade<?php if ($nowActiveTab == 0) { echo ' active show'; }?>" id="leftbar_tab_catalog" role="tabpanel" aria-labelledby="leftbar_tab_catalog_btn">
								<div id="leftbar_catalog"></div>
								<?php if ($options->get('show_headindex_number') == 'true') {?>
									<style>
										#leftbar_catalog ul {
											counter-reset: blog_catalog_number;
										}
										#leftbar_catalog li.index-item > a:before {
											content: counters(blog_catalog_number, '.') " ";
											counter-increment: blog_catalog_number;
										}
									</style>
								<?php }?>
							</div>
						<?php } ?>
						<div class="tab-pane fade text-center<?php if ($nowActiveTab == 1) { echo ' active show'; }?>" id="leftbar_tab_overview" role="tabpanel" aria-labelledby="leftbar_tab_overview_btn">
							<div id="leftbar_overview_author_image" style="background-image: url(<?php echo $options->get('sidebar_auther_image') == '' ? 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48c3ZnIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCAxMDAgMTAwIiB4bWw6c3BhY2U9InByZXNlcnZlIj48cmVjdCBmaWxsPSIjNUU3MkU0MjIiIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIi8+PGc+PGcgb3BhY2l0eT0iMC4zIj48cGF0aCBmaWxsPSIjNUU3MkU0IiBkPSJNNzQuMzksMzIuODZjLTAuOTgtMS43LTMuMzktMy4wOS01LjM1LTMuMDlINDUuNjJjLTEuOTYsMC00LjM3LDEuMzktNS4zNSwzLjA5TDI4LjU3LDUzLjE1Yy0wLjk4LDEuNy0wLjk4LDQuNDgsMCw2LjE3bDExLjcxLDIwLjI5YzAuOTgsMS43LDMuMzksMy4wOSw1LjM1LDMuMDloMjMuNDNjMS45NiwwLDQuMzctMS4zOSw1LjM1LTMuMDlMODYuMSw1OS4zMmMwLjk4LTEuNywwLjk4LTQuNDgsMC02LjE3TDc0LjM5LDMyLjg2eiIvPjwvZz48ZyBvcGFjaXR5PSIwLjgiPjxwYXRoIGZpbGw9IiM1RTcyRTQiIGQ9Ik02Mi4wNCwyMC4zOWMtMC45OC0xLjctMy4zOS0zLjA5LTUuMzUtMy4wOUgzMS43M2MtMS45NiwwLTQuMzcsMS4zOS01LjM1LDMuMDlMMTMuOSw0Mi4wMWMtMC45OCwxLjctMC45OCw0LjQ4LDAsNi4xN2wxMi40OSwyMS42MmMwLjk4LDEuNywzLjM5LDMuMDksNS4zNSwzLjA5aDI0Ljk3YzEuOTYsMCw0LjM3LTEuMzksNS4zNS0zLjA5bDEyLjQ5LTIxLjYyYzAuOTgtMS43LDAuOTgtNC40OCwwLTYuMTdMNjIuMDQsMjAuMzl6Ii8+PC9nPjwvZz48L3N2Zz4=' : $options->get('sidebar_auther_image'); ?>)" class="rounded-circle shadow-sm" alt="avatar"></div>
							<h6 id="leftbar_overview_author_name"><?php echo $options->get('sidebar_auther_name') == '' ? get_bloginfo('name') : $options->get('sidebar_auther_name'); ?></h6>
							<?php $author_desctiption = $options->get('sidebar_author_description'); if (!empty($author_desctiption)) {echo '<h6 id="leftbar_overview_author_description">'. $author_desctiption .'</h6>';}?>
							<nav class="site-state">
								<div class="site-state-item site-state-posts">
									<a <?php $archives_page_url = $options->get('archives_timeline_url'); echo (empty($archives_page_url) ? ' style="cursor: default;"' : 'href="' . $archives_page_url . '"');?>>
										<span class="site-state-item-count"><?php echo wp_count_posts() -> publish; ?></span>
										<span class="site-state-item-name"><?php _e('文章', 'argon');?></span>
									</a>
								</div>
								<div class="site-state-item site-state-categories">
									<button class="btn btn-link" data-toggle="modal" data-target="#blog_categories">
										<span class="site-state-item-count"><?php echo wp_count_terms('category'); ?></span>
										<span class="site-state-item-name"><?php _e('分类', 'argon');?></span>
									</button>
								</div>      
								<div class="site-state-item site-state-tags">
									<button class="btn btn-link" data-toggle="modal" data-target="#blog_tags">
										<span class="site-state-item-count"><?php echo wp_count_terms('post_tag'); ?></span>
										<span class="site-state-item-name"><?php _e('标签', 'argon');?></span>
									</button>
								</div>
							</nav>
							<?php
								/*侧栏个人链接*/
								if ( has_nav_menu('leftbar_author_links') ){
									echo "<div class='site-author-links'>";
									wp_nav_menu( array(
										'container'  => '',
										'theme_location'  => 'leftbar_author_links',
										'items_wrap'  => '%3$s',
										'depth' => 0,
										'walker' => new leftbarAuthorLinksWalker()
									) );
									echo "</div>";
								}
							?>
							<?php
								/*侧栏友情链接*/
								if ( has_nav_menu('leftbar_friend_links') ){
									echo "<div class='site-friend-links'>
											<div class='site-friend-links-title'><i class='fa fa-fw fa-link'></i> Links</div>
											<ul class='site-friend-links-ul'>";
									wp_nav_menu( array(
										'container'  => '',
									    'theme_location'  => 'leftbar_friend_links',
										'items_wrap'  => '%3$s',
									    'depth' => 0,
										'walker' => new leftbarFriendLinksWalker()
									) );
									echo "</ul></div>";
								}else{
									echo "<div style='height: 20px;'></div>";
								}
							?>
							<?php if ( is_active_sidebar( 'leftbar-siteinfo-extra-tools' ) ){?>
								<div id="leftbar_siteinfo_extra_tools">
									<?php dynamic_sidebar( 'leftbar-siteinfo-extra-tools' ); ?>
								</div>
							<?php }?>
						</div>
						<?php if ( is_active_sidebar( 'leftbar-tools' ) ){?>
							<div class="tab-pane fade<?php if ($nowActiveTab == 2) { echo ' active show'; }?>" id="leftbar_tab_tools" role="tabpanel" aria-labelledby="leftbar_tab_tools_btn">
								<?php dynamic_sidebar( 'leftbar-tools' ); ?>
							</div>
						<?php }?>
					</div>
				</div>
			</div>
		</div>
</aside>
<div class="modal fade" id="blog_categories" role="dialog" aria-labelledby="blog_categories_title" aria-modal="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="blog_categories_title"><?php _e('分类', 'argon');?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span>&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<?php
					$categories = get_categories(array(
						'child_of' => 0,
						'orderby' => 'name',
						'order' => 'ASC',
						'hide_empty' => 0,
						'hierarchical' => 0,
						'taxonomy' => 'category',
						'pad_counts' => false
					));
					foreach($categories as $category) {
						echo "<a href=" . get_category_link( $category -> term_id ) . " class='badge badge-secondary tag'>" . $category->name . " <span class='tag-num'>" . $category -> count . "</span></a>";
					}
				?>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="blog_tags" role="dialog" aria-labelledby="blog_tags_title" aria-modal="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="blog_tags_title"><?php _e('标签', 'argon');?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span>&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<?php
					$categories = get_categories(array(
						'child_of' => 0,
						'orderby' => 'name',
						'order' => 'ASC',
						'hide_empty' => 0,
						'hierarchical' => 0,
						'taxonomy' => 'post_tag',
						'pad_counts' => false
					));
					foreach($categories as $category) {
						echo "<a href=" . get_category_link( $category -> term_id ) . " class='badge badge-secondary tag'>" . $category->name . " <span class='tag-num'>" . $category -> count . "</span></a>";
					}
				?>
			</div>
		</div>
	</div>
</div>
<?php
	if (\ArgonModern\Options::instance()->get('page_layout') == 'triple'){
		echo '<aside id="rightbar" class="rightbar widget-area" role="complementary">';
		dynamic_sidebar( 'rightbar-tools' );
		echo '</aside>';
	}
?>
<style>
	/* 确保按钮与链接样式一致 */
	.site-state-item button.btn-link {
		all: unset; /* 移除按钮的默认样式 */
		cursor: pointer; /* 添加指针样式 */
		color: inherit; /* 继承文本颜色 */
		text-decoration: none; /* 移除下划线 */
		display: inline; /* 确保与链接的布局一致 */
	}
	.site-state-item button.btn-link:hover {
		text-decoration: underline; /* 鼠标悬停时添加下划线 */
	}
</style>
