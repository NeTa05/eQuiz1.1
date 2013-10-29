<?php   
    /*here I include the file that has the class Db*/
    require_once 'Db.php';
    /*here I include the config.php*/
    require_once 'Conf.php';
    /* include the classes to send the email */
    include_once("class.phpmailer.php");
    include_once("class.smtp.php");
    
    $string = file_get_contents("c:\\Json\\config.json");;//get the file
    //$string = file_get_contents($argv[1]);//get the file
    $dataJson = json_decode($string, true);//get the variables of config.json
    $host=$dataJson[0]["database_host"];//get host
    $user=$dataJson[0]["database_user"];//get user
    $password=$dataJson[0]["database_pass"];//get password
    $db=$dataJson[0]["database_name"];//get db
    $size=(int)$dataJson[0]["email_batch_limit"];//get db
    
    //checking all the variables
    if(!empty($host) && !empty($user) && !empty($db))
    { 
        //setting the name of the variables to connect the db, for getting it in the construct of Conf.php
        $_SESSION['host']=$host;
        $_SESSION['user']=$user;
        $_SESSION['pass']=$password;
        $_SESSION['name']=$db;
    }
    else
    {
        exit("Did not get the variables for connection");//if I didnt get the variables to connect the db, it is going to finish
    }
    
    /*create the instance for connecting database*/
    $bd= Db::getInstance();
    
    //TEST
    $sql='select * from test where application_date < NOW() and status=1';//select of tests before now and with status=1
    $selectTest=$bd->ejecutar($sql);//to execute sql
    $countSent=0;//it is counting the number of test when it is sending
    
    //loop for checking all the test
    while ($testTable=$bd->obtener_fila($selectTest,0))
    {
        //ROWS TEST
        $idTest=$testTable['id'];//column id from test
        $groupInfoId=$testTable['groupInfo_id'];//column groupinfo_id from test
        $applicationDate=$testTable['application_date'];//column application_date from test
        $description=$testTable['description'];//column description from test
        $termInMinutes=$testTable['term_in_minutes'];//column term_in_minutes from test
        //GROUPINFO
        $sql='select course_id,professor_id FROM groupinfo where id="'.$groupInfoId.'" ';//select from groupinfo for getting course_id and professor_id
        $selectGroupInfo=$bd->ejecutar($sql);//to execute select
        $groupInfoTable=$bd->obtener_fila($selectGroupInfo,0);//getting rows
        //ROWS GROUPINFO
        $courseId=$groupInfoTable['course_id'];//column course_id from groupinfo
        $professorId=$groupInfoTable['professor_id'];//column professor_id from groupinfo
        //PROFESSOR
        $sql='select first_name, last_name FROM professor where id="'.$professorId.'" ';//select from professor for getting first_name and last_name
        $selectProfessor=$bd->ejecutar($sql);//to execute select
        $professorTable=$bd->obtener_fila($selectProfessor,0);//getting rows
        //ROWS PROFESSOR
        $firstNameProfessor=$professorTable['first_name'];//column first_name from professor
        $lastNameProfessor=$professorTable['last_name'];//column last_name from professor
        //COURSE
        $sql='select name FROM course where id="'.$courseId.'" ';//select from course with the value of one id
        $selectCourse=$bd->ejecutar($sql);//to execute select
        $courseTable=$bd->obtener_fila($selectCourse,0);//getting rows
        //ROWS COURSE
        $nameCourse=$courseTable['name'];//column name from course
        
        //STUDENT
        $sql='select * from student';//select all the student 
        $selectStudent=$bd->ejecutar($sql);//to execute select
        
        //loop for checking all the students
        while ($studentTable=$bd->obtener_fila($selectStudent,0))
        {
            $idStudent=$studentTable['id'];//column id from student
            
            $sql='select * FROM registration where groupInfo_id="'.$groupInfoId.'" 
                and student_id="'.$idStudent .'" ';//select groupinfo and student to know if this student is in registration table
            $selectRegistration=$bd->ejecutar($sql);//to execute select 
            $registrationTable=$bd->obtener_fila($selectRegistration,0);//getting rows
            //ROWS REGISTRATION
            $idRegistration=$registrationTable['id'];//column id registration from registration
            
            //if I found the id, so I am going to send the email
            if(!empty($idRegistration))
            {
                
                //select to check if this student has this test 
                $sql='select * FROM notification_sent where student_id="'.$idStudent.'" and test_id="'.$idTest.'" '; 
                $selectNotification=$bd->ejecutar($sql);//to execute select 
                $sent=false;//set the variable in false for supposing that this test was not sending
                
                while($NotificationTable=$bd->obtener_fila($selectNotification,0))//getting row
                {
                    $sent=true;//change the state(the student have the test)
                    
                }
                //if the student didnt have the test
                if(!$sent)
                {
                    $firsNameStudent=$studentTable['first_name'];//column first_name from student
                    $lastNameStudent=$studentTable['last_name'];//column last_name from student
                    $to=$studentTable['email'];//column email from student, the address to send the email

                    $style="text-align:center";//style for the header
                    $header='<h1 style="'.$style.'"> <strong>UTN San Carlos </strong> </h1> '.
                        '<h2 style="'.$style.'"><strong>Equiz 1.1 </strong>  </h2> '; //header of email
                    //information of the email (body with the main information)
                    $body=" Hola ".$firsNameStudent." ".$lastNameStudent.". <br> <br>".
                          "El quiz ".$description." del curso ".$nameCourse." ha sido activado a partir de<br>". 
                          $applicationDate." y por un lapso de ".$termInMinutes." minutos.<br><br>".
                          "Seguir el siguiente link para ingresar al sistema automatizado de quices de la UTN";

                    $link="http://localhost/test/".$idTest;//link with the id of the test
                    $foot="<strong>Profesor ".$firstNameProfessor. " ".$lastNameProfessor.".</strong>";//professor name
                    //join all the previous variables in the html
                    $html='<!DOCTYPE HTML>
                            <html>
                            <head>
                                    <title></title>
                            </head>
                            <body>'.$header.' <p>'.$body.

                            '</p><br> <a href="'.$link.'" >Link</a> <br><br>'.$foot.' </body>
                            </html>';

                    //echo $body;
                    $mail = new PHPMailer(); /*create the instance to send the email*/
                    $mail->IsHTML(True);//the mail is html
                    $mail->IsSMTP(); // SMTP protocol
                    $mail->SMTPAuth = true; //SMTP autentication 
                    $mail->SMTPSecure = "ssl"; // SSL security socket layer
                    $mail->Host =$dataJson[0]["email_smtp_host"];//getting the host of smtp
                    $mail->Port = $dataJson[0]["email_smtp_port"];//getting the port of smtp
                    $mail->Username= $dataJson[0]["email_smtp_user"]; //getting the email
                    $mail->Password = $dataJson[0]["email_smtp_pass"]; //getting the password of the email
                    $mail->From = $dataJson[0]["email_from"]; // getting email from
                    $mail->FromName=$dataJson[0]["email_from_name"];// getting the email_from_name
                    $mail->AddAddress($to);//adding the email to send
                    $mail->Subject = "Quiz ".$nameCourse; // joing the word "quiz" with the name of course
                    $mail->WordWrap = 50; //number of rows in the email
                    $mail->MsgHTML($html); //sending the html
                    
                    //the email was sent
                    if ($mail->Send())
                    { 
                            $answer = "The email was sent";
                            $countSent++;//countSent + 1()
                    }
                    //the email wasn´t sent
                    else 
                    {
                            $answer = "Fail ";
                            $answer .= " Error: ".$mail->ErrorInfo;
                    }
                    //if the countSent is like size 
                    if($countSent==$size)
                    {
                        sleep(3);//sleep 3 second
                        $countSent=0;//setting the countSend in 0
                        
                    }//end if
                }//end if(!$sent)
            }//end if(!empty($idRegistration))
        }// end while ($studentTable=$bd->obtener_fila($selectStudent,0))
        
        //updating the status of every test
        $sql='UPDATE test SET status=0 WHERE id="'.$idTest.'" ';
        $bd->ejecutar($sql);//to execute sql
    }//end while ($testTable=$bd->obtener_fila($selectTest,0))
    
?> 