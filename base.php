<?php
use Roots\Sage\Setup;
use Roots\Sage\Wrapper;
?>
    <?php
      do_action('get_header');
      get_template_part('templates/header');
    ?>
    <div class="wrap" role="document">
      <div class="content container">
        <main class="main row">
          <?php 
          	include Wrapper\template_path();
          	global $wp;
			$type = key($wp->query_vars);
		  ?>
          <?php get_template_part('templates/' . $type); ?>
          <?php 
          	if (Setup\display_sidebar()) : ?>
        	<aside class="sidebar col-md-4">
        		<?php include Wrapper\sidebar_path(); ?>
        	</aside><!-- /.sidebar -->
        	<?php endif; ?>
        </main><!-- /.main -->
      </div><!-- /.content -->
    </div><!-- /.wrap -->
    <?php
      do_action('get_footer');
      get_template_part('templates/footer');
      wp_footer();
    ?>
  </body>
</html>