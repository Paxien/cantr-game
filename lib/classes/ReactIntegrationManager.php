<?php

class ReactIntegrationManager
{

  public function getReactJsFiles()
  {
    if (!is_dir("react/js")) {
      return [];
    }
    return array_filter(scandir("react/js"), function($file) {
      return StringUtil::endsWith($file, ".js");
    });
  }

  public function getReactCssFiles()
  {
    if (!is_dir("react/css")) {
      return [];
    }
    return array_filter(scandir("react/css"), function($file) {
      return StringUtil::endsWith($file, ".css");
    });
  }

  public function getTagsNeededByReact()
  {
    return [
      "create_new_character",
      "player_nav_chat",
      "player_nav_forum",
      "player_nav_wiki",
      "player_nav_contact",
      "player_nav_settings",
      "player_nav_logout",
      "intro_wiki_language_link",
    ];
  }
}
