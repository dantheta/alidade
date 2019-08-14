<h1>Import slide content</h1>
<?php if ($_SERVER['REQUEST_METHOD'] == 'GET') { ?>
<form method="POST" enctype="multipart/form-data">

<p><strong>Warning: Importing content will overwrite all currently defined steps and slides.</strong></p>

Select file to import:
<input type="file" name="file0" />
<br />
<input type="submit" value="Upload" />

</form>
<?php } else { ?>
    Upload result: 
    <?php if ($result) { ?>
        <ul>
        <?php foreach ($result as $line) { ?>
            <li><?php echo $line; ?></li>
        <?php } ?>
        </ul>
    <?php } ?>
    <a href="/manage/index">Back to index</a>
<?php } ?>
