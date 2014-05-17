<?php
/**
 * Application level Controller
 *
 * This file is application-wide controller file. You can put all
 * application-wide controller-related methods here.
 *
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Controller
 * @since         CakePHP(tm) v 0.2.9
 */

App::uses('Controller', 'Controller');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package             app.Controller
 * @link                http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class ApiController extends Controller {

  public $uses = array('Question', 'Choice', 'Answer');

  public static $USER_ID = 1; // For Test
  
  public function start() {
    
    $json = $this->_getNextQuestion();
    $json["carte_id"] = hash("sha256", uniqid(uniqid()));
    
    $this->_out($json);
  }

  public function answer() {
    $params = $this->request->params;

    // save
    $answer = array(
      "user_id" => $this->USER_ID,
      "choice_id" => $params["choice_id"],
      "carte_id" => $params["carte_id"],
    );
    $this->Answer->create();
    $this->Answer->save($answer);
    
    // next question
    $json = $this->_getNextQuestion();
    $json["carte_id"] = $params["carte_id"]; // 引き継ぐ
    
  }

  private function _getNextQuestion() {
    $json = array();

    $json["info"] = array(
      "state" => "question",
    );
    $json["image"] = $this->_chooseImg();

    $question_id = 1;
    $question = $this->Question->findById($question_id);
    $json["question"] = $question["Question"];
    $json["question"]["choices"] = $question["Choices"];

    return $json;
  }

  private function _chooseImg() {

    $imgs = array(
      "/img/joi_angry.png"
    );

    $index = 0;
    return $imgs[$index];
  }       

  private function _out($json) {
    foreach ($json as $key => $value) {
      $this->set($key, $value);
    }
    $this->viewClass = 'Json';
    $this->set('_serialize', array_keys($json));
  }
}
