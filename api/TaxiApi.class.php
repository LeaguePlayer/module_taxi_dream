<?php

require_once('Curl.class.php');

class TaxiApi {

	private $_host = 'https://178.46.154.73:8089/';
	private $_secretkey = '454545';

	/**
	 * Return all adressess like street
	 */
	public function getAddress($street){

		if($street){
			$params = array(
				'get_streets' => 'true',
				'get_points' => 'false',
				'get_houses' => 'false',
				//'max_addresses_count' => 10,
				'street' => $street
			);

			$curl = new Curl($this->_host.'common_api/1.0/get_addresses_like?'.$this->getParamsUrl($params));
			$curl->setSignature($this->getParamsUrl($params), $this->_secretkey);

			header('Content-type: application/json');
			$res = $curl->exec();
			if (!$res) 
				print_r($curl->getError());
			else 
				echo $res;
		}

		die();
		//print $curl->exec(); die();
	}

	/**
	 * Return all houses like $house
	 */
	public function getHouses($street, $house){
		if($street && $house){
			$params = array(
				'get_streets' => 'false',
				'get_points' => 'false',
				'get_houses' => 'true',
				//'max_addresses_count' => 10,
				'street' => $street,
				'house' => $house
			);

			$curl = new Curl($this->_host.'common_api/1.0/get_addresses_like?'.$this->getParamsUrl($params));
			$curl->setSignature($this->getParamsUrl($params), $this->_secretkey);

			header('Content-type: application/json');
			echo $curl->exec();
		}
		
		die();
		//print $curl->exec(); die();
	}

	/**
	 * Return all tariffs
	 */
	public function getTariffs(){

		$params = array();
		$curl = new Curl($this->_host.'common_api/1.0/get_tariffs_list?'.$this->getParamsUrl($params));
		$curl->setSignature($this->getParamsUrl($params), $this->_secretkey);

		header('Content-type: application/json');
		print $curl->exec(); die();
	}

	/**
	 * Get service list
	 */
	public function getServicesList(){
		$params = array(
			'tariff_id' => 1
		);
		$curl = new Curl($this->_host.'common_api/1.0/calc_order_cost?'.$this->getParamsUrl($params));
		$curl->setSignature($this->getParamsUrl($params), $this->_secretkey);

		header('Content-type: application/json');
		print $curl->exec(); die();
	}

	/**
	 * analyze_route and calc_order_cost
	 */

	public function analyzeRoute($from, $addresses){
		header('Content-type: application/json');

		//analyze route
		$params = array(
			'source' => $from,
			'dest' => $addresses[0]
		);
		$curl = new Curl($this->_host.'common_api/1.0/analyze_route?'.$this->getParamsUrl($params));
		$curl->setSignature($this->getParamsUrl($params), $this->_secretkey);

		$res = $curl->exec();
		if($res){
			$res = json_decode($res);
			if ($res->code != 0) die();

			//tariff list
			$tariffs = array( //tariffs ids
				1 => 7, //Эконом
				2 => 1, //Комфорт
				3 => 11 //Бизнес
			);

			//notes for tariff id
			$notes = array(
				1 => 'Примечание для тарифа с ID 1',
				2 => 'Примечание для тарифа с ID 2',
				7 => 'Примечание для тарифа с ID 7',
				8 => 'Примечание для тарифа с ID 8',
				11 => 'Примечание для тарифа с ID 11'
			);

			//час пик
			$currentHour = (int) date('H');
			if (($currentHour >= 7 && $currentHour <= 9) || ($currentHour >= 16 && $currentHour <= 19)) { 
				$tariffs[1] = 8;
				$tariffs[2] = 2;
			}

			$result = array();

			//calc order cost for 3 tariffs
			foreach ($tariffs as $key => $value) {
				$params = array(
					'tariff_id' => $value
				);

				$params['source_zone_id'] = $res->data->source_zone_id;
				$params['dest_zone_id'] = $res->data->dest_zone_id;
				$params['distance_city'] = $res->data->city_dist;
				$params['distance_country'] = $res->data->country_dist;
				$params['source_distance_country'] = $res->data->source_country_dist;

				$curl = new Curl($this->_host.'common_api/1.0/calc_order_cost?'.$this->getParamsUrl($params));
				$curl->setSignature($this->getParamsUrl($params), $this->_secretkey);

				$result[$key] = json_decode($curl->exec());
				$result[$key]->note = $notes[$value];
			}

			echo json_encode($result);
		}

		die();
		// header('Content-type: application/json');
		// print $curl->exec(); die();
	}

	/**
	 * Test function
	 */
	public function test(){
		$params = array(
			'get_streets' => 'true',
			'get_houses' => 'false',
			'get_points' => 'true',
			'street' => 'stree',
			'max_addresses_count' => 10
		);
		$curl = new Curl($this->_host.'common_api/1.0/get_addresses_like?'.$this->getParamsUrl($params));
		$curl->setSignature($this->getParamsUrl($params), $this->_secretkey);

		print_r($this->_host.'common_api/1.0/get_addresses_like?'.$this->getParamsUrl($params));
		//header('Content-type: application/json');
		$result = json_decode($curl->exec(), true);
		$this->pr_r($result);

		/*if(!empty($result['data']['crew_groups'])){
			foreach ($result['data']['crew_groups'] as $crew) {
				$params = array(
					'crew_id' => $crew['id']
				);

				$curl = new Curl($this->_host.'common_api/1.0/get_crew_info?'.$this->getParamsUrl($params));
				$curl->setSignature($this->getParamsUrl($params), $this->_secretkey);

				$result = json_decode($curl->exec(), true);
				$this->pr_r($result);
			}
		}*/
	}

	private function getParamsUrl($array = array()){
		return http_build_query($array);
	}

	private function pr_r($in){
		echo "<pre>";
		print_r($in);
		echo "</pre>";
	}
}