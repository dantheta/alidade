<?php
if(!isset($page) || $page !== 'start') {
$currs = explode('.', $currentSlide);
?>
<?php
$piece = ($inTour ? 'tour' : 'slide');
?>

    <?php foreach($slideindex as $step => $entries) { 
             if ($step == "fullIndex") {
                 continue;
             } ?>
    <div class="step step<?php echo $step; ?> <?php echo ($currs[0] == 1 ? '' : 'hidden-xs'); ?>">
  		<header>
          <h3><a class="slide <?php echo checkSlidePosition($step_number, $slide_number, 1, 0); ?>" href="/project/<?php echo $piece; ?>/1.0<?php echo (!is_null($hash) ? '/?p=' . $hash : ''); ?>">STEP <?php echo $step ?></a></h3>
  		</header>
  		<ul>
  			<?php for($i = 1; $i <= count($entries); $i++){ ?>
  			<li>
  				<a
  				   title="<?php echo $slideMenu[$step . '.' . $i]; ?>"
  				   class="slide <?php echo checkSlidePosition($step_number, $slide_number, 1, $i); ?>"
  				   href="/project/<?php echo $piece; ?>/1.<?php echo $i; ?><?php echo (!is_null($hash) ? '/?p=' . $hash : ''); ?>&edit"
  				>
  					<?php echo $slideMenu[$step . '.' . $i]; ?>
  				</a>
  			</li>
  			<?php } ?>
  		</ul>
    </div>
    <?php } ?>


  

<div class="step">
<header>
  <h3><a class="slide <?php echo checkSlidePosition($step_number, $slide_number, 4, 8); ?>" href="/project/<?php echo $piece; ?>/4.8<?php echo (!is_null($hash) ? '/?p=' . $hash : ''); ?>">CONGRATULATIONS</a></h3>
</header>
</div>


<?php } ?>
