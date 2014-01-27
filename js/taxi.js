jQuery(document).ready(function(){
	var addressTimer = null;
	var idArr = [];
	var countAdd = 1;
	
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

	idArr.push({ street: '#to_street1', house: '#to_house1' });

	//add address
	jQuery('.add-address').on('click', function(){
		countAdd++;

		var label = jQuery('<label>Еще один адрес</label>');
		var from = jQuery('<input type="text" id="to_street' + countAdd + '" name="to_street[]" />');
		var house = jQuery('<input type="text" id="to_house' + countAdd + '" name="to_house[]" />');
		var del = jQuery('<a class="add-del" href="#">Удалить адрес</a>');

		idArr.push({ street: '#to_street' + countAdd, house: '#to_house' + countAdd });

		jQuery('.addresses').append('<div class="row"></div>').find('.row:last').append(label, from, house, del);

		bindSelects(from, house);
	});

	//del row
	jQuery('.addresses').on('click', '.add-del', function(){
		var i = jQuery('.addresses .add-del').index(jQuery(this));
		idArr = delElInArr(idArr, i+1);

		countAdd--;
		jQuery(this).closest('.row').remove();
	});

	//calculate summ
	jQuery('.calculate').on('click', function(){
		var from_s = jQuery('#from_street').select2('data');
		var from_h = jQuery('#from_house').select2('data');
		var self = jQuery(this);

		if(from_s && from_h && from_s.text.length && from_h.text.length){
			var from = from_s.text + ',' + from_h.text;
			var addrs = [];

			for(i in idArr){
				var s = jQuery(idArr[i].street).select2('data');
				var h = jQuery(idArr[i].house).select2('data');
				// console.log(idArr[i], s, h);
				if(s && h && s.text.length && h.text.length){
					addrs.push(s.text + ',' + h.text);
				}
			}

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
						jQuery('.t' + t).find('.price').html('<strong><em>'+sum+'</em></strong>');
						jQuery('.t' + t).find('.note').html('<em>'+note+'</em>');
					}
				}
			})
			.fail(function(){
				jQuery(this).removeAttr('disabled');
			});
		}
	});
	
	//masked input for phone
	$("#client_phone").mask("+7 (999) 999-99-99");

	//delete el from array and return array
	var delElInArr = function rewrite(arr, i){
		var tmp = [];

		for(el in arr){
			if (el == i) continue;
			tmp.push(arr[el]);
		}

		return tmp;
	};
});