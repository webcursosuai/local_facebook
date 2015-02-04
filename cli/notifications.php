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
 * This script send notifications on facebook
 *
 * @package    local/facebook/
 * @subpackage cli
 * @copyright  2010 Jorge Villalon (http://villalon.cl)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->libdir.'/clilib.php');      // cli only functions
require_once($CFG->libdir.'/moodlelib.php');      // moodle lib functions
require_once($CFG->libdir.'/datalib.php');      // data lib functions
require_once($CFG->libdir.'/accesslib.php');      // access lib functions
require_once($CFG->dirroot.'/course/lib.php');      // course lib functions
require_once($CFG->dirroot.'/enrol/guest/lib.php');      // guest enrol lib functions
include "../app/facebook-php-sdk-master/src/facebook.php";
// now get cli options
list($options, $unrecognized) = cli_get_params(array('help'=>false),
                                               array('h'=>'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Send facebook notifications when a course have some news.

Options:
-h, --help            Print out this help

Example:
\$sudo /usr/bin/php /local/facebook/cli/notifications.php
"; //TODO: localize - to be translated later when everything is finished

    echo $help;
    die;
}

cli_heading('Facebook notifications'); // TODO: localize

echo "\nSearching for new notifications\n";
echo "\nStarting at ".date("F j, Y, G:i:s")."\n";
$param = $DB->get_record_sql('SELECT max(timemodified) as maxtime FROM {facebook_notifications} WHERE status = ?',array('1'));

if($param->maxtime == null ){

	$timemodified=0;

}else{
	$timemodified=$param->maxtime;
}

$data_resource = $DB->get_records_sql('SELECT * FROM {resource} WHERE timemodified >= ?', array($timemodified));

foreach ($data_resource as $resources){

	$record = new stdClass();
	$record->courseid =$resources->course;
	$record->time = time();
	$record->status=0;
	$record->timemodified=0;


	$DB->insert_record('facebook_notifications', $record);

}
$i=0;
$data_notifications=$DB->get_records_sql('SELECT * FROM {facebook_notifications} WHERE status = ? AND time >= ? ',array('0',$timemodified));
$array_facebookid=array();
foreach ($data_notifications as $notification){

	$user = $DB->get_records_sql('SELECT a.userid FROM {user_enrolments} a INNER JOIN  {enrol} b on a.enrolid=b.id WHERE b.courseid='.$notification->courseid.'');

	foreach($user as $users){

		$loggedoff=get_user_preferences('message_provider_local_facebook_notification_loggedoff','',$users->userid);
		$loggedin=get_user_preferences('message_provider_local_facebook_notification_loggedin','',$users->userid);
		$loggedoffarray = explode(',', $loggedoff);
		$loggedinarray = explode(',', $loggedin);


		if(in_array('facebook',$loggedoffarray)||in_array('facebook',$loggedinarray)){
				
			$not=$DB->get_record('facebook_user', array('moodleid'=>$users->userid,'status'=>'1'));

			if($not !=false){
					
				if(in_array($not->facebookid,$array_facebookid)){

				}
				else{

					$array_facebookid[]=$not->facebookid;
					$i++;
				}
			}
		}
	}

	$notification->status =1;
	$notification->timemodified=time();

	$DB->update_record('facebook_notifications', $notification);

}
$time=time();
echo $i." Notifications found\n";
echo "ok\n";
echo "Sending notifications ".date("F j, Y, G:i:s")."\n";


$AppID= $CFG->fbkAppID;
$SecretID= $CFG->fbkScrID;
$config = array(
		'appId' => $AppID,
		'secret' => $SecretID,
		'grant_type' => 'client_credentials' );
$facebook = new Facebook($config, true);

$k=0;
$token= $CFG->fbkTkn;
foreach ($array_facebookid as $user_facebookid)
{

	if($user_facebookid!=null){

		$post = $facebook->api('/'.$user_facebookid.'/notifications/', 'post',  array(
				'access_token' => $token,
				'href' => '',  //this does link to the app's root, don't think this actually works, seems to link to the app's canvas page
				'template' => 'Tienes nuevas notificaciones en Webcursos.'
				
		));
$k++;
echo $k." ".$user_facebookid." ok\n";;
	}
}

echo "ok\n";
echo $k." notificantions sent.\n";
echo "Ending at ".date("F j, Y, G:i:s");
$timenow=time();
$execute=$time - $timenow;
echo "\nExecute time ".$execute." sec";
echo "\n";




exit(0); // 0 means success
