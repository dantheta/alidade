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
          <h3><a class="slide <?php echo checkSlidePosition($step_number, $slide_number, $step, 0, null); ?>" href="/project/<?php echo $piece; ?>/<?php echo $step; ?>.0<?php echo (!is_null($hash) ? '/?p=' . $hash : ''); ?>">
            <?php  echo $step_titles[$step]; ?>
          </a></h3>
  		</header>
  		<ul>
  			<?php foreach($entries as $i){ ?>
  			<li>
  				<a
  				   title="<?php echo $slideMenu[$step . '.' . $i]; ?>"
  				   class="slide <?php echo checkSlidePosition($step_number, $slide_number, $step, $i, $projectIndex[$step . '.' . $i]); ?>"
                   href="/project/<?php echo $piece; ?>/<?php echo $step; ?>.<?php echo $i; ?><?php echo (!is_null($hash) ? '/?p=' . $hash : ''); ?>&edit"
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
  <h3><a class="slide <?php echo checkSlidePosition($step_number, $slide_number, $step+1, 0, null); ?>" href="/project/<?php echo $piece; ?>/<?php echo $step + 1?>.0<?php echo (!is_null($hash) ? '/?p=' . $hash : ''); ?>">CONGRATULATIONS</a></h3>
</header>
</div>


<?php } ?>
