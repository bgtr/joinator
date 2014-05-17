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

  public $uses = array('Question', 'Choice', 'User', 'UserAnswer', 'UserStyle', 'Suggest', 'UserSuggest');

  public $user_id = 1; // For Test
  
  public function start() {
    
    $json = $this->_getNextQuestion();
    $json["karte_id"] = hash("sha256", uniqid(uniqid()));
    
    $this->_out($json);
  }

  public function answer() {
    $params = $this->request->query;

    // Save 
    $this->_saveAnswer($params);
    
    // next question
    $json = $this->_getNextQuestion($params);
    $json["karte_id"] = $params["karte_id"]; // 引き継ぐ
    $this->_out($json);
  }

  private function _getNextQuestion($params = null) {
    $json = array();

    $json["info"] = array(
      "state" => "question",
    );
    $json["image"] = $this->_chooseImg();

    if ($params["index"] < 10) {
    //if (false) {

      $question = $this->_chooseQuestion();
      $json["question"] = $question["Question"];
      $json["question"]["choices"] = $question["Choices"];

    } else {

      $json["result"] = $this->_chooseResult();
      $json["info"]["state"] = "result";

    }

    return $json;
  }

  private function _chooseQuestion() {
    $user_answers = $this->UserAnswer->find('all', array(
					"fields" => array("UserAnswer.question_id"),
					"order" => array("UserAnswer.updated_time DESC"),
					"limit" => 10,
					));
    $not_question_ids = array();
    foreach ($user_answers as $value) {
      $not_question_ids[] = $value["UserAnswer"]["question_id"]; 
    }
    $limit = 3;
    $questions = $this->Question->find("all", array(
					"conditions" => array(
                                          "NOT" => array("Question.id" => $not_question_ids)
                                        ),
					"limit" => $limit,
                                    ));
    if ($questions != null) {
      $question = $questions[rand(0, count($questions) - 1)];
    } else {
      $questions = $this->Question->find("all");
      $question = $questions[rand(0, count($questions) - 1)];
    }

    return $question;
  }

  private function _chooseImg() {

    $imgs = array(
      "/img/joi_angry.png"
    );

    $index = 0;
    return $imgs[$index];
  }       

  private function _chooseResult() {
    $user_styles = $this->UserStyle->find('all', array(
					"conditions" => array(
                                          "UserStyle.user_id" => $this->user_id
					)));

    $max = 0;
    $style_id = null;
    foreach ($user_styles as $user_style) {
       $num = $user_style["UserStyle"]["answer_num"];
       $score = $user_style["UserStyle"]["score"];
       $avg = ($num == 0) ? 0 : $score / $num;
       if ($max < $avg) {
         $max = $avg;
         $style_id = $user_style["UserStyle"]["style_id"];
       }
    }

    $suggests = $this->Suggest->find('all', array(
					"conditions" => array(
                                          "Suggest.style_id" => $style_id
					)));
    $suggest = $suggests[rand(0, count($suggests) - 1)];

    // save user suggest
    $this->_saveUserSuggest($suggest["Suggest"]["id"]); 

    $result = $suggest["Suggest"];

    return $result;
    
  }

  private function _saveUserSuggest($suggest_id) {
    $datasource = $this->UserSuggest->getDataSource();
    try {
      $datasource->begin();
    
      $this->Suggest->create();
      $data = array(
                 "user_id" => $this->user_id,
                 "suggest_id" => $suggest_id
               );
      $this->Suggest->save($data);

      $datasource->commit();

    } catch (Exception $e) {
      $datasource->rollback();
    }
  }

  private function _saveAnswer($params) {
    $datasource = $this->UserAnswer->getDataSource();
    
    try {
      $datasource->begin();

      // UserAnswer
      $choice = $this->Choice->findById($params["choice_id"]);
      $style_id = $choice["Question"]["style_id"];
      $user_style = $this->UserStyle->find("first", 
					array("conditions" => array(
						"UserStyle.user_id" => $this->user_id, 
						"UserStyle.style_id" => $style_id)));
      $user_style["UserStyle"]["answer_num"] += 1;
      $user_style["UserStyle"]["score"] += $choice["Choice"]["value"];
      $this->UserStyle->save($user_style["UserStyle"]);

      // Answer
      $answer = array(
        "user_id" => $this->user_id,
        "choice_id" => $params["choice_id"],
        "karte_id" => $params["karte_id"],
        "question_id" => $choice["Choice"]["question_id"],
      );
      $this->UserAnswer->save($answer);
  
      // User
      $user = $this->User->findById($this->user_id);
      $user["User"]["answer_num"] += 1;
      $this->User->save($user["User"]);

      $datasource->commit();

    } catch (Exception $e) {
      $datasource->rollback();
    }
  }

  private function _out($json) {
    foreach ($json as $key => $value) {
      $this->set($key, $value);
    }
    $this->viewClass = 'Json';
    $this->set('_serialize', array_keys($json));
  }
}
