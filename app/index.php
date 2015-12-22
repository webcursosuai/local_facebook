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
 * @copyright  2015 Xiu-Fong Lin (xlin@alumnos.uai.cl)
 * @copyright  2015 Mihail Pozarski (mipozarski@alumnos.uai.cl)
 * @copyright  2015 Hans Jeria (hansjeria@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->dirroot.'/local/facebook/locallib.php');
global $DB, $USER, $CFG;
include "config.php";
use Facebook\FacebookResponse;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequire;
use Facebook\Facebook;
use Facebook\Request;
include "htmltoinclude/javascriptindex.html";


//gets all facebook information needed
$AppID= $CFG->fbkAppID;
$SecretID= $CFG->fbkScrID;
$config = array(
		'app_id' => $AppID,
		'app_secret' => $SecretID,
		"default_graph_version" => "v2.5"
);
$facebook = new Facebook($config);

echo $facebook->getDefaultAccessToken();

$helper = $facebook->getCanvasHelper();

try {
	$accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookSDKException $e) {
	// There was an error communicating with Graph
	// Or there was a problem validating the signed request
	echo $e->getMessage();
	exit;
}
echo "***".$accessToken;
if ($accessToken) {
	// Logged in.
	$_SESSION['facebook_access_token'] = (string) $accessToken;
	try {
		// Returns a `Facebook\FacebookResponse` object
		$response = $facebook->get('/me?fields=id,name',$accessToken);
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		echo 'Graph returned an error: ' . $e->getMessage();
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}
	
	$user = $response->getGraphUser();
	var_dump($user);
}


/*
$helper = $facebook->getCanvasHelper();
try {
	$accessToken = $helper->getAccessToken();
} catch(Facebook\Exceptions\FacebookSDKException $e) {
	// There was an error communicating with Graph
	// Or there was a problem validating the signed request
	echo $e->getMessage();
	exit;
}

$cilent = $facebook->getOAuth2Client();

try {
	// Returns a long-lived access token
	$accessToken = $cilent->getLongLivedAccessToken($accessToken);
} catch(Facebook\Exceptions\FacebookSDKException $e) {
	// There was an error communicating with Graph
	echo $e->getMessage();
	exit;
}

if ($accessToken) {
	echo 'Successfully logged in!';
}
try {
	// Returns a `Facebook\FacebookResponse` object
	$response = $facebook->get('/me?fields=id,name',$accessToken);
} catch(Facebook\Exceptions\FacebookResponseException $e) {
	echo 'Graph returned an error: ' . $e->getMessage();
	exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
	echo 'Facebook SDK returned an error: ' . $e->getMessage();
	exit;
}

$user = $response->getGraphUser();

echo 'Name: ' . $user['name'];
*/

$app_name= $CFG->fbkAppNAME;
$app_email= $CFG->fbkemail;
$tutorial_name=$CFG->fbktutorialsN;
$tutorial_link=$CFG->fbktutorialsL;
$messageurl= new moodle_url('/message/edit.php');
$connecturl= new moodle_url('/local/facebook/connect.php');

//gets the UAI left side bar of the app
include 'htmltoinclude/sidebar.html';
//search for the user facebook information

$userfacebookinfo = $DB->get_record('facebook_user',array('facebookid'=>$facebook_id,'status'=>1));

// if the user exist then show the app, if not tell him to connect his facebook account
if ($userfacebookinfo != false) {
	$moodleid = $userfacebookinfo->moodleid;
	$lastvisit = $userfacebookinfo->lasttimechecked;
	$user_info = $DB->get_record('user', array(
			'id'=>$moodleid
	));
	$usercourse = enrol_get_users_courses($moodleid);
	echo '<div class="cuerpo"><h1>'.get_string('courses', 'local_facebook').'</h1><ul id="cursos">';
	
	//generates an array with all the users courses
	$courseidarray = array();
	foreach ($usercourse as $courses){
		$courseidarray[] = $courses->id;
	}
	
	// get_in_or_equal used after in the IN ('') clause of multiple querys
	list($sqlin, $param) = $DB->get_in_or_equal($courseidarray);
	
	// list the 3 arrays returned from the funtion
	list($totalresource, $totalurl, $totalpost) = get_total_notification($sqlin, $param, $lastvisit);
	$dataarray = get_data_post_resource_link($sqlin, $param);
	
	//foreach that generates each course square
	foreach($usercourse as $courses){
			
		$fullname = $courses->fullname;
		$courseid = $courses->id;
		$shortname = $courses->shortname;
		$totals = 0;
		// tests if the array has something in it
		if (isset($totalresource[$courseid]))
			$totals += intval($totalresource[$courseid]);
		// tests if the array has something in it
		if (isset($totalurl[$courseid]))
			$totals += intval($totalurl[$courseid]);
		// tests if the array has something in it
		if (isset($totalpost[$courseid]))
			$totals += intval($totalpost[$courseid]);
		echo '<a class="inline link_curso" href="#'.$courseid.'"><li class="curso"><p class="nombre"><img src="images/lista_curso.png">'.$fullname.'</p>';
		//if there is something to notify, then show the number of new things
		if ($totals>0){
			echo '<span class="numero_notificaciones">'.$totals.'</span>';
		}
		include "htmltoinclude/tableheaderindex.html";
		//foreach that gives the corresponding image to the new and old items created(resource,post,forum), and its title, how upload it and its link
		foreach($dataarray as $data){
			if($data['course'] == $courseid){
				$date = date("d/m/Y H:i", $data['date']);
				echo '<tr><td><center>';
				if($data['image'] == FACEBOOK_IMAGE_POST){
					echo '<img src="images/post.png">';
				}
				elseif($data['image'] == FACEBOOK_IMAGE_RESOURCE){
					echo '<img src="images/resource.png">';
				}
				elseif($data['image'] == FACEBOOK_IMAGE_LINK){
					echo '<img src="images/link.png">';
				}
				echo '</center></td><td><a href="'.$data['link'].'" target="_blank">'.$data['title'].'</a>
								</td><td style="font-size:11px"><b>'.$data ['from'].'</b></td><td>'.$date.'</td></tr>';
			}
		}
		echo '</tbody></table></div></div>';
	}
	echo '</ul></tbody></div></div>';
	include 'htmltoinclude/spacer.html';
	echo '<div id="overlay"></div>';
	
	//updates the user last time in the app
	$userfacebookinfo->lasttimechecked = time();
	$DB->update_record('facebook_user', $userfacebookinfo);
	
} else{
	echo '<div class="cuerpo"><h1>'.get_string('existtittle', 'local_facebook').'</h1>
		     <p>'.get_string('existtext', 'local_facebook').'<a href="'.$connecturl.'" >'.get_string('existlink', 'local_facebook').'</a></p></div>';
	include 'htmltoinclude/spacer.html';
}
