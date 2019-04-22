<?php
require('twentyi_rest_api.class.php');

class twentyi extends VacationDriver {
  public function init() {
    $rcmail = rcmail::get_instance();
    $token  = $rcmail->config->get('password_20i_token');
    $this->rest = new twentyi_rest_api($token);
  }
  public function _get() {
    $email = explode('@', rcube::Q($this->user->data['username']));
    $local  = $email[0];
    $domain_name = $email[1];
    $domain = $this->rest->getWithFields("https://api.20i.com/package/" . $domain_name . "/email/" . $domain_name);
    foreach ($domain->responder as $responder) {
      if ($responder->local == $local && (substr($responder->id, 0, 1) === 'r')) {
        if ($responder->forwardTo == null) {
          $responder->forwardTo = "";
        }
        return array(
          "subject"=>$responder->subject,
          "body"=>$responder->content,
          "forward"=>$responder->forwardTo,
          "enabled"=>$responder->enabled
        );
      }
      continue;
    }
    return array("subject"=>"","body"=>"","forward"=>"","enabled"=>false);
  }
  public function setVacation() {
    $email = explode('@', rcube::Q($this->user->data['username']));
    $local  = $email[0];
    $domain_name = $email[1];
    $vacArr = array("subject"=>"","aliases"=>"", "body"=>"","forward"=>"","keepcopy"=>true,"enabled"=>false);
    $domain = $this->rest->getWithFields("https://api.20i.com/package/" . $domain_name . "/email/" . $domain_name);
    if ($this->forward == "") {
      $this->forward = null;
    }
    foreach ($domain->responder as $responder) {
      if ($responder->local == $local && (substr($responder->id, 0, 1) === 'r')) {
        rcube::console("Found ". $local ." as id=". $responder->id);
        $data = [
          "update" => [
            $responder->id => [
              "subject"=>$this->subject,
              "endTime"=>null,
              "startTime"=>null,
              "content"=>$this->body,
              "forwardTo"=>$this->forward,
              "enabled"=>$this->enable,
              "type"=>"text/html"
            ]
          ]
        ];
        rcube::console("Done ". $local ." as id=". $responder->id);
        rcube::console("Posted ". print_r($responder, true));
        return $this->rest->postWithFields("https://api.20i.com/package/" . $domain_name . "/email/" . $domain_name, $data);
      }
      continue;
    }
    $data = [
      "new" => [
        "responder" => [
          "subject"=>$this->subject,
          "local"=>$local,
          "endTime"=>null,
          "startTime"=>null,
          "content"=>$this->body,
          "forwardTo"=>$this->forward,
          "enabled"=>$this->enabled,
          "type"=>"text/html"
        ]
      ]
    ];
    return $this->rest->postWithFields("https://api.20i.com/package/" . $domain_name . "/email/" . $domain_name, $data);
  }
}
