<?php

    class Printer extends Model {

      public function getSection($project, $step_no){
          $Step = new Step;
          $Slidelist = new Slidelist;

          $step = $Step->getByPosition($step_no);
          $slidelist = $Slidelist->getList();


          $content = $this->getRecap($project, $step_no, $step, $slidelist);
          return $content;
      }

      private function getRecap($project, $step_no, $step, $slidelist) {
          $twig = TwigManager::getInstance();
          // TODO: re-use similar code from projectcontroller

          $Slide = new Slide;

          $answerslides = array();
          foreach($Slide->findProjectSlides($project) as $slide) {
              if ($slide->step == $step_no) {
                  $answerslides[$slide->slide] = json_decode($slide->answer, TRUE);
              }
          }


          $stepslides = array();
          foreach($slidelist as $slide) {
              if ($slide->step == $step_no) {
                  $slide->description = injectAnswers($slide->description,
                                                      $answerslides[$slide->idslide_list],
                                                      $project
                                                      );
                  $stepslides[] = $slide;
              }
          }
          $tmpl = $twig->load('printer/recap.html');
          $s =  $tmpl->render(array(
              'slides' => $stepslides,
              'step' => $step,
          ));
          return $s;
      }

    }
