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

    // We got this far so lets process the command
    $this->performOperations($this->command);
  }

  /**
   * Get the error message
   * @return
   *   String Error text
   */
  function getError() {
    return $this->error;
  }

  /**
   * Use the submitted file else use the submitted command.
   *
   * @return String
   *   Return the commands to run else FALSE
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
   * Execute the commands
   *
   * @param  Array $commands Array of commands to execute
   *
   */
  public function performOperations($commands) {

    // If the parameter is still a string explode it to an array.
    if (is_string($commands)) {
      $commands = explode("\n", $commands);
    }

    // Loop over each of the commands and take action on them.
    foreach ($commands AS $command) {

      // Check to ensure the command is a valid one.
      if($this->isValidOperation($command)) {

        // Ensure no extra space before or after the command.
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
          // We must have place.
          // Remove place from the string.
          $command = substr($command,6);

          // Get the values for the initial position of the robot
          list($x, $y, $direction) = explode(',', $command);

          $this->setPosition($x,$y,$direction);
          break;
        }
      }
    }
 }

  /** Function to set the position of the robot.
   *
   * @param integer $x
   *   Set the X coordinate.
   * @param integer $y
   *   Set the Y coordinate.
   * @param string $direction
   *   Direction the robot is facing
   */
  private function setPosition($x = 0, $y = 0, $direction = 'North') {
    $this->position = array($x, $y, $direction);
  }

  /**
   * Get the current position of the robot.
   */
  private function getPosition() {
    return $this->position;
  }

  /**
   * Get Formatted Position
   * Used on the frontend display
   */
  public function getFormattedPosition() {
    return implode(',',$this->getPosition());
  }

  /**
   * Perform a turn in direction. Eg LEFT or RIGHT
   *
   * @param String $direction
   *   The direction the robot will turn. Eg LEFT or RIGHT
   */
  private function doTurn($direction) {
    $position          = $this->getPosition();

    $current_direction = $position[2];
    $x                 = $position[0];
    $y                 = $position[1];

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
   * Move the robot forward.
   */
  private function doMove() {
    $position = $this->getPosition();

    if ($this->checkMoveIsValid()) {
      // print '<div class="alert alert-info">Move permitted</div>';
      $direction = $position[2];
      $x         = $position[0];
      $y         = $position[1];

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
    $position  = $this->getPosition();

    $direction = $position[2];
    $x         = $position[0];
    $y         = $position[1];

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
   *
   * @param  integer $x
   *   Test the X coordinate exists.
   * @param  integer $y
   *   Test the Y coordinate exists.
   *
   * @return Boolean
   *   Returns TRUE or FALSE.
   */
  private function testPositionExists($x = 0, $y = 0) {

    if (isset($this->table[$x]) &&
        isset($this->table[$y])) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Validate the operation
   *
   * @param  String  $op
   *   The command sent by the user.
   *
   * @return boolean
   *    Returns TRUE or FALSE.
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
   * Validate command element.
   *
   * @param String $op
   *   Test if the command is MOVE.
   *
   * @return boolean
   *   Returns TRUE or FALSE.
   */
  public function isMove($op) {
    $regex = '/^MOVE/';

    return $this->regexCommand($regex,$op);
  }

  /**
   * Validate command element.
   *
   * @param String $op
   *   Test if the command is LEFT.
   *
   * @return boolean
   *   Returns TRUE or FALSE.
   */
  public function isLeft($op) {
    $regex = '/^LEFT/';

    return $this->regexCommand($regex,$op);
  }

  /**
   * Validate command element.
   *
   * @param String $op
   *   Test if the command is RIGHT.
   *
   * @return boolean
   *   Returns TRUE or FALSE.
   */
  public function isRight($op) {
    $regex = '/^RIGHT/';

    return $this->regexCommand($regex,$op);
  }

  /**
   * Validate command element.
   *
   * @param String $op
   *   Test if the command is PLACE x,y,DIRECTION.
   *
   * @return boolean
   *   Returns TRUE or FALSE.
   */
  public function isPlace($op) {
    $regex = '/^PLACE [0-4],[0-4],(?:NORTH|EAST|SOUTH|WEST)/';

    return $this->regexCommand($regex,$op);
  }

  /**
   * Validate command element.
   *
   * @param String $op
   *   Test if the command is REPORT.
   *
   * @return boolean
   *   Returns TRUE or FALSE.
   */
  public function isReport($op) {
    $regex = '/^REPORT/';

    return $this->regexCommand($regex,$op);
  }

  /**
   * Helper function to validate the commands so we do not need to duplicate code.
   *
   * @param  String $regex
   *   The regex pattern.
   * @param  String $op
   *   The command sent by the user.
   *
   * @return Boolean        [description]
   */
  public function regexCommand($regex, $op) {
    $result = preg_match($regex, $op);

    if ($result == 1) {
        return TRUE;
    } else {
        return FALSE;
    }
  }
}
