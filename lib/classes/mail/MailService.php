<?php

class MailService
{

  private $logger;

  /** @var bool */
  private $undeliveredEmailCheck = true;
  /** @var Db */
  private $db;

  public function __construct($senderName, $senderEmail = null, $replyEmail = null)
  {
    /** senderEmail is now ignored, as all emails are sent from the global emailSender in stddef.inc.php **/
    if (empty($replyEmail)) {
      $replyEmail = $GLOBALS['emailSupport'];
    }

    $this->senderName = $senderName;
    // $this->senderEmail = $senderEmail;
    # temporary fix #170 date 4/11/2020 by Joshuamonkey
    $this->senderEmail = $GLOBALS['emailSender'];
    $this->replyEmail = $replyEmail;

    $this->db = Db::get();
    $this->logger = Logger::getLogger(__CLASS__);
  }

  public function send($toEmail, $subject, $htmlText, $escapeNewlines = true)
  {
    $boundary = "nextPart" . mt_rand();

    if ($escapeNewlines) {
      $htmlText = nl2br($htmlText);
    }

    $converter = new Html2Text\Html2Text($htmlText, false);
    $plaintext = $converter->getText();

    $headers = $this->getBasicHeaders();
    $headers .= "Content-Type: multipart/alternative; boundary=$boundary\r\n";

    //text version
    $message = "\r\n\r\n--$boundary\r\n";
    $message .= "Content-Type: text/plain; charset=utf-8\r\n\r\n";
    $message .= $plaintext;

    //html version
    $message .= "\r\n\r\n--$boundary\r\n";
    $message .= "Content-Type: text/html; charset=utf-8\r\n\r\n";
    $message .= $htmlText;
    $message .= "\r\n\r\n--$boundary--\r\n\r\n";

    if (!$this->canSendEmail($toEmail)) {
      $this->logger->notice("Prevent sending email to '$toEmail' " .
        "with title '$subject', because it is listed as invalid email address to which emails can not be delivered");
      return false;
    }

    return mail($toEmail, $subject, $message, $headers);
  }

  public function sendPlaintext($toEmails, $subject, $plaintext)
  {
    $headers = $this->getBasicHeaders();
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";

    //text version
    $message = $plaintext;

    $emails = explode(",", $toEmails);
    $acceptedEmails = [];
    foreach ($emails as $email) {
      if ($this->canSendEmail($email)) {
        $acceptedEmails[] = $email;
      } else {
        $this->logger->notice("Prevent sending email to '$toEmails' " .
          "with title '$subject', because it is listed as invalid email address to which emails can not be delivered");
      }
    }
    if (empty($acceptedEmails)) {
      return false;
    }

    return mail(implode(",", $acceptedEmails), $subject, $message, $headers);
  }

  private function getBasicHeaders()
  {
    $headers = 'From: ' . $this->senderName . ' <' . $this->senderEmail . '>' . "\r\n";
    $headers .= 'Reply-To: ' . $this->replyEmail . "\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion() . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    return $headers;
  }

  private function canSendEmail($emailAddress)
  {
    if (!$this->undeliveredEmailCheck) {
      return true;
    }

    $stm = $this->db->prepare("SELECT `count` FROM `undelivered_emails` WHERE email = :email");
    $stm->bindStr("email", $emailAddress);
    $someEmailWasNotDelivered =$stm->executeScalar() > 2;
    if ($someEmailWasNotDelivered) {
      $stm = $this->db->prepare("SELECT COUNT(*)
        FROM assignments a
        INNER JOIN players p ON p.id = a.player WHERE p.email = :email");
      $stm->bindStr("email", $emailAddress);
      $isStaffMember = $stm->executeScalar();
      return $isStaffMember > 0;
    }
    return true;
  }

  public function setUndeliveredEmailCheck($undeliveredEmailCheck)
  {
    $this->undeliveredEmailCheck = boolval($undeliveredEmailCheck);
  }
}
