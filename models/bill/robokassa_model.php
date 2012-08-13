<?php

include_once("bill_model.php");

class Robokassa_model extends Bill_model
{		
	function __construct()
	{
		parent::__construct();
	}
	
	/*
	 * Создает счёт на пополнение баланса
	 * 
	 * @return int Идентификатор счёта
	 */
	function create_payment($uid)
	{
		$this->db->insert("robokassa_history", array("uid" => $uid));
		return $this->db->insert_id();
	}
	
	/*
	 * Регистрирует пополнение баланса и изменяет баланс пользователя
	 */
	function register_payment($data)
	{
		$robo_row = $this->db->get_where("robokassa_history", array("id" => $data['Order_id']))->row();
		
		if($robo_row && $robo_row->paid!=1) // Critical! Проверяет не был ли платёж уже зарегистрирован
		{
			$this->db->query("LOCK TABLES robokassa_history WRITE");
			
			$bid = $this->change_balance($robo_row->uid, $data['Sum'], "Robokassa payment");
			
			$this->db->where("id", $data['Order_id']);
			$this->db->update("robokassa_history",
							  array(
							  	"paid" => 1,
							  	"sum" => $data['Sum'],
							  	"bid" => $bid,
							  	"currency" => $data['Currency'],
							  	"account" => $data['InAcc'],
							  	"cur_sum" => $data['InSum'],
							  	"our_currency" => $data['OutCur']
							  )
			);
			$this->db->query("UNLOCK TABLES");
			
			return true;
		}
		else
			return false;
	}
}