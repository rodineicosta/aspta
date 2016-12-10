		<footer class="footer clearfix">
			<div class="container">
				<div class="row">
					<div class="col-md-4 col-sm-6 footer-assinatura">
						<?php dynamic_sidebar('sidebar-logo-text-footer'); ?>
					</div><!-- /.footer-assinatura -->
    	<div class="col-md-8 footer-menu hidden-xs hidden-sm">
    	  <?php dynamic_sidebar('sidebar-footer-one'); ?>
    	  <?php dynamic_sidebar('sidebar-footer-two'); ?>
    	  <?php dynamic_sidebar('sidebar-footer-three'); ?>
    	  <?php dynamic_sidebar('sidebar-footer-four'); ?>
        </div>
    </div>
  </div>
  <div class="footer-redes-sociais">
				<div class="container">
					<div class="row">
						<div class="col-md-12">
							<div class="footer-icones">
								<ul>
									<li class="icone"><a href="http://www.facebook.com/asptaagroecologia/" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon-facebook.png" alt=""/></a></li>
									<li class="icone"><a href="http://plus.google.com" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon-gplus.png" alt=""/></a></li>
									<li class="icone"><a href="http://www.youtube.com/channel/UCfRvRTdz58FkPVese6OzQmA" target="_blank"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/icon-youtube.png" alt=""/></a></li>
								</ul>
							</div>
						</div>
					</div>					
				</div>
			</div>
			<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
			<!--script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script-->
			<!-- Include all compiled plugins (below), or include individual files as needed -->
			<!--script src="<?php bloginfo('template_url'); ?>/assets/scripts/bootstrap.min.js"></script-->
			<script src="<?php bloginfo('template_url'); ?>/assets/scripts/classie.js"></script>
			<script src="<?php bloginfo('template_url'); ?>/assets/scripts/uisearch.js"></script>
			<script src="<?php bloginfo('template_url'); ?>/assets/scripts/jquery.cycle2.min.js"></script>
			<script>
				new UISearch( document.getElementById( 'sb-search' ) );
			</script>
			<?php wp_footer(); ?>		
		</footer>
