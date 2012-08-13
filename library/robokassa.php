<?php   if(!defined('BASEPATH')) exit('No direct script access allowed');

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

class Robokassa {
	
	protected $ci = NULL; // TODO: check null
	
	protected $login = "";
	protected $password1 = "";
	protected $password2 = "";
	
	protected $order_id = 0;
	protected $sum = 0.0;
	protected $currency = "PCR";
	protected $description = "";
	protected $rlanguage = "ru";
	protected $signature = "";

	// TODO: replace test urls
	protected $api_pay_url   = "http://test.robokassa.ru/Index.aspx?";
	protected $api_info_url  = "http://test.robokassa.ru/Webservice/Service.asmx/GetCurrencies?";
	protected $api_state_url = "http://test.robokassa.ru/Webservice/Service.asmx/OpState?";
	
	function __construct()
	{
		$this->ci = & get_instance();
		$this->ci->load->config('robokassa');
		$this->ci->load->model('bill/robokassa_model');
		
		$this->login 	  = $this->ci->config->item('robokassa_login');
		$this->password1  = $this->ci->config->item('robokassa_passwd1');
		$this->password2  = $this->ci->config->item('robokassa_passwd2');
		$this->rlanguage  = $this->ci->config->item('robokassa_language');
	}
	
	/*
	 * Выписка счёта для пользователя
	 * 
	 * @param $login Логин владельца магазина
	 * 
	 * TODO: Проверять на количество открытых и неоплаченных счетов
	 */
	function create_payment($uid, $sum = 0.0, $currency = "PCR", $description = "")
	{
		// TODO: Фильтровать (ограничить) параметры в соответствии с условиями системы
		// @see http://www.robokassa.ru/ru/Doc/Ru/Interface.aspx#22
		$this->order_id = $this->ci->robokassa_model->create_payment($uid);
		$this->sum = $sum;
		$this->description = $description;
		$this->currency = $currency;
		
		// sMerchantLogin:nOutSum:nInvId:sMerchantPass1:shpa=yyy:shpb=xxx
		// shpa,shpb - пользовательские параметры
		$this->signature = md5($this->login.":".$this->sum.":".$this->order_id.":".$this->password1);
		
		return $this->get_pay_url();
	}
	
	/*
	 * Регистрирует платёж с указанным идентификатором
	 */
	function register_payment($order_id)
	{
		$data = $this->get_pay_info($order_id);

		if($data && $data['Code']==100) // Платёж успешно совершен @see http://www.robokassa.ru/ru/Doc/Ru/Interface.aspx#234
			return $this->ci->robokassa_model->register_payment($data);
		else
			return false;
	}
	
	/*
	 * Возвращает пользователю ссылку на оплату
	 * 
	 * @see http://www.robokassa.ru/ru/Doc/Ru/Interface.aspx#222
	 */
	function get_pay_url()
	{
		return $this->api_pay_url."MrchLogin=".$this->login
						."&OutSum=".$this->sum
						."&InvId=".$this->order_id
						."&Desc=".$this->description
						."&SignatureValue=".$this->signature
						."&IncCurrLabel=".$this->currency
						."&Culture=".$this->rlanguage;
	}
	
	/*
	 * Проверяет подлинность присланных сервером данных
	 * 
	 * @return boolean
	 * 
	 * @see http://www.robokassa.ru/ru/Doc/Ru/Interface.aspx#223
	 */
	function check_pay($sum, $order_id, $signature)
	{
		$local_signature = md5($sum.":".$order_id.":".$this->password2);
		
		return (strtoupper($local_signature) == strtoupper($signature));
	}
	
	/*
	 * Проверяет подлинность присланных пользователем данных
	 * 
	 * @return boolean
	 * 
	 * @see http://www.robokassa.ru/ru/Doc/Ru/Interface.aspx#224
	 */
	function check_success_pay($sum, $order_id, $signature)
	{
		$local_signature = md5($sum.":".$order_id.":".$this->password1);
		
		return (strtoupper($local_signature) == strtoupper($signature));
	}
	
	/*
	 * Метод возвращает ассоциативный массив с методами оплаты, рассортироваными по категориям
	 * 
	 * @return Array("Category1" => Array("Description" => "Первая категория", 
	 * 									  Array("Способ оплаты 1", "Способ оплаты 2")),
	 *				 "Category2" => Array("Description" => "Вторая категория", 
	 * 									  Array("Ещё способ 1", "Ещё способ 2")),
	 * 					...
	 * 		   )
	 * 
	 * @see http://www.robokassa.ru/ru/Doc/Ru/Interface.aspx#232
	 */
	function get_pay_methods()
	{
		// Запрос информации о доступных методах платежа для текущего аккаунта (~ 1s.)
		$url = $this->api_info_url.'MerchantLogin='.$this->login.'&Language='.$this->rlanguage;
		$xml = file_get_contents($url);
		
		$xml = new SimpleXMLElement($xml);
		
		$result = array();
		
		// Разбор xml, формирование результирующего массива
		foreach($xml->Groups->Group as $group)
		{
			$code = (string)$group["Code"];
			$result[$code]["description"] = (string)$group["Description"];
			$result[$code]["methods"] = array();
			
			foreach ($group->Items->Currency as $currency)
				$result[$code]["methods"][] = array("Name" => (string)$currency["Name"], "Code" => (string)$currency["Label"]);
		}
		
		return $result;
	}
	
	/*
	 * Возвращает информацию о платеже с указанным идентификатором
	 * 
	 * @see http://www.robokassa.ru/ru/Doc/Ru/Interface.aspx#234
	 */
	function get_pay_info($order_id)
	{
		$signature = strtoupper(md5($this->login.":".$order_id.":".$this->password2));
				
		// TODO: remove StateCode, need for test-server
		$url = $this->api_state_url."MerchantLogin=".$this->login."&InvoiceID=".$order_id
								   ."&Signature=".$signature."&StateCode=100";
		$xml = file_get_contents($url);
				
		$xml = new SimpleXMLElement($xml);
		
		// Success 
		// @see http://www.robokassa.ru/ru/Doc/Ru/Interface.aspx#230
		// @see http://www.robokassa.ru/ru/Doc/Ru/Interface.aspx#234
		if((int)$xml->Result->Code == 0 && (int)$xml->State->Code!=1)
		{
			$result = array();
			
			$result['Order_id'] = $order_id;
			$result['Code']     = (int)$xml->State->Code;
			$result['Date']     = (string)$xml->State->StateDate;
			$result['Currency'] = (string)$xml->Info->IncCurrLabel;
			$result['InSum']    = (string)$xml->Info->IncSum;
			$result['InAcc']	= (string)$xml->Info->IncAccount;
			$result['OutCur'] 	= (string)$xml->Info->OutCurrLabel;
			$result['Sum'] 		= (string)$xml->Info->OutSum;
			
			return $result;
		}
		else
			return false;
	}
	
}

?>
