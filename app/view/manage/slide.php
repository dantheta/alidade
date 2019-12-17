<div class="container">
    
    <div class="row">
        <div class="col-md-9">
            <h1>Edit "<?php echo $slide->title; ?>"</h1>

            <a href="/manage#slides" class="btn btn-alidade"><i class="fa fa-angle-left"></i> back to management</a>
            <form class="/manage/slide/<?php echo $slide->step . '/' . $slide->position; ?>" method="post" id="slide-form">
                <input id="id" name="id" type="hidden" value="<?php echo $slide->idslide_list; ?>">
        
                <div class="form-group">
                    Title: <input type="text" id="title" name="title" class="form-control" value="<?php echo $slide->title; ?>">
                </div>
                <div class="form-group">
                    Position: <input type="text" id="position" name="position" class="form-control" value="<?php echo $slide->position; ?>">
                </div>
                <div class="form-group">
                    Step: <select id="step" name="step">
                    <?php foreach($steps as $step) { ?>
                        <option value="<?php echo $step->idsteps ?>" <?php echo ($slide->step == $step->idsteps ? "selected" : "") ?> ><?php echo $step->position . '. ' . $step->title ?></option>
                    <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    Slide type: <select id="slide_type" name="slide_type">
                        <option value="1" <?php echo ($slide->slide_type == 1) ? "selected" : "" ?>>Informative</option>
                        <option value="2" <?php echo ($slide->slide_type == 2) ? "selected" : "" ?>>Interactive</option>
                        <option value="3" <?php echo ($slide->slide_type == 3) ? "selected" : "" ?>>Branching</option>
                        <option value="4" <?php echo ($slide->slide_type == 4) ? "selected" : "" ?>>Recap</option>
                    </select>
                </div>

                <div class="form-group">
                    <div class="textarea form-control" name="description" id="description"><?php echo $slide->description; ?></div>
                    <?php /* <textarea rows="25" name="description" id="description" class="form-control" data-provide="markdown" data-iconlibrary="fa"><?php echo $slide->description; ?></textarea> */ ?>
                </div>
                <div class="row">
                    <h3>Track</h3>
                    <div class="col-md-4">
                        <h4>Developer</h4>
                        <ul>
                            <li><input type="radio" name="track_developer" value=""  <?php echo (empty($slide->track_developer) ? 'checked': '') ?> />Any</li>
                            <li><input type="radio" name="track_developer" value="1" <?php echo ($slide->track_developer == 1 ? 'checked': '') ?>/>Solo</li>
                            <li><input type="radio" name="track_developer" value="2" <?php echo ($slide->track_developer == 2 ? 'checked': '') ?>/>Organisation</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h4>Product</h4>
                        <ul>
                            <li><input type="radio" name="track_product" value="" <?php echo (empty($slide->track_product) ? 'checked': '') ?> />Any</li>
                            <li><input type="radio" name="track_product" value="1" <?php echo ($slide->track_product == 1 ? 'checked': '') ?> />New</li>
                            <li><input type="radio" name="track_product" value="2" <?php echo ($slide->track_product == 2 ? 'checked': '') ?> />Existing</li>
                        </ul>
                    </div>
                </div>
                <div class="form-group">
                    <button type="submit" id="save-form" data-form="#slide-form" class="btn btn-primary">save</button>
                </div>
            </form>
        </div>
        <div class="col-md-3">
            <h3>Worth Noting</h3>
            
            <p>Please take care while editing slide content. You might come across strange placeholders, or reference/pointers couples that are used to maintain some of the frontend functionalities. Here are some of them, so you know what's going on. </p>
            
            <ul>
                <li><strong>[--answer--]</strong><br />This is used as a placeholder for the textarea in the slide contents. </li>
                <li><strong>[--multiple-answer-counter--]</strong><br />This is used as a placeholder for multiple textareas in the slide contents. Note that "counter" must be a growing digit (starting at 0) and must be different for every placeholder. Also note that this functionality is currently supported exclusively for slides 3.2, 4.2 and 4.5</li>
                <li><strong>[--prev|step.slide--]</strong><br />This will print a box with the answer from the slide you select with a link to edit that answer. An example can be: [--prev|1.4--]. This would print a box with the answer from slide number 4 of step 1. </li>
                <li>
                    <strong>[--box|type--]content[--endbox--]</strong><br />You can create boxes that can go pretty much everywhere you want. Supported values for <em>type</em> are:
                    <ul>
                        <li>questions</li>
                        <li>example</li>
                        <li>casestudy</li>
                        <li>research</li>
                        <li>tips</li>                        
                    </ul>
                </li>
            </ul>
            
        </div>
    </div>
    
</div>
