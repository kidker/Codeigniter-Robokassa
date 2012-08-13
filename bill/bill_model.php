<?php

/**
 * RoboKASSA library
 *
 * Robokassa payment-system integration
 *
 * @package		CMS_CodeIgniter
 * @author		Eduard Antsupov // index01d [indevelope@x01d.com]
 * @copyright		(c) x01d
 * @license		GPL v3
 * @link			http://x01d.com/
 * @link			http://robokassa.ru/
 * @version		Version 1.0
 */

class Bill_model extends CI_Model
{		
	function __construct()
	{
		parent::__construct();
	}
	
	/*
	 * Создает счёт для пользователя
	 */
	function create_balance($uid)
	{
		// TODO: проверить не существует ли уже
		$this->db->insert("balance", array("uid" => $uid, "money" => "0.00"));
		return true;
	}
	
	/*
	 * Возвращает количество денег на счёте пользователя с указанным идентификатором
	 */
	function get_balance($uid)
	{
		$balance = $this->db->get_where("balance", array("uid" => $uid))->row();
		if($balance)
			return $balance->money;
		else
			return "0.00";
	}
	
	/*
	 * Возвращает количество заблокированных денег на счёте пользователя с указанным идентификатором
	 */
	function get_locked($uid)
	{
		$balance = $this->db->get_where("balance", array("uid" => $uid))->row();
		if($balance)
			return $balance->locked;
		else
			return "0.00";
	}
	
	/*
	 * Метод для изменения баланса
	 * 
	 * @desc Сохраняет информацию об изменении в истории, сохраняет и изменяет текущее значение
	 * 		 баланса.
	 * 
	 * @param uid Идентификатор пользователя
	 * @param sum Сумма, на которую необходимо изменить баланс. Может быть отрицательной.
	 * @param description Текстовое описание причины изменения баланса
	 */
	function change_balance($uid, $sum='0.00', $type = 1, $description="Automatic balancer")
	{
		$this->db->query('LOCK TABLES balance_history WRITE, balance WRITE');
		
		$old_balance = $this->get_balance($uid);

		$this->db->insert("balance_history",
		array(
			"type" 		   => $type,
			"uid"  		   => $uid,
			"amount"  	   => $sum,
			"description"  => $description,
			"old_balance"  => $old_balance
		));
		$history_id = $this->db->insert_id();
		
		//$this->db->where('uid', $uid);
		
		$this->db->query("update balance set money = money + ".$sum." where uid=".$uid);
		
		$this->db->query('UNLOCK TABLES');
		
		return $history_id;
	}
	
	/*
	 * Сохраняет запрос на вывод средств в БД
	 */
	function request_withdraw($uid, $account, $amount)
	{
		$this->change_balance($uid, (-1)*$amount, 3, "Запрос на вывод средств");
		
		$this->db->query('LOCK TABLES balance WRITE, bill_requests WRITE');
		$this->db->query("update balance set locked = locked + ".$amount." where uid=".$uid);
		$this->db->insert("bill_requests", array("uid" => $uid, "account" => $account, "amount" => $amount, "paid" => 0));
		$this->db->query('UNLOCK TABLES');
	}
	
	/*
	 * Возвращает массив с историей операций
	 */
	function get_balance_history($uid, $filter='')
	{
		switch($filter)
		{
			case "month":
				$this->db->where('bill_date>', "NOW() - INTERVAL 1 MONTH", FALSE);
				break;
			case "year":
				$this->db->where('bill_date>', "NOW() - INTERVAL 1 YEAR", FALSE);
				break;
			case "all":
				break;
			case "week":
			default:
				$this->db->where('bill_date>', "NOW() - INTERVAL 1 WEEK", FALSE);
				break;
				
		}
		
		$this->db->where('uid', $uid); 
		$this->db->order_by("bill_date", "desc");
		
		return $this->db->get("balance_history")->result_array();
	}
	
	/*
	 * Проверяет был ли ранее сделан запрос на вывод средств, который ещё не выплачен
	 */
	function withdraw_requested($uid)
	{
		$this->db->where(array("uid" => $uid, "paid" => 0));
		return $this->db->count_all_results("bill_requests")>0;
	}
}	
