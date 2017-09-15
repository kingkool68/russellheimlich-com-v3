<?php
include 'helpers.php';
include 'content.php';

// Prevent browser caching...
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<html>
<head>
	<style>
	/* Non style resets */
	body {
		margin: 0;
		padding: 0;
	}
	</style>
<link rel="stylesheet" href="<?php echo maybe_absolute_url(); ?>style.css">
</head>
<body>
	<meta name="viewport" content="width=device-width">
	<div id="sh-back-to-school">


		<div class="sh-back-to-school-intro">
			<div class="intro-gradient-layer">
				<div class="holder">
					<a href="http://thesweethome.com"><img src="<?php echo maybe_absolute_url(); ?>img/the-sweethome.png" width="240" height="26" class="intro-sweethome-logo" srcset="<?php echo maybe_absolute_url(); ?>img/the-sweethome.png 1x, <?php echo maybe_absolute_url(); ?>img/the-sweethome2x.png 2x" alt="The Sweetome"></a>
					<img src="<?php echo maybe_absolute_url(); ?>img/college-101.png" srcset="<?php echo maybe_absolute_url(); ?>img/college-101.png 1x, <?php echo maybe_absolute_url(); ?>img/college-1012x.png 2x" alt="College 101: Start School with the Right Gear" class="intro-image" width="946" height="239">
					<p class="intro-blurb">Get ready for college life! Our researchers have put in more than 150 hours over the past two years tracking down and testing the best school supplies, gadgets, and dorm life gear you'll need to make your return to campus as easy as it can be.</p>
					<p class="intro-for-more"><img src="<?php echo maybe_absolute_url(); ?>img/ruler.png" srcset="<?php echo maybe_absolute_url(); ?>img/ruler.png 1x, <?php echo maybe_absolute_url(); ?>img/ruler2x.png 2x" alt="" width="128" height="19" class="intro-ruler"> For even more <a href="http://thesweethome.com/reviews/college-school-supplies/">college recommendations</a>, as well as supplies for <a href="http://thesweethome.com/reviews/k-12-school-supplies/">school-age kids</a>, read our longer guide at <a href="http://thesweethome.com">The Sweethome</a>.</p>
				</div>
			</div>
		</div>

		<div class="table-of-contents">
			<div class="holder">
				<?php
				$max_items = 0;
				foreach ( $sections as $review_titles ) {
					if ( count( $review_titles ) > $max_items ) {
						$max_items = count( $review_titles );
					}
				}

				foreach ( $sections as $section => $review_titles ):
					$slug = sanitize_title_with_dashes( $section );
					$i = $max_items - count( $review_titles );
				?>
					<div class="table-of-contents-group <?php echo $slug; ?>">
						<a href="#<?php echo $slug;?>"><span class="icon icon-<?php echo $slug;?>" aria-hidden="true"></span></a>
						<h2 class="table-of-contents-title"><a href="#<?php echo $slug;?>"><?php echo $section; ?></a></h2>
						<ul>
							<?php foreach($review_titles as $review_title):
							$review_slug = sanitize_title_with_dashes( $review_title );
							?>
							<li><a href="#<?php echo $review_slug; ?>"><?php echo $review_title; ?></a></li>
						<?php endforeach;

						while( $i > 0 ) {
						?>
							<li class="filler filler-<?php echo $i;?>" aria-hidden="true">&nbsp;</li>
						<?php
							$i--;
						}
						?>
						</ul>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<?php
		foreach( $sections as $section => $review_titles ):
				$section_slug = sanitize_title_with_dashes( $section );
		?>
		<div class="section-divider <?php echo $section_slug;?>-section">
			<div class="holder" id="<?php echo $section_slug; ?>">
				<span class="icon icon-<?php echo $section_slug;?>-white" aria-hidden="true"></span>
				<h3 class="section-divider-title"><?php echo $section; ?></h3>

				<a href="#sh-back-to-school" class="back-to-top">Back to Top <span>&#8593;</span></a>
			</div>
		</div>

		<?php
		foreach( $review_titles as $review_title ):
			$review = get_review( $review_title );
			$review_slug = sanitize_title_with_dashes( $review_title );
		?>

		<div id="<?php echo $review_slug ;?>" class="review holder <?php echo $section_slug; ?>">
			<h4><?php echo $review['title']; ?></h4>
			<?php echo $review['content']; ?>
		</div>

	<?php endforeach; ?>

	<?php endforeach; ?>



		<div class="quick-nav">
		<?php foreach( $sections as $section => $review_title ):
			$slug = sanitize_title_with_dashes( $section );
		?>
			<a href="#<?php echo $slug; ?>" class="<?php echo $slug; ?>">
				<span class="icon icon-<?php echo $slug; ?>">Jump to <?php echo $section; ?></span>
			</a>
		<?php endforeach; ?>
		</div>

	</div>
</body>
</html>
