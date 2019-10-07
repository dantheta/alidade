<?php
/*
    $origin = !isset($original) ? null : $original[0];
    $slideListMenu = $slideMenu;
    reset($slideListMenu);
    while(key($slideListMenu) != $currentSlide ) { next($slideListMenu); }
    $backSlide = prev($slideListMenu);
    $backKey = key($slideListMenu);
*/

    $origin = !isset($original) ? null : $original[0];
    $slideListMenu = $slideMenu;
    $keys = array_keys($slideMenu);
    $flipped_keys = array_flip(array_keys($slideMenu));
    $values = array_values($slideMenu);
    $backSlide = $values[$flipped_keys[$currentSlide] - 1];
    $backKey = $keys[$flipped_keys[$currentSlide] - 1];


?>
<div class="container-fluid slide-<?php echo $currentSlide; ?> step-<?php echo substr($currentSlide, 0, 1); ?> " id="slide-page" >
    <div class="row slide-container">
        <div class="col-md-2 col-sm-4 hidden-xs" id="slide-sidebar">
            <?php include('sidebar.php'); ?>
        </div>
        <div class="col-md-10 col-sm-8 col-xs-12" id="slide-content">
            <div class="row">
              <div class="col-md-10 col-sm-8 col-xs-12">
                <?php if(!empty($backSlide)) { ?>
                <a class="back-link" href="/project/slide/<?php echo $backKey; ?>/?p=<?php echo $hash; ?>&edit"><i class="fa fa-chevron-left"></i> BACK: <?php echo $backSlide; ?></a>
                <?php } ?>
                <h1><?php echo $currentSlide . ' ' . $slide->title; ?></h1>
              </div>
            </div>

            <div class="row">
                <div class="col-md-7 col-sm-8 col-xs-12">
                    <form action="/project/slide/<?php echo $nextSlide . '/?p=' . $projecthash ; ?> " method="post"  id="mainForm">
                        <input type="hidden" name="current_slide"  value="<?php echo $currentSlide; ?>">
                        <input type="hidden" name="hash"  value="<?php echo $projecthash; ?>">
                        <input type="hidden" name="next_slide"  value="<?php echo $nextSlide; ?>">
                        <input type="hidden" name="current_project" value="<?php echo $_SESSION['project']; ?>">
                        <input type="hidden" id="extra-holder" value="<?php echo $origin->extra; ?>">
                        <input type="hidden" name="extra" value="<?php echo(!empty($extra) ? $extra : ''); ?>" id="extra13">

                        <?php

                        if(!is_null($original)) { ?>
                        <input type="hidden" name="slide_update" value="<?php echo $_SESSION['project']; ?>">
                        <?php }
                        if(isset($edit) && $edit == true ) { ?>
                        <input type="hidden" name="edit" value="true">
                        <?php }
                        /** check if this is a recap slide **/
                        if($slide->slide_type == 4){
                          $boxes = injectBox($slide->description);
                          $text = $boxes['content'];
                          echo injectRecap($text);

                        } else {

                          $boxes = injectBox($slide->description);
                          $text = $boxes['content'];

                          $prevAnswer = injectPrevAnswer($text);
                          if($prevAnswer){
                            $text = $prevAnswer['content'];
                          }

                          switch($slide->slide_type){
                            case 1:
                              echo $text;
                              break;
                            case 2:
                              $text = injectChoiceButtons($text);
                              $text = injectChoicePanels($text);
                              $text = injectRadioButtons($text, $origin);
                              $text = injectCheckboxes($text, $origin);
                              $text = injectMultipleAnswerField($text, $origin);
                              echo injectAnswerField($text, 'answer', $origin);
                              break;
                            case 3:
                              echo injectAnswerField($text, 'answer', $origin);
                              break;
                            default:
                              $text = injectParam($text, 'project', $_SESSION['project']);
                              $text = injectParam($text, 'step', $step_number);
                              echo $text;
                              break;
                          }
                          
                        }


                        ?>
                        <div class="row" id="slide-buttons">
                            <div class="col-xs-12 col-sm-12 col-md-12">
                              <?php
                                if($slide->slide_type == 4) {
                              ?>
                              <a href="/printer/output/<?php echo $_SESSION['project'] . '/' . substr($currentSlide, 0, 1); ?>" target="_blank" class="btn btn-alidade btn-lg">Download PDF</a>
                              <?php
                                }
                              ?>
                                <?php
                                if(isset($inProcess) && $inProcess == true){
                                    if($currentSlide == '3.5') {
                                ?>
                                <p></p>
                                <p>Do you need more help to choose, build or implement a tool?</p>
                                <button type="submit" class="btn btn-alidade btn-lg">Yes, we need help</button> or <a href="/project/slide/4.8?p=<?php echo $hash; ?>" class="btn btn-alidade btn-lg">No, we don't</a>
                                <?php
                                }
                                elseif(!is_null($nextSlide) && !empty($nextSlide)) {
                                ?>
                                <button type="submit" class="btn btn-alidade btn-lg">NEXT: <?php echo $slideMenu[$nextSlide]; ?></button>
                                <?php
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-5 col-sm-4 col-xs-12">
                  <aside>
                  <?php
                  if($slide->slide_type == 4) {
                    echo '<img class="img-responsive" src="/assets/images/tool/RecapStep' . $slide->step . '.svg" alt="' . $slide->title . '">';
                  }
                  elseif($slide->slide_type == 1){
                    echo '<img class="img-responsive center-block" src="/assets/images/six-rules/Step' . $slide->step . '.svg" alt="' . $slide->title . '"><p></p>';

                  }
                  ?>
                  <?php echo (!empty($boxes) && isset($boxes) ? implode(' ', $boxes['boxes']) : ''); ?>
                  </aside>
                </div>
            </div>
      </div>
    </div>
</div>

<?php
if ($prevAnswer && false) {
// load modal box for the previous answer editing functionality
?>

<div class="modal fade editPrevAnswer" tabindex="-1" role="dialog" aria-labelledby="editPrevAnswer">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <form action="/save" method="post" class="saveAnswer">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h3 class="modal-title" id="myModalLabel"><?php echo $prevAnswer['slide']->title; ?></h3>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="slide" value="<?php echo $prevAnswer['slide']->idslides; ?>">
                <div class="form-group">
                    <?php
                    if($prevAnswer['multi'] == true){
                        $parts = array_map('trim', explode('##break##', $prevAnswer['slide']->answer));
                        foreach($parts as $i => $part){
                    ?>
                    <textarea class="form-control answer" rows="8" id="answer-<?php echo $i; ?>" name="answer[<?php echo $i; ?>]"><?php echo $part; ?></textarea>
                    <?php
                        }
                    } else { ?>
                    <textarea class="form-control" rows="8" id="answer" name="answer"><?php echo $prevAnswer['slide']->answer; ?></textarea>
                    <?php } ?>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save changes</button>
            </div>
        </form>
    </div>
  </div>
</div>

<?php } ?>

<?php if($currentSlide == '1.0' && false){ ?>
  <div class="modal fade welcome" tabindex="-1" role="dialog" aria-labelledby="welcome">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h2 class="modal-title" id="myModalLabel">Hello!</h2>
        </div>
        <div class="modal-body">
          <h4>Thanks for using Alidade! We hope it helps you.</h4>
          <p>Follow the steps to create a strategy plan for your tech project. You can skip steps and complete them in any order.</p>
          <p>Used Alidade before? <a href="#" class="register-from-modal" data-toggle="modal" data-target="#user-forms">Login</a>.</p>
          <p>Want to save your progress for later? <a href="#" class="register-from-modal" data-toggle="modal" data-target="#user-forms">Register</a>.</p>
          <p>All your data will be saved automatically until you close this page.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-alidade btn-lg" data-dismiss="modal">Let's get started</button>
        </div>
      </div>
    </div>
  </div>

<?php } ?>
