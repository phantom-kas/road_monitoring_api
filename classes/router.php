<?php
class Router
{
  public $class;
  public $action;
  function __construct($class = null)
  {
    global $action;

    $this->action = $action;

    if ($class === null || !$class) {
      global $class;
    }
    if ($class !== false) {
      if (file_exists('./models/' . $class . '.php') and file_exists('./routes/' . $class . '_routes.php')) {
        $action;
        require_once './models/' . $class . '.php';
        require_once './routes/' . $class . '_routes.php';
      } 
      else {
        outPut(servError('Unkown request'));
        die();
      }
    }
  }
  public function checkClass()
  {
  }
}
