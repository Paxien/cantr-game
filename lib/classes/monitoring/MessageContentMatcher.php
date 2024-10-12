<?php

class MessageContentMatcher
{

  private $exactPatterns = [];
  private $regexPatterns = [];

  private function __construct()
  {
  }

  public static function newInstance()
  {
    return new self();
  }

  public function patternMatches($message)
  {
    foreach ($this->exactPatterns as $pattern) {
      if (stripos($message, $pattern) !== false) {
        return true;
      }
    }

    foreach ($this->regexPatterns as $pattern) {
      if (preg_match($pattern, $message) === 1) {
        return true;
      }
    }

    return false;
  }

  public function withBasicPatterns()
  {
    $this->exactPatterns = array_merge($this->exactPatterns, [
      "ooc", "ooc:", "ooc%", "occ%", "occ ", "occ:", "0oc%",
      "0oc ", "0oc:", "o0c%", "o0c ", "o0c:", "00c%", "00c ", "00c:",
      "character", "player", "roleplay"]); // english
    $this->exactPatterns = array_merge($this->exactPatterns, [
      "ocece%", "ocece ", "ocece:", "oecece%", "oecece ", "oecece:",
      "oese%", "oese ", "oese:", "oesese%", "oesese ", "oesese:",
      "o.c.c%", "o.c.c ", "o.c.c:", "o c c%", "o c c ", "o c c:",
      "personaje", "jugador"]); // spanish
    $this->exactPatterns = array_merge($this->exactPatterns, [
      "personagem", "jogador"]);// portuguese

    $this->regexPatterns = array_merge($this->regexPatterns, [
      "/\bpd\b/i", "/\bgab\b/i", "/\bcr\b/i", "/\bcrb\b/i", // english
      "/\bnz\b/i", "/\bnnz\b/i"]); // polish
    return $this;
  }

  public function withBracketPatterns()
  {
    $this->exactPatterns = array_merge($this->regexPatterns, ["(", ")", "{", "}", "[", "]", "<", ">"]);
    return $this;
  }
}
