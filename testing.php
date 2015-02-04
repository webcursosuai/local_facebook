<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

global $DB, $USER;
$url = new moodle_url('/local/facebook/connect.php');

$context = context_system::instance();

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add('Add tester');
$password = optional_param('password', 'webcursos.2014', PARAM_TEXT);
echo $OUTPUT->header();
echo $OUTPUT->heading('Agregar usuario al testing');


if($password!='webcursos.2014'){

        print_error("No tienes acceso a este contenido");
}
echo'<center>

<form name="desenlazar" action="testing.php" method="post">
Username: <input type="text" name="username"><br>
Contraseña: <input type="password" name="password"><br>
<input name="submit" type="submit" value="Agregar usuario">

</form>

';
if(isset($_REQUEST['submit'])){

        $exist=$DB->get_record('user',array('username'=> $_REQUEST['username']));
        if($exist!=false){
                $repeat=$DB->get_record('facebook_testing',array('username'=>$_REQUEST['username']));
                if($repeat==null){
                        $record = new stdClass();
                        $record->username         = $_REQUEST['username'];
                        $record->timecreated = time();
                        $DB->insert_record('facebook_testing', $record);

                }
                else{
                        echo '<p style="color:red">El usuario ya esta ingresado en la lista.</p>';
                                
                }
        }

        else{

                echo '<p style="color:red">El usuario ingresado no existe.</p>';
        }
}








echo'<br><br><h3>Usuarios ya ingresados</h3></center>';
$testers=$DB->get_records('facebook_testing');
$table = new html_table();
$table->data[]= array('Id', 'Usuario','Nombre','Fecha de ingreso','Enlazado','ID de facebook','Fecha de enlace');
foreach($testers as $test){
        $user=$DB->get_record('user',array('username'=>$test->username));
        $enlace=$DB->get_record('facebook_user',array('moodleid'=>$user->id,'status'=>1));
        $bool="No";
        $fecha_enlace="";
		$facebookid="";
        if(isset($enlace) && $enlace!=false){
        	$bool="Si";
        	$fecha_enlace=date("d/m/y H:i",$enlace->timemodified);
        	$facebookid=$enlace->facebookid;
        }
        $table->data[]= array($test->id, $test->username,$user->firstname.' '.$user->lastname,date("d/m/y H:i",$test->timecreated),$bool,$facebookid,$fecha_enlace);
}
echo html_writer::table($table);
echo $OUTPUT->footer();

