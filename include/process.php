<?php

class RobotTest {

  public $command = '';

  public $error = '';

  public $position = array(0,0, 'North');

  public $table = array();

  public $report = FALSE;

  public function __construct() {
    $this->table = array_fill(0,5, array_fill(0, 5, 'free'));
  }

  public function process($data) {
    $this->command = $this->findCommand($data);
    // print  '<pre>'.  $this->command.'</pre>';

    if ($this->command == FALSE) {
      $this->error = 'No command was found';
      return;
    }

    $this->performOperations($this->command);
  }

  function getError() {
    return $this->error;
  }

 public function findCommand($data) {
  //  print_r($data);

   if (!empty($data['file']['file']['tmp_name']) &&
       file_exists($data['file']['file']['tmp_name']) &&
       is_readable($data['file']['file']['tmp_name'])) {
     return file_get_contents($data['file']['file']['tmp_name']);
   }
   else if (!empty($data['txt_command']['command'])) {
     return $data['txt_command']['command'];
   }
   else {
     return FALSE;
   }

 }


 public function performOperations($commands) {

   if (is_string($commands)) {
     $commands = explode("\n", $commands);
   }

  //  var_dump($commands);

  foreach ($commands AS $command) {
    if($this->isValidOperation($command)) {

      $command = trim($command);

      // print  '<pre>'.$command.'</pre>';

      switch ($command) {
        case 'MOVE':
          $this->doMove();
          break;
       case 'LEFT':
         $this->doTurn('LEFT');
         break;
        case 'RIGHT':
          $this->doTurn('RIGHT');
          break;
        case 'REPORT':
          $this->report = TRUE;
          break;
       default:
        // We must have place

        $command = substr($command,6);

        // var_dump($command);
        list($x, $y, $direction) = explode(',', $command);

        $this->setPosition($x,$y,$direction);
         break;
      }
    }
  }

 }

  /**
   *
   * Positoon and movement
   */
  private function setPosition($x = 0, $y = 0, $directory = 'North') {
    $this->position = array($x, $y, $directory);
  }

  private function getPosition() {
    return $this->position;
  }

  public function getFormattedPosition() {
    return implode(',',$this->getPosition());
  }

  private function doTurn($direction) {
    $position = $this->getPosition();

    $current_direction = $position[2];
    $x = $position[0];
    $y = $position[1];

    if ($direction == 'LEFT') {
      // LEFT
      switch($current_direction) {
        case 'NORTH':
          $this->setPosition($x, $y, 'WEST');
          break;
        case 'WEST':
        $this->setPosition($x, $y, 'SOUTH');
            break;
        case 'SOUTH':
          $this->setPosition($x, $y, 'EAST');
          break;
        case 'EAST':
          $this->setPosition($x, $y, 'NORTH');
          break;
      }
    } else {
      // RIGHT
      switch($current_direction) {
        case 'NORTH':
          $this->setPosition($x, $y, 'EAST');
          break;
        case 'EAST':
          $this->setPosition($x, $y, 'SOUTH');
          break;
        case 'SOUTH':
          $this->setPosition($x, $y, 'WEST');
          break;
        case 'WEST':
          $this->setPosition($x, $y, 'NORTH');
          break;
      }
    }

  }

  private function doMove() {
    $position = $this->getPosition();

    if ($this->checkMoveIsValid()) {
      print "Move permitted<br />";
      $direction = $position[2];
      $x = $position[0];
      $y = $position[1];

      switch($direction) {
        case 'NORTH':
          return $this->setPosition($x, ($y+1), 'NORTH');
          break;
        case 'EAST':
          return $this->setPosition(($x-1), $y, 'EAST');
          break;
        case 'SOUTH':
          return $this->setPosition($x, ($y-1), 'SOUTH');
          break;
        case 'WEST':
          return $this->setPosition(($x+1), $y, 'WEST');
          break;
      }
    } else {
      print "position invalid skipped<br />";
    }

  }


  /**
  * Validation functions
  */

  private function checkMoveIsValid() {
    $position = $this->getPosition();

    // print  '<pre>'.print_r($position).'</pre>';
    // print  '<pre>'.print_r($this->table).'</pre>';

    $direction = $position[2];
    $x = $position[0];
    $y = $position[1];

    switch($direction) {
      case 'NORTH':
        return $this->testPositionExists($x, ($y+1));
        break;
      case 'EAST':
        return $this->testPositionExists(($x-1), $y);
        break;
      case 'SOUTH':
        return $this->testPositionExists($x, ($y-1));
        break;
      case 'WEST':
        return $this->testPositionExists(($x+1), $y);
        break;
    }

  }

  private function testPositionExists($x, $y) {

    if (isset($this->table[$x]) &&
        isset($this->table[$y])) {
      return TRUE;
    }

    return FALSE;
  }

  private function isValidOperation($op) {
  if ($this->isMove($op) ||
      $this->isLeft($op) ||
      $this->isRight($op) ||
      $this->isPlace($op) ||
      $this->isReport($op)
     ) {
    return TRUE;
  } else {
    return FALSE;
  }

  }

  public function isMove($op) {
    $regex = '/^MOVE/';

    return $this->regexCommand($regex,$op);
  }

  public function isLeft($op) {
    $regex = '/^LEFT/';

    return $this->regexCommand($regex,$op);
  }

  public function isRight($op) {
    $regex = '/^RIGHT/';

    return $this->regexCommand($regex,$op);
  }

  public function isPlace($op) {
    $regex = '/^PLACE [0-4],[0-4],(?:NORTH|EAST|SOUTH|WEST)/';

    return $this->regexCommand($regex,$op);
  }

  public function isReport($op) {
    $regex = '/^REPORT/';

    return $this->regexCommand($regex,$op);
  }


  public function regexCommand($regex, $op) {
    $result = preg_match($regex, $op);

    if ($result == 1) {
        return true;
    } else {
        return false;
    }
  }

}


// test the command is valid.
