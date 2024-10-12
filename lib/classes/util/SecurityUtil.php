<?php

class SecurityUtil
{
  public static function generatePasswordHash($password)
  {
    return password_hash($password, PASSWORD_DEFAULT, ["cost" => 10]);
  }

  public static function verifyPassword($password, $hash)
  {
    return password_verify($password, $hash);
  }

  public static function generateOldPasswordHash($in)
  {
    $temp = md5($in . "Cantr.net&5000leaguesUnd@r3s3^");
    $t1 = substr($temp, 0, 10);//first 10 chars (0 to 9, 10 total)
    $t2 = substr($temp, 10, 22);//last 22 chars (10 to 32)
    $final = substr(md5($t1), 0, 10) . $t2;
    return $final;
  }

  /**
   * @return string
   */
  public static function generateNewRandomPassword()
  {
    $possibleChars = '1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
    $newPassword = '';
    for ($i = 0; $i < 10; $i++) {
      $randomCharIndex = mt_rand(0, strlen($possibleChars) - 1);
      $newPassword .= $possibleChars[$randomCharIndex];
    }
    return $newPassword;
  }

  /**
   * @param $forgottenPlayer
   * @return string
   */
  public static function createPlayerConfirmationHash(Player $forgottenPlayer)
  {
    // it can look ugly, but let's try do something harder to guess
    return substr(md5($forgottenPlayer->getPasswordHash() . $forgottenPlayer->getId() . "random1da231"), 0, 10);
  }

}