<?php
    /**
     * ВНИМАНИЕ
     * Для работы с API на сервере должны быть установлены расширения Curl и JSON 
     */
$connect = array(
    'ip' => '178.46.154.73',      //IP адрес Вашего сервера Такси-Мастер
    'port' => '8090',             //Порт, который указан в настройках ТМ API
    'secret' => '454545',      //Cекретный ключ, который указан в настройках ТМ API
    'method' => 'create_order'
);

function serviceIsAvailable( $connect ) {
    $requestArgs = getArgs();
    $ch = curl_init( "https://".$connect['ip'].":".$connect['port']."/common_api/1.0/get_tariffs_list?" . $requestArgs );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
    curl_setopt( $ch, CURLOPT_TIMEOUT, 3 );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
        'Signature: ' . md5( $requestArgs . $connect['secret'] ),
        'Content-Type: application/x-www-form-urlencode'
    ));

    $response = json_decode(curl_exec( $ch ));
    $error_code = curl_errno($ch);
    curl_close($ch);
    return ( $response->code == 0 ) && ( !empty($response->data->tariffs) ) && ( $error_code == 0);
}

function getArgs( $extra = array() ) {
    return http_build_query(array_merge(
            array(
                'pay_system_type'   => '0',
                'account'           => '123400542',//'004700-000003',
                'oper_id'           => '20120813182430',//'20120813182430',
                'sum'               => '1',
                'oper_time'         => '20120813182430',
                'test'              => '1'
            )
            , $extra)
    );
}

function addOperation( $connect, $phone, $address, $customer, $comment ) {
    $requestArgs = getArgs(array( 'phone' => $phone, 'source' => $address, 'source_time' => date('YmdHis'), 'customer' => $customer, 'comment' => $comment ));
    $ch = curl_init( "https://".$connect['ip'].":".$connect['port']."/common_api/1.0/".$connect['method'] );

    curl_setopt( $ch, CURLOPT_POST, TRUE );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $requestArgs );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
    curl_setopt( $ch, CURLOPT_TIMEOUT, 3 );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
        'Signature: ' . md5( $requestArgs . $connect['secret'] ),
        'Content-Type: application/x-www-form-urlencode'
    ));

    $response = json_decode(curl_exec( $ch ));
    $error_code = curl_errno($ch);
    curl_close($ch);
    if( $response->code == 0 && !empty($response->data->order_id) && ( $error_code == 0) ) {
        return true;
    } else {
        return $response->descr;
    }
}

