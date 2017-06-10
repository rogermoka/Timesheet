<?php
$to      = "Roger.Mokarzel@itron.com";
$subject = "the subject";
$message = "hello";
$headers = 'From: webmaster@example.com' . "\r\n" .
           'Reply-To: webmaster@example.com' . "\r\n" .
           'X-Mailer: PHP/' . phpversion();

if(mail("$to","$subject","$message","$headers"))
{
  echo "Success : Email sent.";
}
 else {
   echo "Error : Email not sent.";
      }
?>
