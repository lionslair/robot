<?php
error_reporting(E_ALL); ini_set('display_errors', 1);
include_once 'include/process.php';

$robot = new RobotTest();

$data = array('txt_command' => $_REQUEST, 'file' => $_FILES);

$robot->process($data);

 ?>
<!DOCTYPE HTML>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <title>Toy Robot Simulator</title>
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</head>
<body>
    <form action="index.php" method="post" enctype="multipart/form-data" class="form-horizontal simulator">
        <fieldset>
            <legend>Toy Robot Simulator</legend>

            <div class="control-group">
                <label for="command-input" class="control-label">Command</label>
                <div class="controls">
                  <textarea name="command" class="input-xlarge" id="command" rows="10"><?php print $robot->command; ?></textarea>
                </div>
            </div>

          <div class="control-group">
              <label for="fileupload" class="control-label">File</label>
              <div class="controls"><input type="file" name="file" id="file" class="input-file" /></div>
          </div>`

            <?php if($robot->report): ?>
            <div class="alert alert-info"><strong>Robot Position:</strong> <?php print $robot->getFormattedPosition(); ?></div>
            <?php endif; ?>

            <?php if(!empty($robot->getError())): ?>
            <div class="alert alert-error"><strong>error:</strong><?php print $robot->getError(); ?></div>
            <?php endif; ?>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Send Commands</button>
            </div>
        </fieldset>
    </form>

    <div>
      <a href="https://github.com/lionslair/robot" target="_blank">https://github.com/lionslair/robot</a>
    </div>

</body>
</html>
