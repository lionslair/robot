<?php

class RobotTest {

  public $command  = '';
  public $error    = '';
  public $position = array(0,0, 'North');
  public $table    = array();
  public $report   = FALSE;

  /**
   *
   * Constructor
   */
  public function __construct() {
    $this->table = array_fill(0,5, array_fill(0, 5, 'free'));
  }

  /**
   *
   * Main process function
   * @param  Array $data Holds the submitted form values
   */
  public function process($data) {
    $this->command = $this->findCommand($data);
    // print  '<pre>'.  $this->command.'</pre>';

    // Check a command was submitted
    if ($this->command == FALSE) {
      $this->error = 'No command was found';
      return;
    }

    $this->performOperations($this->command);
  }

  /**
   * Get the error message
   * @return String Error text
   */
  function getError() {
    return $this->error;
  }

  /**
   * @return String      Return the commands to run else FALSE
   */
 public function findCommand($data) {

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

  /**
   * Run the commands
   */
  public function performOperations($commands) {

    if (is_string($commands)) {
      $commands = explode("\n", $commands);
    }

    foreach ($commands AS $command) {
      if($this->isValidOperation($command)) {

        $command = trim($command);

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

  /**
   * Get the position
   */
  private function getPosition() {
    return $this->position;
  }

  /**
   * Get Formatted Position
   */
  public function getFormattedPosition() {
    return implode(',',$this->getPosition());
  }

  /**
   * Perform a turn in direction
   */
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

  /**
   * Move the robot
   */
  private function doMove() {
    $position = $this->getPosition();

    if ($this->checkMoveIsValid()) {
      // print '<div class="alert alert-info">Move permitted</div>';
      $direction = $position[2];
      $x = $position[0];
      $y = $position[1];

      switch($direction) {
        case 'NORTH':
          return $this->setPosition($x, ($y+1), 'NORTH');
          break;
        case 'EAST':
          return $this->setPosition(($x+1), $y, 'EAST');
          break;
        case 'SOUTH':
          return $this->setPosition($x, ($y-1), 'SOUTH');
          break;
        case 'WEST':
          return $this->setPosition(($x-1), $y, 'WEST');
          break;
      }
    } else {
      // print '<div class="alert alert-danger">position invalid skipped</div>';
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
        return $this->testPositionExists(($x+1), $y);
        break;
      case 'SOUTH':
        return $this->testPositionExists($x, ($y-1));
        break;
      case 'WEST':
        return $this->testPositionExists(($x-1), $y);
        break;
    }

  }

  /**
   * Test the position exists
   */
  private function testPositionExists($x, $y) {

    if (isset($this->table[$x]) &&
        isset($this->table[$y])) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Validate the operation
   */
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

  /**
   * Validate command element
   */
  public function isMove($op) {
    $regex = '/^MOVE/';

    return $this->regexCommand($regex,$op);
  }

  /**
   * Validate command element
   */
  public function isLeft($op) {
    $regex = '/^LEFT/';

    return $this->regexCommand($regex,$op);
  }

  /**
   * Validate command element
   */
  public function isRight($op) {
    $regex = '/^RIGHT/';

    return $this->regexCommand($regex,$op);
  }

  /**
   * Validate command element
   */
  public function isPlace($op) {
    $regex = '/^PLACE [0-4],[0-4],(?:NORTH|EAST|SOUTH|WEST)/';

    return $this->regexCommand($regex,$op);
  }

  /**
   * Validate command element
   */
  public function isReport($op) {
    $regex = '/^REPORT/';

    return $this->regexCommand($regex,$op);
  }

  /**
   * Helper function to validate the commands
   */
  public function regexCommand($regex, $op) {
    $result = preg_match($regex, $op);

    if ($result == 1) {
        return true;
    } else {
        return false;
    }
  }

}