$result = array();
$errorMessage = array();
if( !function_exists('curl_init') ) {
    $errorMessage[] = 'не установлен curl';
}
if ( !function_exists('json_encode') ) {
    $errorMessage[] = 'не установлен json';
}
if( empty( $_POST['Number'] ) || empty( $_POST['OrderSourceAddress'] ) ) {
    $errorMessage[] = 'не указан обязательный параметр.';
}
if( serviceIsAvailable( $connect ) ) {
    $subResult = addOperation( $connect, $_POST['Number'], $_POST['OrderSourceAddress'], $_POST['OrderClientName'], $_POST['OrderComment'] );
    if( $subResult === true ) {
        $result = 'Заказ успешно принят.';
    } else {
        $errorMessage[] = $subResult;
    }
} else {
    $errorMessage[] = 'Сервис недоступен. Проверьте настройки подключения.';
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
    <title>Такси</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style type="text/css">
        body {
            font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
            font-size: 13px;
            line-height: 18px;
            color: #333;
        }
        p {
            margin: 0 0 9px;
        }
        input, button, select, textarea {
            font-family: "Helvetica Neue",Helvetica,Arial,sans-serif;
        }
        input, textarea, .uneditable-input {
            margin-left: 0;
        }
        select, textarea, input[type="text"], input[type="password"], input[type="datetime"], input[type="datetime-local"], input[type="date"], input[type="month"], input[type="time"], input[type="week"], input[type="number"], input[type="email"], input[type="url"], input[type="search"], input[type="tel"], input[type="color"], .uneditable-input {
            display: inline-block;
            height: 18px;
            padding: 4px;
            margin-bottom: 9px;
            font-size: 13px;
            line-height: 18px;
            color: #555;
        }
        textarea, input[type="text"], input[type="password"], input[type="datetime"], input[type="datetime-local"], input[type="date"], input[type="month"], input[type="time"], input[type="week"], input[type="number"], input[type="email"], input[type="url"], input[type="search"], input[type="tel"], input[type="color"], .uneditable-input {
            background-color: white;
            border: 1px solid #CCC;
            -webkit-border-radius: 3px;
            -moz-border-radius: 3px;
            border-radius: 3px;
            -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
            -moz-box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
            -webkit-transition: border linear .2s,box-shadow linear .2s;
            -moz-transition: border linear .2s,box-shadow linear .2s;
            -ms-transition: border linear .2s,box-shadow linear .2s;
            -o-transition: border linear .2s,box-shadow linear .2s;
            transition: border linear .2s,box-shadow linear .2s;
        }
        textarea:focus, input[type="text"]:focus, input[type="password"]:focus, input[type="datetime"]:focus, input[type="datetime-local"]:focus, input[type="date"]:focus, input[type="month"]:focus, input[type="time"]:focus, input[type="week"]:focus, input[type="number"]:focus, input[type="email"]:focus, input[type="url"]:focus, input[type="search"]:focus, input[type="tel"]:focus, input[type="color"]:focus, .uneditable-input:focus {
            border-color: rgba(82, 168, 236, 0.8);
            outline: 0;
            -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075),0 0 8px rgba(82, 168, 236, 0.6);
            -moz-box-shadow: inset 0 1px 1px rgba(0,0,0,0.075),0 0 8px rgba(82,168,236,0.6);
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075),0 0 8px rgba(82, 168, 236, 0.6);
        }
        input, textarea {
            width: 210px;
        }
        textarea {
            height: auto;
        }
        input[type="submit"], input[type="reset"], input[type="button"], input[type="radio"], input[type="checkbox"] {
            width: auto;
        }
        .btn {
            display: inline-block;
            padding: 4px 10px 4px;
            margin-bottom: 0;
            font-size: 13px;
            line-height: 18px;
            color: #333;
            text-align: center;
            text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
            vertical-align: middle;
            cursor: pointer;
            background-color: whiteSmoke;
            background-image: -ms-linear-gradient(top,white,#E6E6E6);
            background-image: -webkit-gradient(linear,0 0,0 100%,from(white),to(#E6E6E6));
            background-image: -webkit-linear-gradient(top,white,#E6E6E6);
            background-image: -o-linear-gradient(top,white,#E6E6E6);
            background-image: linear-gradient(top,white,#E6E6E6);
            background-image: -moz-linear-gradient(top,white,#E6E6E6);
            background-repeat: repeat-x;
            border: 1px solid #CCC;
            border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
            border-bottom-color: #B3B3B3;
            -webkit-border-radius: 4px;
            -moz-border-radius: 4px;
            border-radius: 4px;
            filter: progid:dximagetransform.microsoft.gradient(startColorstr='#ffffff',endColorstr='#e6e6e6',GradientType=0);
            filter: progid:dximagetransform.microsoft.gradient(enabled=false);
            -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2),0 1px 2px rgba(0, 0, 0, 0.05);
            -moz-box-shadow: inset 0 1px 0 rgba(255,255,255,0.2),0 1px 2px rgba(0,0,0,0.05);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2),0 1px 2px rgba(0, 0, 0, 0.05);
        }
        .btn.active, .btn:active {
            background-color: #E6E6E6;
            background-image: none;
            outline: 0;
            -webkit-box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.15),0 1px 2px rgba(0, 0, 0, 0.05);
            -moz-box-shadow: inset 0 2px 4px rgba(0,0,0,0.15),0 1px 2px rgba(0,0,0,0.05);
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.15),0 1px 2px rgba(0, 0, 0, 0.05);
        }
        .btn-primary, .btn-primary:hover {
            color: white;
            text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
            background-image: none;
        }
        .btn-primary{
            background-color: #05C;
        }
        .btn-primary:hover, .btn-primary:active {
            background-color: #05C;
        }
        .btn-primary:active, .btn-primary.active {
            background-color: #004099;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        label, input, button, select, textarea {
            font-size: 13px;
            font-weight: normal;
            line-height: 18px;
        }
        .alert {
            padding: 8px 35px 8px 14px;
            margin-bottom: 18px;
            color: #C09853;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
            background-color: #FCF8E3;
            border: 1px solid #FBEED5;
            -webkit-border-radius: 4px;
            -moz-border-radius: 4px;
            border-radius: 4px;
        }
        .alert-danger, .alert-error {
            color: #B94A48;
            background-color: #F2DEDE;
            border-color: #EED3D7;
        }
        .alert-info {
            color: #3A87AD;
            background-color: #D9EDF7;
            border-color: #BCE8F1;
        }
        .alert-success {
            color: #468847;
            background-color: #DFF0D8;
            border-color: #D6E9C6;
        }
        h2{
            margin-bottom: 10px;
        }
        .booking-wrapper{
            padding: 20px 20px 0 20px;
        }
        tr > td{
            vertical-align: top;
        }
        .settings-wrapper{
            height: 40px;
            padding: 10px 0 0 20px;
        }
        .settings label, .settings input{
            display: block;
            float: left;
        }
        .settings label{
            margin-right: 5px;
            padding: 3px;
        }
        .settings input{
            margin-right: 5px;
        }
    </style>
</head>
<body>

<div class="booking-wrapper">
    <?php if( !empty($_POST) ) { ?>
        <?php if( empty($errorMessage) ) { ?>
                <p class="alert alert-success"><?php echo $result; ?></p>
            <?php } else { ?>
                <p class="alert alert-info"><?php echo implode(' ', $errorMessage); ?></p>
            <?php } ?>
        <?php } else { ?>
<!--        <p class="alert alert-info">Выберите параметры</p>-->
    <?php } ?>

    <form action="" method="post">
        <h2>Создание заказа</h2>
        <table>
            <tr>
                <td width="175" align="right" valign="middle" class="bold">
                    *Номер телефона:
                </td>
                <td>
                    <input type="text" name="Number" class="inp" maxlength="11" size="10"
                           value="<?php echo isset($_POST['Number']) ? $_POST['Number'] : '' ?>"/><br/>
                    Пример: <strong>555111</strong> или <strong>89331515153</strong>
                </td>
            </tr>

            <tr>
                <td width="175" align="right" valign="middle">Ваше имя и отчество:</td>
                <td><input type="text" name="OrderClientName" class="inp" maxlength="50"
                           size="35"
                           value="<?php echo isset($_POST['OrderClientName']) ? $_POST['OrderClientName'] : '' ?>"/><br/>
                    Пример: <strong>Василий Анатольевич</strong>
                </td>
            </tr>

            <tr>
                <td width="175" align="right" valign="middle" class="bold">
                    *Адрес подачи:
                </td>
                <td>
                    <input type="text" name="OrderSourceAddress" class="inp"
                           maxlength="50" size="35"
                           value="<?php echo isset($_POST['OrderSourceAddress']) ? $_POST['OrderSourceAddress'] : '' ?>"/><br/>
                    Пример: <strong>Пушкинская 155</strong>
                </td>
            </tr>

            <tr>
                <td width="175" align="right" valign="middle">Примечание:</td>
                <td>
                    <textarea name="OrderComment" class="inp" maxlength="100" cols="35"></textarea><br/>
                    Заполнять не обязательно
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>* - Поля, обязательные к заполнению.</strong>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td>
                    <input type="submit" name="submit" class="btn btn-primary" value="Заказать" style="float:right"/>
                </td>
            </tr>
        </table>
    </form>
</div>
</body>
</html>