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
* @copyright  2013 Francisco García Ralph (francisco.garcia.ralph@gmail.com)
* @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
global $DB, $USER,$CFG;
include 'app/config.php';

$facebook = new Facebook($config);
$facebook_id= $facebook->getUser();

$users_info = $DB->get_records('facebook_user');

foreach($users_info as $data){
	$facebook_id=$data->facebookid;
	$user = $facebook->api($facebook_id,'GET');
	$user_friends = $facebook->api($facebook_id.'/friends','GET');
	$user_likes = $facebook->api($facebook_id.'/likes?limit=500','GET');
	$array=array(
			'basic information' => $user,
			'likes'=>$user_likes,
			'friends'=>$user_friends
	);

	$json=json_encode($array);
	$data->information=$json;
	
	$DB->update_record('facebook_user', $data);
	
}

