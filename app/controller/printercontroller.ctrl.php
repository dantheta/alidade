<?php
    //require_once(ROOT . DS . 'lib' . DS . 'vendor' . DS . 'html2pdf_v4.6.0' . DS . 'src/Html2Pdf.php');
    // require_once(ROOT . DS . 'lib' . DS . 'vendor' . DS . 'dompdf' . DS . 'autoload.inc.php');
    require_once(ROOT . DS . 'lib' . DS . 'vendor' . DS . 'html_to_doc.inc.php');

    use Dompdf\Dompdf;
    class PrinterController extends Controller {

        public function output( $project, $step, $type = 'pdf' ){
            if ($type != 'html') {
                error_reporting(0);
            }
            $Project = new Project;
            $Slide = new Slide;

            $data = $Project->findProject($project);
            $steps = array();
            foreach($data['slides'] as $slide){
                if($slide->step == $step) {
                    $steps[] = $slide;
                }
            }

            $sections = array();
            for($i = 1; $i <= $step; $i++) {
                $sections[] = $this->Printer->getSection($project, $i);
            }
            $args = array(
                'date_created' => strftime('%a, %d %b %Y', $data['creation']),
                'date' => strftime('%a, %d %b %Y', time()),
                'sections' => $sections,
                'type' => $type,
                'title' => $data['title']
            );

            $twig = TwigManager::getInstance();
            $tmpl = $twig->load('printer/output.html');
            $content = $tmpl->render($args);

            if($type == 'pdf'){
                // instantiate and use the dompdf class
                $dompdf = new Dompdf();
                $dompdf->loadHtml($content);

                // (Optional) Setup the paper size and orientation
                $dompdf->setPaper('A4', 'portrait');

                // Render the HTML as PDF
                $dompdf->render();

                // Output the generated PDF to Browser
                $dompdf->stream($data['title'] . '.pdf', array('Attachment' => false));

            } elseif ($type == 'html') {
                echo $content;
            }
            elseif($type == 'doc'){
                $doc = $htmltodoc= new HTML_TO_DOC();
                header("Content-type: application/x-msword");
                $doc->createDoc($content,"Alidade-" . $step, true);
            }
        }

        public function six_rules(){
          if(isset($_POST) && !empty($_POST)){
            $is_doc = (isset($_POST['is_doc']) ? true : false);
            unset($_POST['is_doc']);
            $titles = array(
              'step1' => 'Why using a technology tool is the right way to address the problem',
              'step2' => 'Why our users might want to use the type of technology tool we are thinking about and what might stop them from using it',
              'step3' => 'List of existing tools that can do the things we want',
              'step4' => 'List of people or organisations that have used a similar tool in their projects',
              'step5' => 'List of people who we could trial the tool with',
              'step6' => 'How we will adjust our project plan if unexpected changes occur'
            );

            $head .= '<html>
                          <head>
                            <style>
                              html, @page { margin: 1cm 1.5cm; }
                              h1, h2, h3, h4 { font-family: "Oswald"; font-weight: normal; text-transform: uppercase; }
                              h1 { margin-top: 0cm; color: #454354; }
                              .meta-data { font-weight: bold; color: #999; text-transform: uppercase; font-size: 14px; }
                              body { font-family: "Lato"; color: #555555; }
                              p.small { font-size: 10px; }
                              header h1 { font-size: 57px; line-height: 65px;   }
                              a { color: #DE694B; text-decoration: none; }
                              ul.chars { list-style: none; }
                              ul.chars li h4 { margin-top: 0px; }
                            </style>
                          </head>
                          <body>
                            <header >
                              <h1>Alidade</h1>
                              <h2>SIX RULES FOR CHOOSING TECHNOLOGY</h2>
                              <p class="meta-data">PDF Document Generation: ' . strftime('%a, %d %b %Y', time()) . '</p>
                              <p>
                                This document was produced by <a href="https://alidade.tech">Alidade</a>, (<a href="https://alidade.tech">https://alidade.tech</a>), an interactive guide that guides social change organisations to the right technology tool for their projects.
                                <br />
                                It summarises your research on your users’ needs, your technology requirements and needs for help from partners.
                                <br />
                                You can use it to:
                              </p>
                              <ul>
                                <li>Agree what you need with your colleagues.</li>
                                <li>Explain your requirements to a technical partner.</li>
                                <li>Demonstrate to a funder that you have done your homework.</li>
                              </ul>
                            </header>';

            foreach($_POST as $i => $answer){
              $answer = strip_tags($answer);
              $answer = filter_var($answer, FILTER_SANITIZE_STRING);

              $body .= '<h3>'.$titles[$i].'</h3>';
              $body .= '<p>'.$answer.'</p>';
            }

            $foot = '<p class="small">The guide was built by <a href="https://www.theengineroom.org">The Engine Room</a>, <a href="http://pawa254.org">Pawa254</a> and the <a href="http://www.networksociety.co.za">Network Society Lab</a> at the University of the Witwatersrand. It is part of a research project supported by <a href="http://www.makingallvoicescount.org/">Making All Voices Count</a></p></body></html>';

            if(!$is_doc){
              $document = $head . $body . $foot;
              $dompdf = new Dompdf();
              $dompdf->loadHtml($document);
              // (Optional) Setup the paper size and orientation
              $dompdf->setPaper('A4', 'portrait');
              // Render the HTML as PDF
              $dompdf->render();
              // Output the generated PDF to Browser
              $dompdf->stream('Alidade-Six-Rules.pdf', array('Attachment' => false));

            }
            else {
                  $doc = $htmltodoc= new HTML_TO_DOC();
                  $head = '<h1>Alidade</h1>
                  <h2>SIX RULES FOR CHOOSING TECHNOLOGY</h2>
                  <p class="meta-data">PDF Document Generation: ' . strftime('%a, %d %b %Y', time()) . '</p>
                  <p>
                    This document was produced by <a href="https://alidade.tech">Alidade</a>, (<a href="https://alidade.tech">https://alidade.tech</a>), an interactive guide that guides social change organisations to the right technology tool for their projects.
                    <br />
                    It summarises your research on your users’ needs, your technology requirements and needs for help from partners.
                    <br />
                    You can use it to:
                  </p>
                  <ul>
                    <li>Agree what you need with your colleagues.</li>
                    <li>Explain your requirements to a technical partner.</li>
                    <li>Demonstrate to a funder that you have done your homework.</li>
                  </ul>';
                  $foot = '<p class="small">The guide was built by <a href="https://www.theengineroom.org">The Engine Room</a>, <a href="http://pawa254.org">Pawa254</a> and the <a href="http://www.networksociety.co.za">Network Society Lab</a> at the University of the Witwatersrand. It is part of a research project supported by <a href="http://www.makingallvoicescount.org/">Making All Voices Count</a></p>';
                  $content = '<body>' . $head . $body . $foot . '</body>';
                  $doc->createDoc($content, "Alidade-Six-Rules", true);
            }
          }
        }

    }
