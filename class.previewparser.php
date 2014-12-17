<?php
class ytp_previewParserObject {
  public $id;
  public $uriDef;
  
  public function __construct($uriRaw, $uriDef) {
    $this->uriDef = $uriDef;
    $this->id = $this->getId($uriRaw);
  }

  public function getSmallImg() {
    $templates = array(
        "http://img.youtube.com/vi/%s/default.jpg",
        "http://img.youtube.com/vi/%s/1.jpg",
        "http://img.youtube.com/vi/%s/2.jpg",
        "http://img.youtube.com/vi/%s/3.jpg",
    );
    $c = '';
    foreach ($templates as $template) {
      $src = sprintf($template, $this->id);
      $headers = get_headers($src);
      $exist = strpos($headers[0], "200");
      if ($exist !== false) {
        $c .= sprintf(
                '<a target="_blank" href="%1$s" class="nofancybox preparser-img-wr" download>'
                . '<img class="preparser-img" src="%1$s">'
                . '</a> ', 
                $src);
      }
    }
    return $c;
  }
  public function getMediumImg() {
    $templates = array(
        "http://img.youtube.com/vi/%s/0.jpg",
        "http://img.youtube.com/vi/%s/hqdefault.jpg"
    );
    $c = '';
    foreach ($templates as $template) {
      $src = sprintf($template, $this->id);
      $headers = get_headers($src);
      $exist = strpos($headers[0], "200");
      if ($exist !== false) {
        $c .= sprintf(
                '<div class="pp-col w6-12 preparser-result-medium-img">'
                . '<a target="_blank" href="%1$s" download="%1$s" class="nofancybox preparser-img-wr">'
                . '<img class="preparser-img" src="%1$s">'
                . '</a>'
                . '</div>', $src);
      }
    }
    return $c;
  }
  
  public function getFullImg() {
    $templates = array(
        "http://img.youtube.com/vi/%s/maxresdefault.jpg"
    );
    $c = '';
    foreach ($templates as $template) {
      $src = sprintf($template, $this->id);
      $headers = get_headers($src);
      $exist = strpos($headers[0], "200");
      if ($exist !== false) {
        $c .= sprintf(
                '<div class="pp-col w12-12 preparser-result-full-img">'
                . '<a target="_blank" href="%1$s" download="%1$s" class="nofancybox preparser-img-wr">'
                . '<img class="preparser-img" src="%1$s">'
                . '</a>'
                . '</div>', 
                $src);
      }
    }
    return $c;
  }
  public function getIframe() {
    return sprintf(
            '<iframe width="640" height="360" src="//www.youtube.com/embed/%s" frameborder="0" allowfullscreen></iframe>', 
            $this->id);
  }

  public function getId($uriRaw = false) {
    $uri = htmlspecialchars($uriRaw);
    $parsedUri = parse_url($uri);

    if ($parsedUri["host"] === "youtu.be") {
      $id = trim($parsedUri["path"], "/");
    } else {
      parse_str($parsedUri["query"], $query);
      $id = $query["v"];
    }
    if (empty($id)) {
      $id = $this->uriDef;
    }
    return $id;
  }

}