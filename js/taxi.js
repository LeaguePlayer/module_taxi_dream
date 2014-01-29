jQuery(document).ready(function(){


	var addressTimer = null;
	var idArr = [];
	var countAdd = 1;
	var to_move_tpl = '<div class="control-group">' +
			    		'<label class="control-label" for="">Куда поедете?</label>' +
				    	'<div class="controls">' +
				      		'<input type="text" class="to_street" id="" name="to_street[]"/>' +
				      		'<input type="text" class="to_house" id="" name="to_house[]"/>' +
				      		'<a href="#" class="add-del">Удалить адрес</a>' +
				    	'</div>' +
				  	'</div>';

	var tariffsLabels = {
		'1': 'Эконом',
		'2': 'Комфорт',
		'3': 'Бизнес'
	};

	var step1 = $('.step1');
	var step2 = $('.step2').hide();
	var step3 = $('.step3').hide();
	var step4 = $('.step4').hide();

	var smsTimeout = null;
	var smsCode = null;
	
	//function add select2 plugin on inputs
	var bindSelects = function bindSelects(from, house){
		
		//get addresses
		from.select2({
			placeholder: "Улица",
			minimumInputLength: 2,
			width: '300',
			quietMillis: 200,
			ajax: {
				url: '',
				dataType: 'json',
				data: function(term, page){
					return { from: term, get_addresses: true };
				},
				results: function(data, page){
					var res_adds = [];
					if(data.code === 0){ //ok
						var data_adds = data.data.addresses;
						if(data_adds.length){
							for(a in data_adds) { res_adds.push({id: a, text: data_adds[a].street}); }
						}
					}else res_adds.push({id: 0, text: data.descr});

					return { results: res_adds };
				}
			}
		});


		//get houses
		house.select2({
			placeholder: "Дом",
			minimumInputLength: 1,
			width: '200',
			ajax: {
				url: '',
				dataType: 'json',
				data: function(term, page){
					var street = from.select2('data');
					if(street) street = street.text;

					return { street: street, house: term, get_houses: true };
				},
				results: function(data, page){
					// console.log(data);
					var res_adds = [];

					if(data.code === 0){ //ok addresses
						var data_adds = data.data.addresses;
						if(data_adds.length){
							for(a in data_adds){
								res_adds.push({id: a, text: data_adds[a].house});
							}
						}
					}else res_adds.push({id: 0, text: data.descr});
					
					return { results: res_adds };
				}
			}
		});
	};

	// init
	bindSelects(jQuery('#from_street'), jQuery('#from_house'));
	bindSelects(jQuery('#to_street1'), jQuery('#to_house1'));
	// idArr.push({ street: '#to_street1', house: '#to_house1' });


	//add address
	jQuery('.add-address').on('click', function(){
		countAdd++;
		var row = $(to_move_tpl);
		$('label', row).attr('for', 'to_street' + countAdd).text('Еще один адрес');
		var streetInput = $('input.to_street', row).attr('id', 'to_street' + countAdd);
		var houseInput = $('input.to_house', row).attr('id', 'to_house' + countAdd);
		// idArr.push({ street: streetInput.attr('id'), house: houseInput.attr('id') });
		$('.addresses').append(row);
		bindSelects(streetInput, houseInput);
		return false;
	});


	//del row
	jQuery('.addresses').on('click', '.add-del', function(){
		var i = jQuery('.addresses .add-del').index(jQuery(this));
		// idArr = delElInArr(idArr, i+1);
		jQuery(this).closest('.control-group').remove();
		return false;
	});


	//calculate summ
	jQuery('.calculate').on('click', function(){
		var from_s = $('#from_street').select2('data');
		var from_h = $('#from_house').select2('data');
		var self = jQuery(this);

		if(from_s && from_h && from_s.text.length && from_h.text.length){
			var from = from_s.text + ',' + from_h.text;
			var addrs = [];

			var emptiesExist = false;

			var rows = $('.addresses .control-group').not(':first');
			rows.each(function(index, item) {
				var row = $(item);
				var s = $('input.to_street', row).select2('data');
				var h = $('input.to_house', row).select2('data');

				if ( s && h && s.text.length && h.text.length ) {
					addrs.push(s.text + ',' + h.text);
				} else {
					emptiesExist = true;
					return false;
				}
			});

			if ( emptiesExist ) return false;

			//get sum for 3 tariffs
			self.attr('disabled','disabled');
			jQuery.ajax({
				url: '',
				data: {from: from, addresses: addrs, analize: true},
				dataType: 'json'
			})
			.done(function(res){
				self.removeAttr('disabled');
				for(t in res){
					if(res[t]['code'] === 0){
						var sum = res[t]['data']['info'][2]['sum'],
							note = res[t]['note'];
						jQuery('.t' + t).find('input:radio').data('tariff_id', res[t]['tariff_id']);
						jQuery('.t' + t).find('.price').html('<strong><em>'+sum+'</em></strong>');
						jQuery('.t' + t).find('.note').html('<em>'+note+'</em>');
					}
				}
				$('.info.route').text('Маршрут: ' + from + ' -> ' + addrs.join(' -> '));
				step1.hide();
				step2.show();
			})
			.fail(function(){
				jQuery(this).removeAttr('disabled');
			});
		}
		return false;
	});

	$('.next', step2).click(function(e) {
		$('.info.tariff').text('Тариф: ' + tariffsLabels[$('input[name="tariff"]:checked').val()]);
		step2.hide();
		step3.show();
		return false;
	});

	$('.next', step3).click(function(e) {
		var error = false;
		$('input.required', step3 ).each(function(index, item) {
			if ( $(item).val().trim() == '' ) {
				error = true;
				return false;
			}
		});

		if ( error )
			return false;

		$('.info.client_entrance').text('Подъезд: ' + $('input[name="client_entrance"]').val() );
		$('.info.client_phone').text('Телефон: ' + $('input[name="client_phone"]').val() );
		$('.info.client_name').text('Имя: ' + $('input[name="client_name"]').val() );
		$('.info.client_comment').text('Примечание: ' + $('textarea[name="client_comment"]').val() );
		step3.hide();
		step4.show();

		$.ajax({
			url: '',
			data: {send_sms: true, phone: $('input[name="client_phone"]').val()},
			dataType: 'json'
		}).done(function(data) {
			smsTimeout = data.timeout;
			smsCode = data.sms_code;
			alert('На ваш телефон выслан код подтверждения');
		});

		return false;
	});


	$('.repeat_sms', step4).click(function() {
		smsTimeout = null;
		smsCode = null;
		$.ajax({
			url: '',
			data: {send_sms: true, phone: $('input[name="client_phone"]').val()},
			dataType: 'json'
		}).done(function(data) {
			smsTimeout = data.timeout;
			smsCode = data.sms_code;
			alert('На ваш телефон выслан код подтверждения' + smsCode);
		});
		return false;
	});


	$('.order', step4).click(function(e) {
		if ( !smsTimeout || !smsCode ) {
			return false;
		}
		if ( parseInt($('#sms_code').val()) != smsCode ) {
			alert('Неверно введен проверочный код');
			return false;
		}

		$.ajax({
			url: '',
			data: {get_time: true}
		}).done(function(time) {
			if ( parseInt(time) > smsTimeout ) {
				alert('Время действия кода истекло');
				return;
			}

			$.ajax({
				url: '',
				type: 'POST',
				data: {
					order: {
						phone: $('#client_phone', step3).val(),
						source: $('#from_street').select2('data') + ', ' + $('#from_house').select2('data'),
						source_time: $('#client_sourcetime', step3).val(),
						dest: $('#to_street1').select2('data') + ', ' + $('#to_house1').select2('data'),
						customer: $('#client_name', step3).val(),
						comment: $('#client_comment', step3).val(),
						tariff_id: $('input[name="tariff"]:checked', step2).data('tariff_id'),
						is_prior: true,
					}
				}
			}).done(function(data) {
				if ( data.code == 0 ) {
					alert('Ваша заявка принята!');
					smsTimeout = null;
					smsCode = null;
					step4.hide();
					step1.show();
				} else if (data.code == 100) {
					alert('Ваша заявка уже в обработке');
					smsTimeout = null;
					smsCode = null;
					step4.hide();
					step1.show();
				}
			});
		});
		return false;
	});

	$('.back').on('click', function(e) {
		var self = $(this);
		var step = self.closest('.step');
		if ( step.hasClass('step4') ) {
			smsTimeout = null;
			smsCode = null;
			$('input#sms_code').val('');
		}
		step.hide();
		step.prev('.step').show();
		return false;
	});
	
	//masked input for phone
	$("input#client_phone").mask("+7 (999) 999-99-99");
});