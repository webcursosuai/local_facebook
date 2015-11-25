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
* @package    local
* @subpackage facebook
* @copyright  2015 Hans Jeria (hansjeria@gmail.com)
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require_once (dirname ( dirname ( dirname ( __FILE__ ) ) ) . "/config.php");
global $DB, $CFG;

require_login ();
if(!is_siteadmin($USER)){
	die();
}

$url = new moodle_url ( "/local/facebook/updatedata.php" );

$context = context_system::instance ();

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_pagelayout("standard");

echo $OUTPUT->header ();

$AppID = $CFG->fbkAppID;
$SecretID = $CFG->fbkScrID;
$config = array(
		'appId' => $AppID,
		'secret' => $SecretID,
		'grant_type' => 'client_credentials' 
);

$facebook = new facebook($config);

$sqlgetusers = "SELECT *
		FROM {facebook_user} AS fu 
		WHERE fu.status = ? ";

// Users linked with facebook
$users = $DB->get_records_sql($sqlgetusers, array(1));

$countusers = count($users);
echo "La cantidad de usuarios para actualizar información son: ".$countusers."<br>";
$countusersupdate = 0;

$table = new html_table();
$table->head = array("Nombre usuario", "Facebook ID", "Actualizado");

foreach($users as $user){
	$userprofile = $facebook->api ( '' . $user->facebookid . '', 'GET' );
	
	$newinfo = new stdClass();
	$newinfo->moodleid = $user->moodleid;
	$newinfo->facebookid = $user->facebookid;
	
	$newinfo->link = $userprofile['link'];
	$newinfo->firstname = $userprofile['first_name'];
	if (isset ( $userprofile ['middle_name'] )) {
		$newinfo->middlename = $userprofile['middle_name'];
	}else{
		$newinfo->middlename = "";
	}
	$newinfo->lastname = $userprofile['last_name'];
	
	$table->data[] = array();
	
	$status = "NO";
	if($DB->update_record("facebook_user", $newinfo )){
		$countusersupdate++;
		$status = "SI";
	}
	
	$table->data[] = array(
			$newinfo->firstname." ".$newinfo->middlename." ".$newinfo->lastname,
			$user->facebookid,
			$status		
	);
	
}

echo "La cantidad de usuarios actulizados es :".$countusersupdate."<br>";

echo html_writer::table($table);	

echo $OUTPUT->footer ();

