<!DOCTYPE unspecified PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" href="style.css">
<script src="js/jquery.js"></script>
<script src="js/java.js"></script>

	<script>
			$(document).ready(function(){							
				$(window).resize(function(){
					$("#container1").width($(document).width()-10);
					$("#cuerpo").width($(document).width()-10);
					
					}
				);
				$("#container1").width($(document).width()-10);
				$("#cuerpo").width($(document).width()-10);
				$("#wrapper,#lateral,#cuerpo").height($(document).height()-100);
				
			});
		</script>
		<div id="fb-root"></div>
<script>

	(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/es_LA/all.js#xfbml=1&appId=559078344137958";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * 
 *
 * @package    local
 * @subpackage facebook
 * @copyright  2013 Francisco García Ralph (francisco.garcia.ralph@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

global $DB, $USER, $CFG;
require_once 'config.php';



$facebook = new Facebook($config);
$facebook_id= $facebook->getUser();

$app_name= $CFG->fbkAppNAME;
$app_email= $CFG->fbkemail;
$tutorial_name=$CFG->fbktutorialsN;
$tutorial_link=$CFG->fbktutorialsL;
$messageurl= new moodle_url('/message/edit.php');
$connecturl= new moodle_url('/local/facebook/connect.php');
?>

<div id="wrapper">
	<div id="container1" class="clearfix">
		<br>
		
		<div class="lateral">
			<br>
			
			<div class="box">
				<div class=titulo>
				<img src="images/logo.png" height="25" width="20" align="left">WebCursos UAI
				</div>
				<div class="linea">
				</div>
				<div class="links">
					<table width="100%" height="100%" cellspacing="5px">		
					<tr>
						<td>
							<img src="images/lista.png">
						</td>
						<td>
							<a href="<?php echo $messageurl; ?>" target=”_blank”><?php echo get_string('notificationsettings', 'local_facebook');?></a>
						</td>
					</tr>	
					<tr>
						<td>
							<img src="images/lista.png">
						</td>
						<td>
							<a href="<?php echo $connecturl; ?>" target=”_blank”><?php echo get_string('connectheading', 'local_facebook'); ?></a>
						</td>
					</tr>	
					</table>
				</div>
			</div>
			<div class="box">
				<div class=titulo>
				<img src="images/logo.png" height="25" width="20" align="left"><?php echo get_string('help', 'local_facebook');?>
				</div>
				<div class="linea">
				</div>
				<div class="links">
					<table width="100%" height="100%" cellspacing="5px">		
					<tr>
						<td>
							<img src="images/lista.png">
						</td>
						<td>
							<a href="<?php echo $tutorial_link; ?>" target=”_blank” ><?php echo $tutorial_name; ?></a>
						</td>
					</tr>	
					<tr>
						<td>
							<img src="images/lista.png">
						</td>
						<td>
							<?php echo $app_email;?>
						</td>
					</tr>	
					</table>
				</div>
			</div>
		<br><br>
		<center><div class="fb-like" data-href="http://apps.facebook.com/webcursosuai" 
				data-width="175"  data-layout="box_count" data-show-faces="false" data-send="false"></div><center>
		
		</div>
		<!-- FIN BARRA LATERAL !-->
		<?php 
$user_facebook_info=$DB->get_record('facebook_user',array('facebookid'=> $facebook_id,'status'=>1));




if($user_facebook_info!=false){
$moodle_id=$user_facebook_info->moodleid;
$lastvisit=$user_facebook_info->lasttimechecked;
$user_info=$DB->get_record('user',array('id'=>$moodle_id));
$user_course = enrol_get_users_courses($moodle_id);
 



 echo'
<div class="cuerpo">
<h1>'.get_string('courses', 'local_facebook').'</h1>

 <ul id="cursos">';

 

foreach($user_course as $courses){
	

	
	$fullname=$courses->fullname;
	$courseid=$courses->id;
	$shortname=$courses->shortname;
	

	$params = array(1,1,$courseid,$lastvisit);
	// cuenta todos los recursos desde la ultima vez que se conecto a la app.
	$sql = "SELECT count(*) FROM {course_modules} as cm
	inner join {modules} as m on (cm.module = m.id)
	where m.name in ('label','resource') and cm.visible = ? and m.visible = ? and course = ? and added > ?";

	$totalresource = $DB->count_records_sql($sql, $params);
	// cuenta todos los links desde la ultima vez que se conecto a la app.
	$sql = "SELECT count(*) FROM {course_modules} as cm
	inner join {modules} as m on (cm.module = m.id)
	where m.name in ('label','url') and cm.visible = ? and m.visible = ? and course = ? and added > ?";
	
	$totalurl = $DB->count_records_sql($sql, $params);
	// cuenta todos los post desde la ultima vez que se conecto a la app.
	$sql = 'SELECT count(*) FROM {forum_posts} AS posts INNER JOIN {forum_discussions} AS discussions ON (posts.discussion=discussions.id) WHERE discussions.course = ? AND posts.modified > ? ';
	$params = array($courseid,$lastvisit);
	$totalpost = $DB->count_records_sql($sql, $params);
	
	
	$total = $totalpost+$totalresource+$totalurl;
	
echo'<a class="inline link_curso" href="#'.$courseid.'"><li class="curso">
<p class="nombre"><img src="images/lista_curso.png"> '.$fullname.'</p>';

if($total > 0){
echo'<span class="numero_notificaciones">'.$total.'</span>
';
}


$sql='SELECT posts.id, posts.modified, posts.userid, posts.subject, discussions.id AS dis_id 
		FROM {forum_posts} AS posts 
		INNER JOIN {forum_discussions} AS discussions ON (posts.discussion=discussions.id)
		INNER JOIN {forum} as forum on (forum.id=discussions.forum)
		INNER JOIN {course_modules} as cm on (cm.instance=forum.id)
		WHERE discussions.course = ? AND cm.visible= ? 
		';
$params = array($courseid,1);
$data_post=$DB->get_records_sql($sql, $params);
$data_link=$DB->get_records('url', array('course'=>$courseid));
$data_resource=$DB->get_records('resource', array('course'=>$courseid));
$data_array= array();
foreach ($data_post as $post){
	$user=$DB->get_record('user',array('id'=>$post->userid));
	$posturl= new moodle_url('/mod/forum/discuss.php',array('d'=>$post->dis_id));
	$data_array[]=array('dibujo'=>1,'link'=>$posturl , 'title'=>$post->subject, 'from'=>$user->firstname.' '.$user->lastname, 'date'=>$post->modified);

}
foreach ($data_resource as $resource){

	$cm = get_coursemodule_from_instance('resource', $resource->id, $resource->course, false, MUST_EXIST);
	
	$date=date("d/m H:i",$resource->timemodified );
	$resourceurl= new moodle_url('/mod/resource/view.php',array('id'=>$cm->id));
	if($cm->visible==1 && $cm->visibleold==1){
	$data_array[]=array('dibujo'=>2,'link'=>$resourceurl,'title'=>$resource->name,'from'=>'','date'=>$resource->timemodified);
}

}

foreach ($data_link as $link){
	$date=date("d/m H:i",$link->timemodified );

	$cm = get_coursemodule_from_instance('url', $link->id, $link->course, false, MUST_EXIST);
	if($cm->visible==1 && $cm->visibleold==1){
	$data_array[]=array('dibujo'=>3,'link'=>$link->externalurl,'title'=>$link->name, 'from'=>'','date'=>$link->timemodified);
}
}

$data_array = record_sort($data_array, 'date', 'true');


?>
			</li></a>
<div class="popup_curso" id="<?php echo $courseid ?>">
<a href="#" class="close"></a>
<div class="contenido_popup">
<?php echo get_string('tabletittle', 'local_facebook').$fullname; ?><br>
<table class="tablesorter" border="0" width="100%"  style="font-size:13px" >
<thead>
<tr>
<th width="8%"></th>
<th width="52%"><?php echo get_string('rowtittle', 'local_facebook'); ?></th>
<th width="20%"><?php echo get_string('rowfrom', 'local_facebook'); ?></th>
<th width="20%"><?php echo get_string('rowdate', 'local_facebook'); ?></th>

</tr>
</thead>
<tbody>
<?php	

foreach ($data_array as $data){
	$date=date("d/m/Y H:i",$data['date'] );
	echo'	<tr>
		
<td><center>';
	if($data['dibujo']==1){

		echo'<img src="images/post.png">';
	}
	elseif($data['dibujo']==2){

		echo '<img src="images/resource.png">';
	}
	else{

		echo'<img src="images/link.png">';

	}


	echo'</center></td>

<td><a href="'.$data['link'].'" target=”_blank”>'.$data['title'].'</a></td>
<td style="font-size:11px"><b>'.$data['from'].'</b></td>

		<td>'.$date.'</td>

</tr>
';
}
echo '</tbody> 
</table> 


 </div>
  </div>';


}

?>

 </ul>

 </tbody>
		</div>
	</div>
	<div id="separador">
	<br>
	</div>
	<div id="container2">
<table width="100%">
 
 <tr>
 <td align="left"><img  src="images/logo_webcursos_abajo.png"> </td>
 <td align="right"><img  src="images/logo_abajo.png"></td>
  </tr>
 </table>
	</div>
</div>
<div id="overlay"></div>
<?php
$user_facebook_info->lasttimechecked=time();
$DB->update_record('facebook_user', $user_facebook_info);
}else{


	echo'


<div class="cuerpo">
		 <h1>'.get_string('existtittle', 'local_facebook').'</h1>
		<p>'.get_string('existtext', 'local_facebook').'<a href="'.$connecturl.'" >'.get_string('existlink', 'local_facebook').'</a></p>
		</div>
						
		';


 ?>
<div id="separador">
	<br>
	</div>
	<div id="container2">
<table width="100%">
 
 <tr>
 <td align="left"><img  src="images/logo_webcursos_abajo.png"> </td>
 <td align="right"><img  src="images/logo_abajo.png"></td>
  </tr>
 </table>
	</div>
</div>

 
 <?php 
   }
 
 function record_sort($records, $field, $reverse=false)
 {
 	$hash = array();
 	foreach($records as $record)
 	{
 		$hash[$record[$field]] = $record;
 	}
 	($reverse)? krsort($hash) : ksort($hash);
 	$records = array();
 	foreach($hash as $record)
 	{
 		$records []= $record;
 	}
 	return $records;
 }
