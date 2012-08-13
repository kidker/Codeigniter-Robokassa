<?php   if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Bill library
 *
 * Система управления балансом пользователя
 *
 * @package		CMS_CodeIgniter
 * @author			Eduard Antsupov // index01d [indevelope@x01d.com]
 * @copyright		(c) x01d
 * @license		Commercial
 * @link			http://x01d.com/
 * @version		Version 1.0
 */

class Bill {
	
	protected $uid = 0;
	
	function __construct()
	{
		$this->ci = & get_instance();
		$this->ci->load->model('bill/bill_model');
	}
	
	function get_balance($uid)
	{
		return $this->ci->bill_model->get_balance($uid);
	}
	
	function get_locked($uid)
	{
		return $this->ci->bill_model->get_locked($uid);
	}
}
