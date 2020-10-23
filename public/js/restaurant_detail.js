app.controller('restaurants_detail', ['$scope', '$http', '$timeout', function ($scope, $http, $timeout) {
	$('.detail-popup .icon-close-2').click(function () {
		$scope.item_count = 1;
		$('.detail-popup').removeClass('active');
		$('body').removeClass('non-scroll');
	});

	$('.restuarant-popup .icon-close-2').click(function () {
		$('.restuarant-popup').removeClass('active');
		$('body').removeClass('non-scroll');
	});

	$scope.show_promo = function(){
		$('.add-promo').toggle();
		$('.promo_btn_show').toggle();
	};

	$scope.menu_item = '';
	$scope.item_count = 1;
	$scope.index_id='';

	$(document).on('change','#menu_changes',function() {
		var category_id = $('#menu_changes').val();
		var url_category = getUrls('category_details');
		$http.post(url_category,{
			id : category_id,
		}).then(function(response){
			$scope.menu_category = response.data.menu_category;
		});
	});
	
	$scope.apply_promo = function() {
		$('.promo_code_error').addClass('text-danger');
		var promo_code = $('.promo_code_val').val();
		if(promo_code=='') {
			$('.promo_code_error').removeClass('d-none');
			return false
		}
		$('.promo_code_error').addClass('d-none');
		$('.promo_loading').addClass('loading');
		var add_promo_code_data = getUrls('add_promo_code_data');
		$http.post(add_promo_code_data,{
			code : promo_code,
			restaurant_id : $('#restaurant_id').val(),
		}).then(function(response){
			$('.promo_code_error').removeClass('d-none');
			$('.promo_loading').removeClass('loading');
			if(response.data.status==0)
			{

				$('.promo_code_error').text(response.data.message);
				return false
			}
			$('.promo_code_success').removeClass('d-none');
			$('.promo_code_success').text(response.data.message);
			$scope.order_data = response.data.order_detail_data;
		});
		return false;
	};

	$scope.remove_promo = function() {
		$('.promo_code_error').addClass('text-danger');
		var promo_id = $('#promo_code_id').val();
		if(promo_id=='') {
			$('.promo_code_error').removeClass('d-none');
			return false
		}
		$('.promo_remove').addClass('d-none');
		$('.promo_loading').addClass('loading');
		var remove_promo_code_data = getUrls('remove_promo_code');
		$http.post(remove_promo_code_data,{
				id : promo_id,
				restaurant_id : $('#restaurant_id').val(),
		}).then(function(response){
			 	$('.promo_code_error').removeClass('d-none');
				$('.promo_loading').removeClass('loading');
					if(response.data.status==0)
					{
						$('.promo_code_error').text(response.data.message);
						return false
					}
					$('.promo_code_success').removeClass('d-none');
					$('.promo_code_success').text(response.data.message);
				$scope.order_data = response.data.order_detail_data;
		});
		return false;
	};

	$('.pro-item').click(function () {
		if($('#location_search').val()=='') {
			$('.location_error_msg').removeClass('d-none');
			return false;
		}
		$('.location_error_msg').addClass('d-none');
		var item_id = $(this).attr('data-id');
		var price1 = $(this).attr('data-price');
		$scope.item_count = 1;
		$('.count_item').text($scope.item_count);
		$('#menu_item_price').text(price1);

		var url_item = getUrls('item_details');

		$http.post(url_item,{
			item_id :  item_id,
		}).then(function(response) {
			$scope.add_notes = '';
			$scope.menu_item = response.data.menu_item;
			setTimeout( () => $scope.updateModifierItems(),10);
			$('body').addClass('non-scroll');
			$('.detail-popup').addClass('active');
		});
	});

	// Common function to check and apply Scope value
    $scope.applyScope = function() {
        if(!$scope.$$phase) {
            $scope.$apply();
        }
    };

	// Check input is valid or not
    $scope.checkInValidInput = function(value) {
        return (value == undefined || value == 0 || value == '');
    };

	$scope.updateModifierItem = function(modifier_item,type) {
		if(modifier_item.item_count <= 0 && type == 'decrease') {
			return false;
		}
		if(type == 'decrease') {
			modifier_item.item_count--;
		}
		else {
			modifier_item.item_count++;
		}
		modifier_item.is_selected = (modifier_item.item_count > 0);
		setTimeout( () => $scope.updateModifierItems(),10);
	};

	$scope.updateCount = function(modifier_item) {
		modifier_item.item_count = (modifier_item.is_selected) ? 1 : 0;
		setTimeout( () => $scope.updateModifierItems(),10);
	};

	$scope.updateRadioCount = function(index,modifier_item_id) {
		menu_item_modifier = $scope.menu_item.menu_item_modifier[index];
		$.each(menu_item_modifier.menu_item_modifier_item, function(key,menu_item_modifier_item) {
			menu_item_modifier_item.item_count = 0;
			menu_item_modifier_item.is_selected = false;
			if(menu_item_modifier_item.id == modifier_item_id) {
				menu_item_modifier_item.item_count = 1;
				menu_item_modifier_item.is_selected = true;
			}
		});

		setTimeout( () => $scope.updateModifierItems(),10);
	};

	$scope.updateModifierItems = function() {
		var cart_disabled = false;
		angular.forEach($scope.menu_item.menu_item_modifier, function(menu_item_modifier,index) {
			var item_count = 0;
			$.each(menu_item_modifier.menu_item_modifier_item, function(key,menu_item_modifier_item) {
				if(menu_item_modifier.is_multiple != 1) {
					item_count += (menu_item_modifier_item.is_selected) ? 1 : 0;
				}
				else {
					item_count += menu_item_modifier_item.item_count;
				}
				if($scope.menu_item.menu_item_modifier[index].is_selected == false && menu_item_modifier_item.is_selected) {
					$scope.menu_item.menu_item_modifier[index].is_selected = true;
				}

				if($scope.menu_item.menu_item_modifier[index].is_multiple == 0 && $scope.menu_item.menu_item_modifier[index].is_required == 1 && $scope.menu_item.menu_item_modifier[index].count_type == 0 && ($scope.menu_item.menu_item_modifier[index].max_count == 0 || ($scope.menu_item.menu_item_modifier[index].max_count == 1 && menu_item_modifier.menu_item_modifier_item.length == 1))) {
					$scope.menu_item.menu_item_modifier[index].is_selected = true;
					$scope.menu_item.menu_item_modifier[index].item_count = 1;
					menu_item_modifier_item.is_selected = true;
					menu_item_modifier_item.item_count = 1;
					item_count = 1;
				}
			});

			menu_item_modifier.isMaxSelected = false;
			if(menu_item_modifier.max_count == item_count) {
				menu_item_modifier.isMaxSelected = true;
				$.each(menu_item_modifier.menu_item_modifier_item, function(key,menu_item_modifier_item) {
					menu_item_modifier_item.isDisabled = true;
				});
			}

			if(menu_item_modifier.is_required == 1) {
				if(menu_item_modifier.count_type == 0 && item_count < menu_item_modifier.max_count) {
					cart_disabled = true;
				}

				if(menu_item_modifier.count_type == 1) {
					if(item_count < menu_item_modifier.min_count) {
						cart_disabled = true;
					}
				}
			}
		});

		$scope.cartDisabled = cart_disabled;
		$scope.updateCartPrice();
		$scope.applyScope();
	};

	$scope.updateCartPrice = function() {
		var modifer_price = 0;
		var menu_price = $scope.menu_item.offer_price > 0 ? $scope.menu_item.offer_price : $scope.menu_item.price;
		menu_price = menu_price - 0;
		
		angular.forEach($scope.menu_item.menu_item_modifier, function(menu_item_modifier) {
			$.each(menu_item_modifier.menu_item_modifier_item, function(key,menu_item_modifier_item) {
				var item_count = 0;
				if(menu_item_modifier.is_multiple != 1) {
					item_count += (menu_item_modifier_item.is_selected) ? 1 : 0;
				}
				else {
					item_count += menu_item_modifier_item.item_count
				}
				modifer_price += (item_count * menu_item_modifier_item.price);
			});
		});
		$scope.menu_item_price = $scope.item_count * (menu_price + modifer_price);
		$('#menu_item_price').text($scope.menu_item_price.toFixed(2));
	};

	$(document).on('click','.value-changer',function() {
		if($(this).attr('data-val')=='add') {
			if($scope.item_count < 20) {
				$scope.item_count++;
			}
		}

		if($(this).attr('data-val')=='remove') {
			if($scope.item_count > 1) {
				$scope.item_count--;
			}
		}

		$scope.updateCartPrice();
		$scope.applyScope();
	});

	$('#checkout').click(function () {
		if($scope.order_data=='') {
			return false;
		}
		else {
			var url = getUrls('checkout');
			window.location.href = url ;
		}
	});

	$scope.order_data =[];
	$scope.add_notes='';

	function check_restaurant() {
		var session_order_data = $scope.order_data;
		var prev_restaurant_id ;

		$.each(session_order_data,function(key,val){
			prev_restaurant_id =val.restaurant_id;
		});

		if(prev_restaurant_id){
			if(prev_restaurant_id!=$scope.restaurant_id){
				$scope.item_counts = $scope.item_count;
				$('.icon-close-2').trigger('click');
				$('.toogle_modal').trigger('click');
				return false;
			}
		}
		return true;
	}

	//remove order
	$scope.remove_sesion_data = function(index) {
		$('#calculation_form').addClass('loading');
		var remove_url = getUrls('orders_remove');
		$scope.order_data.items.splice(index, 1);
		var data = $scope.order_data;
		$http.post(remove_url,{
			order_data    : data,
		}).then(function(response){
			$scope.order_data = response.data.order_data;
			$('#calculation_form').removeClass('loading');
			if($scope.order_data==''){
				$('#count_card').text('');
				$('.icon-shopping-bag-1').removeClass('active');
				$('#checkout').attr('disabled','disabled');
				if($('#check_detail_page').val()!=1){
					var url = getUrls('search');
					window.location.href = url ;
				}
			}

			$('#calculation_form').removeClass('loading');
		});
	};

	$scope.$watch('order_data', function() {
		if($scope.other_restaurant == 'no' && $scope.order_data) {
			if($scope.order_data.total_item_count > 0) {
				$('.icon-shopping-bag-1').addClass('active');
			}
			else {
				$('.icon-shopping-bag-1').removeClass('active');
			}
			$('#count_card').text($scope.order_data ? $scope.order_data.total_item_count:'');
		}
	});

	$scope.order_store_changes = function(order_item_id) {
		if($('#confirm_address').val()!='')
		$('#place_order').removeAttr('disabled');
		$('#calculation_form').addClass('loading');
		var change_url = getUrls('orders_change');

		$http.post(change_url,{
			order_data    : $scope.order_data,
			order_item_id    : order_item_id,
			 delivery_mode : $scope.delivery_mode,
		}).then(function(response){
			$scope.order_data = response.data.order_data;
			$('#calculation_form').removeClass('loading');
			$('#calculation_form').removeClass('loading');
		});

		if($scope.delivery_mode == 1) {
			$('.location_section').hide();
			$scope.num_five = 4;
		} else {
			$('.location_section').show();
			$scope.num_five = 5;			
		}
	};

	$('.restaurant_popup').click(function(){
		var url = getUrls('session_clear_data');
		$http.post(url,{}).then(function(response) {
			$scope.order_data ='';
			$scope.other_restaurant = 'no';
			$scope.order_store_session();
		});
	});

	var autocompletes;
	initAutocompletes();

	function initAutocompletes() {
		if(document.getElementById('confirm_address') == undefined) {
	    	return false;
	    }
		autocompletes = new google.maps.places.Autocomplete(document.getElementById('confirm_address'),{types: ['geocode']});
		autocompletes.addListener('place_changed', fillInAddress1);
	}

	function fillInAddress1() {
		$('#header_location_val').val('');
		fetchMapAddress1(autocompletes.getPlace());
	}

	function fetchMapAddress1(data) {
		var componentForm = {
			street_number: 'short_name',
			route: 'long_name',
			sublocality_level_1: 'long_name',
			sublocality_level_2: 'long_name',
			sublocality_level_3: 'long_name',
			sublocality: 'long_name',
			locality: 'long_name',
			administrative_area_level_1: 'long_name',
			country: 'short_name',
			postal_code: 'short_name'
		};

		$scope.address = '';
		$scope.postal_code = '';
		$scope.city = '';
		$scope.latitude = '';
		$scope.longitude = '';
		$scope.locality = '';

		var place = data;
		for (var i = 0; i < place.address_components.length; i++) {
			var addressType = place.address_components[i].types[0];
			if (componentForm[addressType]) {
				var val = place.address_components[i][componentForm[addressType]];
				if (addressType == 'postal_code') $scope.postal_code = val;
				if (addressType == 'locality') $scope.city = val;

				if (addressType == 'sublocality_level_1' && $scope.locality == '') 
					$scope.locality = val;
				else if (addressType == 'sublocality' && $scope.locality == '') 
					$scope.locality = val;
				else if (addressType == 'locality' && $scope.locality == '') 
					$scope.locality = val;
				else if(addressType == 'administrative_area_level_1' && $scope.locality == '') 
					$scope.locality = val;
				else if(addressType  == 'country' && $scope.locality == '') 
					$scope.locality = place.address_components[i]['long_name'];

				if(addressType       == 'street_number')
					$scope.street_address = val;
				if(addressType       == 'route')
					$scope.street_address = $scope.street_address+' '+val;
				if(addressType       == 'country')
					$scope.country = val;
				if(addressType       == 'administrative_area_level_1')
					$scope.state = val;
			}
		}

		$scope.latitude = place.geometry.location.lat();
		$scope.longitude = place.geometry.location.lng();
		$scope.is_auto_complete = 1;
		$('.checkout-content').addClass('loading');
		var url_search = getUrls('store_location');
		var location_val = $('#header_location_val').val();
		$scope.street_address = ($scope.street_address)?$scope.street_address:$scope.city;
		$http.post(url_search,{
			postal_code: $scope.postal_code,
			city: $scope.city,
			address : $scope.street_address,
			latitude: $scope.latitude,
			longitude: $scope.longitude,
			state : $scope.state,
			country : $scope.country,
			location: location_val,
			locality: $scope.locality,
			order_id: $scope.order_data.order_id,
		}).then(function(response) {
			$scope.order_data.delivery_fee=response.data.data.delivery_fee;
            $scope.order_data.total_price=parseInt($scope.order_data.subtotal)+parseInt($scope.order_data.tax)+parseInt($scope.order_data.booking_fee)+parseInt($scope.order_data.delivery_fee);
		});

		setTimeout( () => {
			$('#error_place_order').hide();
			var url              = getUrls('location_check');
			var restuarant_id    = $('#restaurant_id').val();
			var order_data_id    = $('#order_data_id').val();
			var location         = $('#confirm_address').val();

			$http.post(url,{
				order_id         : order_data_id,
				restuarant_id    : restuarant_id,
				city             : $scope.city,
				address1         : $scope.street_address,
				state            : $scope.state,
				country          : $scope.country,
				postal_code      : $scope.postal_code,
				latitude         : $scope.latitude,
				longitude        : $scope.longitude,
				location         : location,
				locality         : $scope.locality,
				checkout_page    : 'Yes',

			}).then(function(response){
				if(response.data.success=='none'){
					$('.checkout-content').removeClass('loading');
					$('#error_place_order').show();
					$('#place_order').attr('disabled','disabled');
					$('#error_place_order').text(response.data.message);
					return false;
				}
				$('#error_place_order').hide();
				$('.checkout-content').removeClass('loading');
			});
			$('#place_order').removeAttr('disabled');
			$('#order_city').val($scope.city);
			$('#order_street').val($scope.street_address);
			$('#order_state').val($scope.state);
			$('#order_country').val($scope.country);
			$('#order_postal_code').val($scope.postal_code);
			$('#order_latitude').val($scope.latitude);
			$('#order_longitude').val($scope.longitude);
			
		},2000);

	}

	$('#confirm_address').change(function(){
		if($(this).val()=='') {
			$('#place_order').attr('disabled','disabled');
		}
	});

	$(document).ready(function(){
		if($('#confirm_address').val()==''){
			$('#place_order').attr('disabled','disabled');
			$('#error_place_order').show();
		} else if($scope.delivery_mode==''){
		  $('#place_order').attr('disabled','disabled');
		}
		else{
			$('#place_order').removeAttr('disabled');
			$('#error_place_order').hide();
		}

		$('#confirm_address').keyup(function(){
			if($('#confirm_address').val()==''){
				$('#place_order').attr('disabled','disabled');
				$('#error_place_order').show();
				$('#error_place_order').text(Lang.get('js_messages.restaurant.location_field_is_required'));
			}
		})
	});

	$scope.updateCardDetails = function() {
		$('.payment-modal_load').addClass('loading');
		$('#error_add_card').text('');
		$scope.ajax_loading = true;
		var url = getUrls('card_details');
		var data_params = {
			card_number     : $('#card_number').val(),    
			expire_month    : $('#expire_month').val(),
			expire_year     : $('#expire_year').val(),
			cvv_number      : $('#cvv_number').val(),
		}

		$http.post(url,data_params).then(function(response) {
			$('.payment-modal_load').removeClass('loading');
			if(response.data.status_code == '0') {
				$scope.ajax_loading = false;
				$('#error_add_card').text(response.data.status_message);
			}
			if(response.data.status_code == '1') {
				$('#payment-modal').modal('hide');
				$scope.confirmCardSetup(response.data.intent_client_secret);
			}
		});

	};

    $scope.confirmCardSetup = function (clientSecret) {
		var stripe = Stripe(STRIPE_PUBLISH_KEY);
		stripe.confirmCardSetup(clientSecret).then(function(result) {
		    if (result.error) {
		      	// Display error.message in your UI.
		      	$('#payment-error').text(result.error.message);
				$('#payment-error').removeClass('d-none');
		      	$scope.ajax_loading = false;
		      	$scope.applyScope();
		    }
		    else {
		      	// The setup has succeeded. Display a success message.
		    	$scope.confirmCardDetails(result.setupIntent.id);
		    }
		});
    };

    $scope.completeCardAuthentication = function (clientSecret,data_params) {
    	var stripe = Stripe(STRIPE_PUBLISH_KEY);
        stripe.confirmCardPayment(clientSecret).then(function(result) {
			if (result.error) {
				// Show error in payment form
				$('#payment-error').text(result.error.message);
				$('#payment-error').removeClass('d-none');
				$scope.ajax_loading = false;
				$scope.applyScope();
			}
			else {
				// The card action has been handled & The PaymentIntent can be confirmed again on the server
				data_params['payment_intent_id'] = result.paymentIntent.id;
				$scope.placeOrder(data_params);
			}
        });
    };

    $scope.confirmCardDetails = function(intent_id) {
		var url = getUrls('card_details');
		var data_params = {
			intent_id     : intent_id,
		}

		$http.post(url,data_params).then(function(response) {
			$scope.ajax_loading = false;
			$('.payment-modal_load').removeClass('loading');
			if(response.data.status_code == '0') {
				$('#error_add_card').text(response.data.status_message);
			}

			if(response.data.status_code == '1') {
				$('#payment-modal').modal('hide');
				$scope.confirmCardSetup(response.data.intent_client_secret);
			}

			if(response.data.status_code == '2') {
				$('#payment-modal').modal('hide');
				$scope.payment_details =response.data.payment_details;
				$scope.payment_method =1;
			}
		});
    };

	$('#payment_method').change(function(){
		var payment_method = $('#payment_method').val();
		var display = (payment_method==1) ? 'block' : 'none';
		$('#payment_detail').css('display',display);
	});
	
	$('#place_order').click(function() {
		$('.place_order_change').addClass('loading');
		var confirm_address     = $('#confirm_address').val();
		var order_street        = $('#order_street').val();
		var order_city          = $('#order_city').val();
		var order_state         = $('#order_state').val();
		var order_country       = $('#order_country').val();
		var order_postal_code   = $('#order_postal_code').val();
		var order_latitude      = $('#order_latitude').val();
		var order_longitude     = $('#order_longitude').val();
		var suite               = $('#suite').val();
		var delivery_note       = $('#delivery_note').val();
		var payment_method      = $('#payment_method').val();
		var order_note          = $('#order_note').val();
		var delivery_time       = $('#delivery_time').val();
		var order_type          = $('#order_type').val();
		 var delivery_mode       = $scope.delivery_mode;

		var url = getUrls('place_order_details');
		if(confirm_address!='' && order_city!='' && order_state!='') {
			$('#error_place_order').css('display','none');
			$http.post(url,{
				confirm_address     : confirm_address, 
				street              : order_street,
				city                : order_city, 
				state               : order_state,
				country             : order_country,
				postal_code         : order_postal_code,
				latitude            : order_latitude,
				longitude           : order_longitude,  
				suite               : suite,
				delivery_note       : delivery_note,
				payment_method      : payment_method,
				order_note          : order_note,
				delivery_mode          : delivery_mode,
				
			}).then(function(response) {
				if(response.data.success=='true') {
					var order_id = response.data.order.id;
					var wallet = 0;
					var data_params = {
						order_id       : order_id,
						isWallet       : wallet,
						payment_method : payment_method,
						delivery_time  : delivery_time,
						order_type     : order_type,
						notes     	   : order_note,
						delivery_mode  : delivery_mode,
					};
					$scope.placeOrder(data_params);
				}
			});
		}
		else {
			$('#error_place_order').css('display','block');
		}
	});

	$scope.placeOrder = function(data_params) {
		
		var url = getUrls ('place_order');

		$http.post(url,data_params).then(function(response) {
			$('.place_order_change').removeClass('loading');
			$('#payment-error').addClass('d-none');
			$scope.ajax_loading = false;
			if(response.data.status_code == '1') {
				$('#order_id').val(response.data.order_details.id);
				var url = getUrls('order_track');
				window.location.href = url+'?order_id='+response.data.order_details.id;
			}
			else if(response.data.status_code == '2') {
				$scope.ajax_loading = true;
				$scope.completeCardAuthentication(response.data.client_secret,data_params);
			}
			else if(response.data.status_code == '3') {
				$('#payment-error').text(response.data.status_message);
				$('#payment-error').removeClass('d-none');
			}
			else if(response.data.status_code == '0') {
				$('#payment-error').removeClass('d-none');
				$('#payment-error').text(response.data.status_message);
			}
			else {
				$('#error_place_order').text(response.data.status_message);
				$('#error_place_order').show();
			}
		});
	};

	$scope.add_notes='';
	$scope.order_store_session= function() {
		if($scope.other_restaurant=='yes'){
			$('#myModal').modal();
			return false;
		}
		$('.detail-popup').addClass('loading');
		$('.cart-scroll').addClass('loading');

		$scope.item_count = $scope.item_count;

		var restaurant_id = $scope.restaurant_id;

		$scope.item_notes = $scope.add_notes;

		var index_id = $(this).attr('data-remove');

		var menu_item_id = $scope.menu_item.id;

		var add_cart = getUrls('add_cart');

		$http.post(add_cart,{
			restaurant_id    : restaurant_id,
			menu_data        : $scope.menu_item,
			item_count       :  $scope.item_count,
			item_notes       : $scope.item_notes,
			item_price       : $scope.price,
			individual_price : $scope.individual_price,
		}).then(function(response) {
			$scope.order_data = response.data.cart_detail;

			$('.detail-popup').removeClass('loading');
			$('.cart-scroll').removeClass('loading');

			$('.detail-popup').removeClass('active');
			$('body').removeClass('non-scroll');
			$('#checkout').removeAttr('disabled');
		});
	};

}]);

app.controller('orders_detail', ['$scope', '$http', '$timeout', function ($scope, $http, $timeout) {

	history.pushState(null, null, location.href);
	window.onpopstate = function() {
		history.go(1);
	};

	$('.invoice-btn').click(function() {
		var order_id = $(this).attr('data-id');
		var url = getUrls('order_invoice');
		$http.post(url,{
			order_id    : order_id,
		}).then(function(response) {
			$scope.order_detail = response.data.order_detail;
			$scope.currency_symbol = response.data.currency_symbol;
		});
	});

	$(document).ready(function() {
		var status = $('#order_status').val();
		var display = (parseInt(status) >= 5) ? 'block' : 'none';
		$('.delivery_data').css('display',display);
	});

	$scope.open_cancel_model = function(id){
		$('#open_cancel_model').modal('show');
		$('#cancel_order_id').val(id);
	};

}]);