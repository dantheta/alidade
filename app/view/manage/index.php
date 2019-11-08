<?php function swaplink($base, $id, $dir) { ?>
    <span style="margin-right: 0.4em" class="swap <?php echo $dir; ?>">
    <?php if ($dir == "up") { ?>
    <a href="<?php echo $base . $dir . '/' . $id; ?>" title="Move up">
        <span class="glyphicon glyphicon-arrow-up"></span>
    <?php } elseif ($dir == "down") { ?>
    <a href="<?php echo $base . $dir . '/' . $id; ?>" title="Move down">
        <span class="glyphicon glyphicon-arrow-down"></span>
    <?php } ?>
    </a>
    </span>
<?php } ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Manage Content</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">

            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class=""><a data-toggle="tab" href="#pages">Pages</a></li>
                <li role="presentation" class="active"><a data-toggle="tab" href="#steps">Steps</a></li>
                <li role="presentation" class=""><a data-toggle="tab" href="#slides">Slides</a></li>
                <li role="presentation" class=""><a data-toggle="tab" href="#manage">Manage</a></li>
            </ul> <!-- /.nav-tabs -->

            <div class="tab-content">

                <div id="manage" role="tabpanel" class="tab-pane manage-pane">
                    <h2>Management</h2>
                    <div><a href="/manage/import" class="btn btn-default">Import Content</a></div>
                    <div><a href="/manage/export" class="btn btn-default">Export Content</a></div>
                </div>

                <div id="pages" role="tabpanel" class="tab-pane manage-pane">
                    <div style="float: right"><a class="btn btn-default" href="/manage/page/new">Add new page</a></div>
                    <h2>Pages</h2>
                    <table class="table">
                        <?php foreach ( $pages as $page ){ ?>
                        <tr>
                            <td><a href="/manage/page/<?php echo $page->idpages; ?>"><?php echo $page->title; ?></a></td>
                            <td>/page/<?php echo $page->url; ?></td>
                            <td>
                            <a class="deletepage" href="/manage/pagedel/<?php echo $page->idpages; ?>">
                              <span class="glyphicon glyphicon-trash"></span> Delete</a>
                            </td>

                        </tr>
                        <?php  } ?>
                    </table>
                </div>

                <div id="steps" role="tabpanel" class="tab-pane manage-pane active">
                    <div style="float: right"><a class="btn btn-default" href="/manage/step/new">Add new step</a></div>
                    <h2>Steps</h2>

                    <table class="table">
                        <?php foreach ( $steps as $step ){ ?>
                        <tr>
                            <td><a href="/manage/step/<?php echo $step->idsteps; ?>"><?php echo $step->title; ?></a></td>
                            <td class="">
                                <?php swaplink('/manage/step', $step->idsteps, 'up') ?>
                                <?php swaplink('/manage/step', $step->idsteps, 'down') ?>
                            <a class="deletestep" href="/manage/stepdel/<?php echo $step->idsteps; ?>">
                              <span class="glyphicon glyphicon-trash"></span> Delete</a>
                            </td>
                        </tr>
                        <?php  } ?>
                    </table>
                </div>

                <div id="slides" role="tabpanel" class="tab-pane manage-pane">
                    <div style="float: right"><a class="btn btn-default" href="/manage/slide/new">Add new slide</a></div>
                    <h2>Slides</h2>

                    <table class="table">
                    <?php $last = null; ?>
                    <?php foreach ( $slides as $slide ){ ?>
                        <?php if ($slide->step != $last) { ?>
                    <tr>
                        <td colspan="3"><h3><?php echo $slide->step; ?></td>
                    </tr>
                            <?php } ?>
                        <?php $last = $slide->step; ?>
                        <tr>
                            <td><?php echo $slide->step . '.' . $slide->position; ?> <a href="/manage/slide/<?php echo $slide->idslide_list; ?>"><?php echo $slide->title; ?></a></td>
                            <td class="">
                                <?php swaplink('/manage/slide', $slide->idslide_list, 'up') ?>
                                <?php swaplink('/manage/slide', $slide->idslide_list, 'down') ?>
                            <a class="deleteslide" href="/manage/slidedel/<?php echo $slide->idslide_list; ?>">
                              <span class="glyphicon glyphicon-trash"></span> Delete</a>
                            </td>
                        </tr>
                    <?php  } ?>
                    </table>
                </div>

            </div> <!-- /.tab-content -->

        </div>
    </div>
    
</div>

