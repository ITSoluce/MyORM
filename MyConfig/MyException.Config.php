<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$MyExceptionLogError = 1; // 0 no log on php_errors.log / 1 log
$MyExceptionScreenError = 1; // 0 no log on screen / 1 log
$MyExceptionMailError = 0; // 0 no email on error / 1 email
$MyExceptionMailFatalError = 0; // 0 no email on fatal or custom error / 1 email
$MyExceptionEmail = "*"; // the email for error / 1 email
$MyExceptionProjectName = "MySQL Class"; // Project name to personalize ur error, if u are using this lib on several programs.

/*
 * For PHPMailMailer / trying mail() si nothing
 */

$MyExceptionPHPMailer = 0; // enable PHPMailer
$MyExceptionPHPMailSMTPAuth   = true; // enable SMTP authentication
$MyExceptionPHPMailSMTPSecure = "tls"; // sets the prefix to the servier
$MyExceptionPHPMailHost = "*"; // sets SMTP server mail.smtp2go.com
$MyExceptionPHPMailPort = 2525; // set the SMTP port
$MyExceptionPHPMailUsername = "*";  // username
$MyExceptionPHPMailPassword = "*"; // password