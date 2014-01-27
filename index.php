<?php require_once('api/TaxiApi.class.php'); ?>
<?php
	
	require_once('api/Curl.class.php');

	$curl = new Curl('http://gates.smsgear.ru/http/gate.cgi');

	$curl->setContentType('application/x-www-form-urlencode');
	$curl->setPostData(array(
		'user' => '26883_Artemev3',
		'pass' => md5('1lkmkDPH'),
		'action' => 'post_sms',
		'message' => 'Тест',
		'target' => '+79220448947'
	));
	var_dump($curl->exec());
	// print_r($curl->getInfo());
// echo rand(100000, 999999);

if(isset($_GET['get_addresses']) || isset($_GET['get_houses']) || isset($_GET['analize'])){
	
	//Получаем адреса
	if(isset($_GET['get_addresses']) && isset($_GET['from'])){
		if(!empty($_GET['from'])){
			$ta = new TaxiApi;
			$ta->getAddress($_GET['from']);
		}
	}

	//Полчение номеров домов по адресу
	if(isset($_GET['get_houses']) && isset($_GET['street']) && isset($_GET['house'])){
		if(!empty($_GET['street']) && !empty($_GET['house'])){
			$ta = new TaxiApi;
			$ta->getHouses($_GET['street'], $_GET['house']);
		}
	}

	//Анализ маршрута
	if(isset($_GET['analize']) && isset($_GET['from']) && isset($_GET['addresses'])){
		if(!empty($_GET['from']) && !empty($_GET['addresses'])){
			$ta = new TaxiApi;
			//print_r($_GET['addresses']);
			// $ta->getTariffs();
			$ta->analyzeRoute($_GET['from'], $_GET['addresses']);
		}
	}
	die();
}

// $ta = new TaxiApi;
// $ta->getServicesList();

?>
<!DOCTYPE html>
	<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>Taxi</title>
		<link rel="stylesheet" href="js/select2/select2.css">
		<link rel="stylesheet" href="css/styles.css">
	</head>
	<body>		
		<form action="" method="POST">
			<div class="step1">
				<h2>Шаг 1</h2>
				<div class="addresses">
					<div class="row">
						<label for="from_street">От куда вас забрать?</label>
						<input type="text" id="from_street" name="from_street"/>
						<input type="text" id="from_house" name="from_house"/>
						<br>
						<label for="to_street1">Куда поедете?</label>
						<input type="text" class="to" id="to_street1" name="to_street[]"/>
						<input type="text" id="to_house1" name="to_house[]"/>
					</div>
				</div>
				<input type="button" class="add-address" value="Добавить адрес">
				<input type="button" class="calculate" value="Расчитать">
			</div>
			<div class="step2">
				<h2>Шаг 2</h2>
				<p>Выберите тариф:</p>
				<table width="70%">
					<tr>
						<td class="t1">
							<label>Эконом</label><br>
							<input type="radio" name="tariff" value="1" checked="checked" />
							<div class="price"></div>
							<div class="note"></div>
						</td>
						<td class="t2">
							<label>Комфорт</label><br>
							<input type="radio" name="tariff" value="2" />
							<div class="price"></div>
							<div class="note"></div>
						</td>
						<td class="t3">
							<label>Бизнес</label><br>
							<input type="radio" name="tariff" value="3" />
							<div class="price"></div>
							<div class="note"></div>
						</td>
					</tr>
				</table>
				<input type="button" value="Далее">
			</div>
			<div class="step3">
				<h2>Шаг 3</h2>

				<div class="row">
					<label for="client_name">Ваше имя <span class="require">*</span></label>
					<input type="text" id="client_name" name="client_name">
				</div>
				<div class="row">
					<label for="client_phone">Ваш телефон <span class="require">*</span></label>
					<input type="text" id="client_phone" name="client_phone">
				</div>
				<div class="row">
					<label for="client_entrance">Подъезд</label>
					<input type="text" id="client_entrance" name="client_entrance">
				</div>
				<div class="row">
					<label for="client_comment">Примечание</label>
					<textarea name="client_comment" id="client_comment" cols="30" rows="10"></textarea>
				</div>
				<em><span class="require">*</span> - обязательные поля</em>
			</div>

		</form>

		<script src="js/jquery.js"></script>
		<script src="js/select2/select2.min.js"></script>
		<script src="js/jquery.maskedinput.min.js"></script>
		<script src="js/taxi.js"></script>
	</body>
</html>