<?php

/**
 * @copyright  Frederic G. Østby
 * @license    http://www.makoframework.com/license
 */

namespace mako\auth\user;

/**
 * User interface.
 *
 * @author  Frederic G. Østby
 */

interface UserInterface
{
	public function getId();
	public function setEmail($email);
	public function getEmail();
	public function setUsername($username);
	public function getUsername();
	public function setPassword($password);
	public function getPassword();
	public function setIp($ip);
	public function getIp();
	public function generateActionToken();
	public function getActionToken();
	public function generateAccessToken();
	public function getAccessToken();
	public function activate();
	public function deactivate();
	public function isActivated();
	public function ban();
	public function unban();
	public function isBanned();
	public function save();
	public function delete();
}