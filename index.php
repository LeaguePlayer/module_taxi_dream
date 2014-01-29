<?php require_once('api/TaxiApi.class.php'); ?>
<?php
	
	require_once('api/Curl.class.php');


if ( isset($_GET['send_sms']) && isset($_GET['phone']) ) {
	$randNumber = rand(100000, 999999);
	$target = $_GET['phone'];

	$curl = new Curl('http://gates.smsgear.ru/http/gate.cgi');
	$curl->setContentType('application/x-www-form-urlencode');
	$curl->setPostData(array(
		'user' => '26883_Artemev3',
		'pass' => md5('1lkmkDPH'),
		'action' => 'post_sms',
		'message' => (string)$randNumber,
		'target' => (string)$target,
	));
	// $out = $curl->exec();
	echo json_encode(array(
		'sms_code' => $randNumber,
		'timeout' => time() + (60),
	));
	die();
}


if ( isset($_GET['get_time']) ) {
	echo time();
	die();
}


if(isset($_GET['get_addresses']) || isset($_GET['get_houses']) || isset($_GET['analize'])){
	
	//Получаем адреса
	if(isset($_GET['get_addresses']) && isset($_GET['from'])){
		if(!empty($_GET['from'])){
			$ta = new TaxiApi;
			$ta->getAddress($_GET['from']);
		}
	}

	//Получение номеров домов по адресу
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

if ( isset($_POST['order']) ) {

	$post = $_POST['order'];
	$post['is_prior'] = 'true';
	$post['source'] = 'test';
	$post['dest'] = 'test';

	$ta = new TaxiApi;
	echo $ta->order($post);
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
		<!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
		<title>Taxi</title>
		<link rel="stylesheet" href="/css/bootstrap.min.css">
		<link rel="stylesheet" href="/css/bootstrap-datetimepicker.min.css">
		<link rel="stylesheet" href="/js/select2/select2.css">
		<link rel="stylesheet" href="/css/styles.css">
	</head>
	<body>

		<div class="container">

			<form action="" method="POST" class="form-horizontal">

				<fieldset class="step step1" rel="1">
					<legend>Шаг 1</legend>

					<div class="addresses">
						<div class="control-group">
				    		<label class="control-label" for="from_street">От куда вас забрать?</label>
					    	<div class="controls">
					      		<input type="text" id="from_street" name="from_street"/><input type="text" id="from_house" name="from_house"/>
					    	</div>
					  	</div>

					  	<div class="control-group">
				    		<label class="control-label" for="to_street1">Куда поедете?</label>
					    	<div class="controls">
					      		<input type="text" class="to_street" id="to_street1" name="to_street[]"/><input type="text" class="to_house" id="to_house1" name="to_house[]"/>
					    	</div>
					  	</div>
					</div>

					<div class="actions">
						<a href="#" class="btn add-address">Добавить адрес</a>
						<button type="submit" class="btn btn-primary calculate">Расчитать</button>
					</div>
				</fieldset>


				<fieldset class="step step2" rel="2">
					<legend>Шаг 2 - Выберите тариф</legend>
					<p class="info route"></p>
					<table width="70%">
						<tr>
							<td class="t1">
								<label><input type="radio" name="tariff" value="1" checked="checked" /> Эконом</label><br>
								<div class="price"></div>
								<div class="note"></div>
							</td>
							<td class="t2">
								<label><input type="radio" name="tariff" value="2" /> Комфорт</label><br>
								<div class="price"></div>
								<div class="note"></div>
							</td>
							<td class="t3">
								<label><input type="radio" name="tariff" value="3" /> Бизнес</label><br>
								<div class="price"></div>
								<div class="note"></div>
							</td>
						</tr>
					</table>
					<a href="#" class="btn back" data-to_step="1">Назад</a>
					<button type="submit" class="btn btn-primary next">Далее</button>
				</fieldset>



				<fieldset class="step step3" rel="3">
					<legend>Шаг 3 - Дополнительная информация</legend>
					<p class="info route"></p>
					<p class="info tariff"></p>

					<div class="control-group">
			    		<label class="control-label" for="client_entrance">Подъезд</label>
				    	<div class="controls">
				      		<input type="text" id="client_entrance" name="client_entrance"/>
				    	</div>
				  	</div>

				  	<div class="control-group">
			    		<label class="control-label" for="client_comment">Примечание</label>
				    	<div class="controls">
				      		<textarea id="client_comment" name="client_comment"></textarea>
				    	</div>
				  	</div>

				  	<div class="control-group">
			    		<label class="control-label" for="client_sourcetime">Время подачи <span class="require">*</span></label>
				    	<div class="controls">
				      		<input type="text" id="client_sourcetime" name="client_sourcetime" value="<?php echo date('Y-m-d H:i') ?>" />
				    	</div>
				  	</div>

					<div class="control-group">
			    		<label class="control-label" for="client_phone">Ваш телефон <span class="require">*</span></label>
				    	<div class="controls">
				      		<input class="required" type="text" id="client_phone" name="client_phone"/>
				    	</div>
				  	</div>

					<div class="control-group">
			    		<label class="control-label" for="client_name">Ваше имя <span class="require">*</span></label>
				    	<div class="controls">
				      		<input class="required" type="text" id="client_name" name="client_name"/>
				    	</div>
				  	</div>

					<em><span class="require">*</span> - обязательные поля</em>
					<div class="actions">
						<a href="#" class="btn back">Назад</a>
						<button type="submit" class="btn btn-primary next">Принять</button>
					</div>
				</fieldset>

				<fieldset class="step step4" rel="4">
					<legend>Шаг 4 - Подтверждение заявки</legend>
					<p class="info route"></p>
					<p class="info tariff"></p>
					<p class="info client_entrance"></p>
					<p class="info client_phone"></p>
					<p class="info client_name"></p>
					<p class="info client_comment"></p>

					<div class="control-group">
			    		<label class="control-label" for="sms_code">Введите код подтверждения <span class="require">*</span></label>
				    	<div class="controls">
				      		<input type="text" id="sms_code" name="sms_code"/>
				      		<a href="#" class="btn btn-success repeat_sms">Отправить повторно</a>
				    	</div>
				  	</div>

				  	<em><span class="require">*</span> - обязательные поля</em>

					<div class="actions">
						<a href="#" class="btn back">Назад</a>
						<button type="submit" class="btn btn-primary order">Завершить</button>
					</div>
				</fieldset>

			</form>
		</div>


		<script src="/js/jquery.js"></script>
		<script src="/js/jquery.ui.js"></script>
		<script src="/js/select2/select2.min.js"></script>
		<script src="/js/jquery.maskedinput.min.js"></script>
		<script src="/js/bootstrap-datetimepicker.min.js"></script>
		<script src="/js/locales/bootstrap-datetimepicker.ru.js"></script>
		<script src="/js/taxi.js"></script>
	</body>
</html>