<div class="container">
    
    <div class="col-md-8 col-md-offset-2">
        <h1 class="h2">Edit Step</h1>
        <form class="" action="/manage/step/<?php echo $step->idsteps; ?>" method="post" id="step-form">
            <?php
            if(isset($response) && !empty($response)) {
                printResponse($response);     
            }
            ?>
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" name="title" id="title" value="<?php echo $step->title; ?>" class="form-control">
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <div class="textarea" name="description" id="description"><?php echo $step->description; ?></div>
            </div>
            
            
            <button class="btn btn-main" type="submit" id="save-step-form"  data-form="#step-form"><i class="fa fa-save"></i> SAVE</button>
            
        </form>
        
    </div>
    <div class="col-md-2">
        <br />
        <a href="/manage" class="btn btn-sm btn-alt pull-right">back to management <i class="fa fa-angle-right"></i></a>
    </div>
</div>

