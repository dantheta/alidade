<?php function swaplink($base, $id, $dir) { ?>
    <div class="swap <?php echo $dir; ?>">
    <a href="<?php echo $base . $dir . '/' . $id; ?>">
    <?php if ($dir == "up") { ?>
        <span class="glyphicon glyphicon-arrow-up"></span> Move up
    <?php } elseif ($dir == "down") { ?>
        <span class="glyphicon glyphicon-arrow-down"></span> Move down
    <?php } ?>
    </a>
    </div>
<?php } ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Manage Content</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <a href="/manage/import" class="btn btn-default">Import Content</a>
            <a href="/manage/export" class="btn btn-default">Export Content</a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-5">
            <h2>Pages</h2>
            <ul class="object-list">
                <?php foreach ( $pages as $page ){ ?>
                <li>
                    <a href="/manage/page/<?php echo $page->idpages; ?>"><?php echo $page->title; ?></a>
                </li>
                <?php  } ?>
            </ul>
            <h2>Steps</h2>
            <ul class="object-list">
                <?php foreach ( $steps as $step ){ ?>
                <li>
                    <a href="/manage/step/<?php echo $step->idsteps; ?>"><?php echo $step->title; ?></a>
                    <span class="movelinks">
                        <?php swaplink('/manage/step', $step->idsteps, 'up') ?>
                        <?php swaplink('/manage/step', $step->idsteps, 'down') ?>
                    </span>
                    <a class="deletelink deletestep" href="/manage/stepdel/<?php echo $step->idsteps; ?>">
                      <span class="glyphicon glyphicon-trash"></span> Delete</a>
                </li>
                <?php  } ?>
            </ul>
            <div><a class="btn btn-default" href="/manage/step/new">Add new step</a></div>
        </div>
        
        <div class="col-md-7" id="manage-slide-list">
            <h2>Slides</h2>
            <?php $last = null; ?>
            
            <?php foreach ( $slides as $slide ){ ?>
                <?php if ($slide->step != $last) { ?>
                    <?php if ($last != null) { ?>
            </ul>
                    <?php } ?>
            <h3>Step <?php echo $slide->step ?> <small>[<a class="expander-link" href="#">Expand</a>]</small>  </h3>
            <ul class="object-list hidden">
                <?php $last = $slide->step; } ?>
                <li>
                    <?php echo $slide->step . '.' . $slide->position; ?> <a href="/manage/slide/<?php echo $slide->idslide_list; ?>"><?php echo $slide->title; ?></a>
                    <span class="movelinks">
                        <?php swaplink('/manage/slide', $slide->idslide_list, 'up') ?>
                        <?php swaplink('/manage/slide', $slide->idslide_list, 'down') ?>
                    </span>
                    <a class="deletelink deleteslide" href="/manage/slidedel/<?php echo $slide->idslide_list; ?>">
                      <span class="glyphicon glyphicon-trash"></span> Delete</a>
                </li>
            <?php  } ?>
            </ul>
            <div><a class="btn btn-default" href="/manage/slide/new">Add new slide</a></div>

        </div>
        
    </div>
</div>

