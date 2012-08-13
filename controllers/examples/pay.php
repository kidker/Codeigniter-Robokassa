<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * RoboKASSA library
 *
 * Robokassa payment-system integration
 *
 * @package		CMS_CodeIgniter
 * @author		Eduard Antsupov // index01d [indevelope@x01d.com]
 * @copyright		(c) x01d
 * @license		Commercial
 * @link			http://x01d.com/
 * @link			http://robokassa.ru/
 * @version		Version 1.0
 */

class Pay extends CI_Controller
{	
	function __construct()
	{
		parent::__construct();
		$this->load->library('robokassa');
	}
	
	function increase_balance($uid=19, $amount=10.00)
	{
		$this->load->model('bill/robokassa_model');
		$this->robokassa_model->change_balance($uid, $amount, "Test interface");
	}
	
	function get_pay_info()
	{
		echo json_encode($this->robokassa->get_pay_info(1));
	}
	
	function get_pay_methods()
	{
		echo json_encode($this->robokassa->get_pay_methods());
	}
	
	function get_pay_url()
	{
		$uid = 19;
		echo $this->robokassa->create_payment($uid, 5.00, "PCR", "За дело git!");
	}
	
	function result()
	{
		if($this->robokassa->check_pay($_GET['OutSum'], $_GET['InvId'], $_GET['SignatureValue']))
			$this->robokassa->register_payment($_GET['InvId']);
		else
			echo "hm! error!";
	}
	
	function success()
	{
		if($this->robokassa->check_success_pay($_GET['OutSum'], $_GET['InvId'], $_GET['SignatureValue']))
			$this->robokassa->register_payment($_GET['InvId']);
		else
			echo "hm! error!";
	}
	
	function fail()
	{
		echo "fail";
		print_r($_POST);
	}
}
