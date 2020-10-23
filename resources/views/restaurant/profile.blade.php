@extends('template')

@section('main')
<main id="site-content" role="main" ng-controller="profile">
	<div class="partners document-page py-4 px-0">
		@include ('restaurant.navigation')
		<div class="container">
			<div class="profile-tab">
				<ul class="nav nav-tabs justify-content-center text-center mt-2 my-md-4" id="myTab" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="true">
							{{trans('messages.profile.profile')}}
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="document-tab" data-toggle="tab" href="#document" role="tab" aria-controls="document" aria-selected="false">
							{{trans('admin_messages.document')}}
						</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="open_time-tab" data-toggle="tab" href="#open_time" role="tab" aria-controls="open_time" aria-selected="false">
							{{trans('admin_messages.open_time')}}
						</a>
					</li>
				</ul>
				<!-- <h1 class="title align-center">profile</h1> -->

				<div class="tab-content" id="myTabContent">
					<div  class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
						<div class="custom-panel mt-4 mb-5 mx-auto col-md-11 col-lg-8">
							{!! Form::open(['url'=>route('restaurant.profile'),'method'=>'post','class'=>'mt-4' , 'id'=>'profile_form','files' => true])!!}

							<div class="form-group d-md-flex">
								<div class="col-md-4">
									<label>
										{{trans('messages.driver.first_name')}}
										<span class="required">*</span>
									</label>
								</div>

								<div class="col-md-8">
									{!! Form::text('first_name',$basic->first_name,['id'=>'first_name'])!!}
									<span class="text-danger">{{ $errors->first('first_name') }}</span>
								</div>
							</div>
							<div class="form-group d-md-flex">
								<div class="col-md-4">
									<label>
										{{trans('messages.driver.last_name')}}
										<span class="required">*</span>
									</label>
								</div>
								<div class="col-md-8">
									{!! Form::text('last_name',$basic->last_name,['id'=>'last_name'])!!}
									<span class="text-danger">{{ $errors->first('last_name') }}</span>
								</div>
							</div>
							<div class="form-group d-md-flex">
								<div class="col-md-4">
									<label>
										{{trans('messages.profile.email_address')}}
										<span class="required">*</span>
									</label>
								</div>
								<div class="col-md-8">
									{!! Form::text('email',$basic->email,['id'=>'name'])!!}
									<span class="text-danger">{{ $errors->first('email') }}</span>
								</div>
							</div>
							<div class="form-group d-md-flex">
								<div class="col-md-4">
									<label>
										{{trans('messages.driver.phone_number')}}
										<span class="required">*</span>
									</label>
								</div> 
								<div class="col-md-8">
									<div class="d-flex w-100 mobile-error error_clone">
										<div class="select mob-select col-md-3">
										
										<span class="phone_code">+{{$basic->country_code }}</span> 
										<select id="phone_code" name="phone_code" class="form-control">
						                    @foreach ($country as $key => $country)
						                        <option value="{{ $country->phone_code }}" {{ $country->phone_code == $basic->country_code ? 'selected' : '' }} >{{ $country->name }}</option>
						                    @endforeach
						                </select>


										</div>
										{!! Form::text('mobile_number',$basic->mobile_number,['id'=>'mobile_number','placeholder' => trans('messages.driver.phone_number'),'class' =>'col-md-9','data-error-placement'=>"container",'data-error-container'=>".phone_error"])!!}
										
									</div>
									<p class="message_status text-danger"> </p>
									@if($basic->mobile_no_verify!=1)
									<a class="verify_link" href="javascript:void(0)"  ng-click="send_message()">{{trans('messages.restaurant.verify_your_phone_number')}}</a>
									@endif

									<div class="modal fade" id="verify_modal" role="dialog" >
										<div class="modal-dialog">
											<div class="modal-content verify_modal_content">
												<div class="modal-header">
													<button type="button" class="close" data-dismiss="modal">
														<i class="icon icon-close-2"></i>
													</button>
													<h3 class="modal-title">{{trans('messages.restaurant.verification')}}</h3>
												</div>
												<div class="modal-body">
													<p>{{trans('messages.restaurant.verification_code_send_to')}} +<span class="phone_code_val">{{$basic->country_code }}</span> <span class="phone_number_val">{{ $basic->mobile_number }}</span></p>

													{!! Form::text('code','',['id'=>'verify_code','placeholder' => trans('messages.restaurant.verification_code'),'class' =>'col-md-9'])!!}
													<p class="confirn_code text-danger"> </p>
												</div>
												<div class="modal-footer text-right">
													<button type="reset" data-dismiss="modal" class="btn btn-primary theme-color">{{trans('messages.restaurant.cancel')}}</button>
													<button type="button" class="btn btn-theme ml-2" ng-click="verify_mobile_code()">{{trans('messages.restaurant.submit')}}</button>
												</div>
											</div>
										</div>
									</div>

								</div>
							</div>
							<div class="form-group d-md-flex">
								<div class="col-md-4">
									<label>
										{{trans('admin_messages.date_of_birth')}}
										<span class="required">*</span>
									</label>
								</div>
								<div class="col-md-8">
								<input type="text" autocomplete="off" id="dob_datepicker" name='dob' value="{{$basic->date_of_birth?date('m/d/Y',strtotime($basic->date_of_birth)):''}}">
									<span class="text-danger">{{ $errors->first('dob') }}</span>
								</div>
							</div>
							<div class="form-group d-md-flex">
								<div class="col-md-4">
									<label>
										{{trans('admin_messages.restaurant_name')}}
										<span class="required">*</span>
									</label>
								</div>

								<div class="col-md-8">
									{!! Form::text('restaurant_name',$restaurant->name,['id'=>'restaurant_name'])!!}
									<span class="text-danger">{{ $errors->first('restaurant_name') }}</span>
								</div>
							</div>
							<div class="form-group d-md-flex">
								<div class="col-md-4">
									<label>
										{{trans('admin_messages.restaurant_description')}}
										<span class="required">*</span>
									</label>
								</div>
								<div class="col-md-8">
									{!! Form::text('description',$restaurant->description,['id'=>'description'])!!}
									<span class="text-danger">{{ $errors->first('description') }}</span>
								</div>
							</div>
							<div class="form-group d-md-flex">
								<div class="col-md-4">
									<label>
										{{trans('messages.restaurant.price_rating')}}
										<span class="required">*</span>
									</label>
								</div>
								<div class="col-md-8">
									{!! Form::select('price_rating',priceRatingList(),@$restaurant->price_rating, ['placeholder' => trans('admin_messages.select'),'class'=>'','id'=>'price_rating']); !!}


									<span class="text-danger">{{ $errors->first('price_rating') }}</span>
								</div>
							</div>
							<div class="form-group cuisine-error">
								<label class="col-12 col-form-label">
									@lang('admin_messages.cuisine')
									<span class="required">*</span>
								</label>
								<div class="col-12 p-0 cuisine_err">
									<div class="form-group mt-3 cuisine row ">
										@foreach($cuisine as $cuisine_key => $cuisine_value)
										<div class="form-check col-md-6">
											<label class="form-check-label">
												{!! Form::checkbox('cuisine[]',$cuisine_key,in_array($cuisine_key,$restaurant_cuisine), ['class'=>'form-check-input','data-error-placement'=>"container" ,'data-error-container'=>".cuisine_error"]); !!}
												<span class="form-check-sign">
													<span class="check"></span>
												</span>
												{{$cuisine_value}}
											</label>
										</div>
										@endforeach
										<span class="text-danger">{{ $errors->first('cuisine') }}</span>
									</div>
									<span class="cuisine_error"> </span>
								</div>
							</div>

							<div class="form-group d-md-flex">
								<div class="col-md-4">
									<label>
										{{trans('messages.profile.location')}}
										<span class="required">*</span>
									</label>
								</div>
								<div class="col-md-8">
									{!! Form::text('address',isset($address->address)?$address->address:'',['id'=>'location_value'])!!}
									<span class="text-danger">{{ $errors->first('address') }}</span>
									<div id="location_map" class="mt-4" style="width:100%;height:300px;">
									</div>
								</div>
							</div>

							<div class="form-group cuisine-error">
								<label class="col-12 col-form-label">
									@lang('admin_messages.delivery_mode')
									<span class="required">*</span>
								</label>
								<div class="col-12 p-0 cuisine_err">
									<div class="form-group mt-3 cuisine row ">
										@foreach($delivery_mode as $delivery_key => $delivery_value)
										<div class="form-check col-md-6">
											<label class="form-check-label">
												{!! Form::checkbox('delivery_mode[]',$delivery_key,in_array($delivery_key,@explode(',',$restaurant->delivery_mode)), ['class'=>'form-check-input','data-error-placement'=>"container" ,'data-error-container'=>".cuisine_error",
												'onchange'=>"deliveryChange()"]); !!}
												<span class="form-check-sign">
													<span class="check"></span>
												</span>
												{{$delivery_value}}
											</label>
										</div>
										@endforeach
										<span class="text-danger">{{ $errors->first('delivery_mode') }}</span>
									</div>
									<span class="cuisine_error"> </span>
								</div>
							</div>

							<!-- Delivery Fee -->
							<div class="form-group required-list class_delivery_fee" ng-init="is_free={{ isset($restaurant->is_free) ? $restaurant->is_free:1 }}" style="{{ in_array('2',@explode(',',@$restaurant->delivery_mode)) ? 'display:flex;' : 'display:none;' }}">
								<div class="col-md-4">
									<label class="mt-2">
										{{trans('messages.new_changes.delivery_fee')}}
										<span class="required">*</span>
									</label>
								</div>
								<div class="col-md-8">
									<ul class="is_free-container">
						                <li>
						                 	<label>
						                    	<input type="radio" class="require" name="is_free" value="1" ng-model="is_free" ng-checked="is_free == '1'" data-error-placement = "container" data-error-container= ".is_free-container">
						                    	@lang('messages.new_changes.free')
						                  </label>
						                </li>
						                <li>
						                  	<label>
						                    	<input type="radio" class="optional" name="is_free" value="0" ng-model="is_free" ng-checked="is_free == '0'" data-error-placement = "container" data-error-container= ".is_free-container">
						                    	@lang('messages.new_changes.cost')
						                    	
						                  	</label>
						                </li>
						            </ul>

									<div class="row mt-3" style="" ng-show="is_free == '0'">
										<div class="col-md-3">
											<label>
												{{trans('messages.new_changes.fee')}}
											</label>
										</div>
										<div class="col-md-9">
										{!! 
											Form::number(
											'delivery_fee',
											isset($restaurant->delivery_fee) ? $restaurant->delivery_fee:'',
											[
												'id' => 'delivery_fee',
												'placeholder' => trans('messages.new_changes.fee_per_km'),
												'min' => 1
											])
										!!}
										</div>
									</div>
									<span class="text-danger">
										{{ $errors->first('delivery_fee') }}
										<!-- {{ $errors->first('is_free') }} -->
									</span>
								</div>
							</div>

							<div class="form-group d-md-flex">
								<input type="hidden" value="{{isset($address->latitude)?$address->latitude:''}}" ng-model="latitude"  name="latitude" id="latitude">
								<input type="hidden" value="{{isset($address->longitude)?$address->longitude:''}}" ng-model="longitude"  name="longitude" id="longitude">
								<input type="hidden" value="{{isset($address->city)?$address->city:''}}" ng-model="city"  name="city" id="city">
								<input type="hidden" value="{{isset($address->country)?$address->country:''}}" ng-model="country"  name="country"  id="country">
								<input type="hidden" value="{{isset($address->state)?$address->state:''}}" ng-model="state"  name="state" id="state">
								<input type="hidden" value="{{isset($address->street)?$address->street:''}}" ng-model="street"  name="street" id="street">
								<input type="hidden" value="{{isset($address->postal_code)?$address->postal_code:''}}" ng-model="postal_code"  name="postal_code" id="postalcode">
								<input type="hidden" value="{{isset($address->country_code)?$address->country_code:''}}" ng-model="country_code"  name="country_code" id="countrycode">
							</div>

							<div class="form-group d-md-flex" ng-init="">
								<div class="col-md-4">
									<label>
										{{trans('messages.restaurant.restaurant_logo')}}
										<span class="required">*</span>
										<span class="d-block">({{trans('messages.store.recommended')}} {{trans('admin_messages.size')}} : 370*230)</span>
									</label>
								</div>
								<div class="col-md-8">
									<div class="file-input">
										<input type="file" name="restaurant_logo" class="form-control-file" id="inputLogoFile"  onchange="readUrl(this)" data-title=" " style="visibility:hidden;">
									<a class="choose_file_type logo_choose" id="chooses_logo_file"><span id="logo_name"> @lang('messages.profile.choose_file') </span></a>
									<span class="upload_logo_text" id="logo_file_text_1"></span>
									</div>
									<span class="text-danger">{{ $errors->first('restaurant_logo') }}</span>
									<img src="{{ $restaurant->restaurant_logo }}" class="img-thumbnail mt-3">
								</div>
							</div>

							<div class="form-group d-md-flex" ng-init="latitude='{{ isset($address->latitude)?$address->latitude:''}}';longitude='{{ isset($address->longitude)?$address->longitude:''}}';verify_code=''">
								<div class="col-md-4">
									<label>
										{{trans('messages.restaurant.restaurant_image')}}
										<span class="d-block">({{trans('messages.store.recommended')}} {{trans('admin_messages.size')}} : 1350*310)</span>
									</label>
								</div>
								<div class="col-md-8">
									<div class="file-input">
										<input type="file" name="banner_image" class="form-control-file" id="inputFile"  onchange="readUrl(this)" data-title=" " style="visibility:hidden;">
									<a class="choose_file_type banner_choose" id="chooses_file"><span id="banner_name"> @lang('messages.profile.choose_file') </span></a>
									<span class="upload_text" id="file_text_1"></span>
									</div>
									<span class="text-danger">{{ $errors->first('banner_image') }}</span>
									<img src="{{ $restaurant->restaurant_image }}" class="img-thumbnail mt-3">
								</div>
							</div>

							<div ng-init="country='{{ isset($address->country)?$address->country:'' }}';postal_code='{{ isset($address->postal_code)?$address->postal_code:''}}';city='{{ isset($address->city)?$address->city:''}}';state='{{ isset($address->state)?$address->state:''}}';street='{{ isset($address->street)?$address->street:''}}';country_code='{{ isset($address->country_code)?$address->country_code:''}}';">
							</div>
							<div class="profile-submit text-right mt-4 pt-4">
								<button type="submit" class="btn btn-theme" id="profile_save">{{trans('messages.profile.save')}}</button>
							</div>
							{!! Form::close() !!}
						</div>
					</div>
					<div class="tab-pane fade" id="document" role="tabpanel" aria-labelledby="document-tab">
						<div class="custom-panel mt-4 mb-5 mx-auto col-md-11 col-lg-8">
							{!! Form::open(['url'=>route('restaurant.update_documents'),'method'=>'post','class'=>'mt-2' , 'id'=>'restaurant_documents','files' => true])!!}
							@csrf
			<!--
			<div class="form-group d-md-flex">
						<div class="col-md-5">
							<label>SSI copy</label>
						</div>
						<div class="col-md-7">
							<div class="file-input">
								<input type="file" class="form-control-file" name="document[ssi]" id="inputFile" accept="image/*" onchange="readUrl(this)" data-title=" " >

							</div>
							<h5>Allowed Files:png, jpeg, jpg</h5>

						</div>
					</div>
				-->
				<div class="" ng-init="all_document={{json_encode(old('document')?: @$documents)}};errors = {{json_encode($errors->getMessages())}}">
					<h3 class="col-12 text-center text-capitalize col-form-label">
						{{trans('admin_messages.documents')}}
					</h3>
				</div>

				<div ng-repeat="document in all_document" ng-cloak>
					<div class="d-md-flex mt-3">
						<label class="col-md-5 col-form-label">
							{{trans('admin_messages.document_name')}}
							<span class="required">*</span>
						</label>
						<div class="col-md-7">
							<div class="form-group">
								<input type="text" name="document_name[]" ng-model="document.name" class="form-control" id="document_name@{{$index}}">
								<span class="text-danger">@{{ errors['document.'+$index+'.name'][0] }}</span>
							</div>
						</div>
					</div>
					<div class="d-md-flex mt-3">
						<label class="col-md-5 col-form-label">
							{{trans('admin_messages.document')}}
							<span class="required">*</span>
						</label>
						<div class="col-md-7">
							<input type="file" name="document_file[]" class="form-control" id="document_file@{{$index}}" data="@{{document.document_id?document.document_id:'null'}}" style="visibility:hidden;">
							<a class="choose_file_type document_choose" id="@{{$index}}" onclick="documentImage(id)"><span id="doc_name">{{trans('messages.profile.choose_file')}}</span></a>
							<span class="upload_text" id="file_text"></span>
                                  <div style="">
                                  	<input type="hidden" name="document_id[]" value="@{{document.document_id}}" ng-model="document.document_id" >
                                  </div>

                                  <div class="fileinput-new mt-3 thumbnail">
                                  	<img class="img-thumbnail" ng-show="document.file['restaurant_document'] && document.file['file_extension']!='pdf'" src="@{{ document.file['restaurant_document']}}" alt="...">
                                  	<a href="@{{ document.file['restaurant_document']}}" ng-show="document.file['restaurant_document'] && document.file['file_extension']=='pdf'">
                                  		@{{ document.file['name']}}
                                  	</a>
                                  </div>
                                  <p class="logo_error"></p>
                                  <span class="text-danger">@{{ errors['document.'+$index+'.file'][0] }}</span>
                              </div>
                          </div>

                          <div class="col-12 text-right" ng-show="all_document.length > 1">
                          	<a href="javascript:void(0)" ng-click="delete_document($index,document.document_id)" class="icon icon-rubbish-bin text-danger">
                          	</a>
                          </div>
                      </div>

                      <div class="col-12 text-right mt-2">
                      	<a href="javascript:void(0)" ng-click="add_document()" class="theme-color">
                      		+{{trans('admin_messages.add')}}
                      	</a>
                      </div>

                      <div class="profile-submit text-right mt-4 pt-4">
                      	<button type="submit" class="btn btn-theme">{{trans('messages.profile.save')}}</button>
                      </div>
                      {!! Form::close() !!}
                  </div>
              </div>
              <div class="tab-pane fade" id="open_time" role="tabpanel" ng-init="open_time_timing={{json_encode($open_time)}};day_name ={{ json_encode(day_name()) }}">
              	<div class="custom-panel mt-4 mb-5 mx-auto col-md-11 col-lg-8 rest_prof">
              		{!! Form::open(['url'=>route('restaurant.update_open_time'),'method'=>'post','class'=>'mt-1' , 'id'=>'open_time_form','files' => true])!!}
              		@csrf
              		<div class="mb-4 mb-md-3 d-md-flex align-items-start select-day menu-view" ng-repeat="open_time in open_time_timing">
              			<div class="select">
              				<select name="day[]" ng-model="open_time.day" id="select_day_@{{$index}}">
              					<option value="">{{ trans('messages.restaurant_dashboard.select_a_day') }}</option>
              					<option value="@{{key}}" ng-selected="open_time.day==key" ng-repeat="(key,value) in day_name track by $index" ng-if="( key | checkKeyValueUsedInRestaurant : 'day': open_time_timing) || open_time.day==key">@{{value}}</option>
              					<!-- ng-if="( key | checkKeyValueUsedInStack : 'day': open_time_timing) || open_time.day==key " -->
              				</select>
              			</div>
              			<input type="hidden" name="time_id[]" value="@{{open_time.id}}">
              			<div class="added-times d-md-flex mt-2 mt-md-0 ml-md-3 align-items-start">
              				<div class="d-flex align-items-start justify-content-between select-time">
              					<div class="select">
              						{!! Form::select('start_time[]',time_data('time'),'', ['placeholder'=>trans('admin_messages.select'),'ng-model'=>'open_time.orginal_start_time', 'id'=>'start_time_@{{$index}}','class'=>'start_time', 'data-index'=>'@{{$index}}', 'data-end_time'=>'@{{open_time.orginal_end_time}}']); !!}
              					</div>
              					<div class="m-2">{{ trans('messages.store.to') }}</div>
              					<div class="select">
              						{!! Form::select('end_time[]',time_data('time'),'', ['placeholder'=>trans('admin_messages.select'),'ng-model'=>'open_time.orginal_end_time','id'=>'end_time_@{{$index}}','class'=>'end_time' ,'data-index'=>'@{{$index}}']); !!}
              					</div>
              				</div>
              				<div class="d-flex align-items-start mt-2 mt-md-0 select-status">
              					<div class="select ml-md-3">
              						{!! Form::select('status[]',['1'=>trans('admin_messages.active'),'0'=>trans('admin_messages.inactive')],'', ['placeholder'=>trans('admin_messages.select'),'ng-model'=>'open_time.status','id'=>'status_@{{$index}}','class'=>'status' ,'data-index'=>'@{{$index}}']); !!}
              					</div>
              					<i ng-show="open_time_timing.length > 1" class="icon icon icon-rubbish-bin d-inline-block m-2 mr-0 text-danger" ng-click="delete_open_time($index)"></i>
              				</div>
              			</div>
              		</div>
              		<div class="mt-4">
              			<a href="javascript:void(0)" class="theme-color" ng-click="add_open_time()" ng-show="open_time_timing.length < 7">
              				<i class="icon icon-add mr-2"></i>
              				{{trans('messages.restaurant.add_more')}}
              			</a>
              			<center><p id="saving_data" style="display: none;color: green;">{{trans('messages.restaurant.saving')}}..</p></center>
              			<div class="mt-3">
              				<button type="submit" class="btn btn-theme text-uppercase" id="timing_save">{{trans('messages.profile.save')}}</button>
              			</div>
              		</div>
              		{!! Form::close() !!}
              	</div>
              </div>
          </div>
      </div>
  </div>
</main>
@stop

@push('scripts')
<script type="text/javascript">

	if (window.location.hash) {
		$("a[href='" + window.location.hash + "']").tab('show');
	}

	$('#chooses_file').click(function(){
    	$('#inputFile').trigger('click');
    	$('#inputFile').change(function(evt) {
    		var fileName = $(this).val().split('\\')[$(this).val().split('\\').length - 1];
        		$('.banner_choose').css("background-color","#43A422");
        		$('.banner_choose').css("color","#fff");
        		
        		$('#banner_name').text(Lang.get('js_messages.file.file_attached'));
        		$('#file_text_1').text(fileName);
        		$('span.upload_text').attr('title',fileName)
    		});
    });

	$('#chooses_logo_file').click(function(){
    	$('#inputLogoFile').trigger('click');
    	$('#inputLogoFile').change(function(evt) {
    		var fileName = $(this).val().split('\\')[$(this).val().split('\\').length - 1];
        		$('.logo_choose').css("background-color","#43A422");
        		$('.logo_choose').css("color","#fff");
        		
        		$('#logo_name').text(Lang.get('js_messages.file.file_attached'));
        		$('#logo_file_text_1').text(fileName);
        		$('span.upload_logo_text').attr('title',fileName)
    		});
    });

    function documentImage(input) {

    	$('#document_file'+input).trigger('click');
    	//$('.document_choose').css("background-color","yellow");
    	$('#document_file'+input).change(function(evt) {
    		var fileName = $(this).val().split('\\')[$(this).val().split('\\').length - 1];
        		$('.document_choose').css("background-color","#43A422");
        		$('.document_choose').css("color","#fff");
        		

        		$('#doc_name').text(Lang.get('js_messages.file.file_attached'));
        		$('#file_text').text(fileName);
        		$('span.upload_text').attr('title',fileName)
    		});


	}



	function readUrl(input) {
		if (input.files && input.files[0]) {
			let reader = new FileReader();
			reader.onload = (e) => {
				let imgData = e.target.result;
				let imgName = input.files[0].name;
				input.setAttribute("data-title", imgName);
				console.log(e.target.result);
			}
			reader.readAsDataURL(input.files[0]);
		}
	}


	$('#location_map').locationpicker({
		location: {
			latitude: '{{isset($address->latitude)?$address->latitude:''}}',
			longitude: '{{isset($address->longitude)?$address->longitude:''}}'
		},
		radius: 0,
		inputBinding: {
			latitudeInput: $('#latitude'),
			longitudeInput: $('#longitude'),
			cityInput: $('#city'),
			countryInput: $('#country'),
			stateInput: $('#state'),
			streetInput: $('#street'),
			locationNameInput: $('#location_value'),
			postalcodeNameInput: $('#postalcode'),
			countrycodeNameInput: $('#countrycode'),
		},
		enableAutocomplete: true,
		onchanged: function (currentLocation, radius, isMarkerDropped) {
		}
	});



  	deliveryChange();
  	function deliveryChange() {
      	var newArray = [];
      	$("input:checkbox[name='delivery_mode[]']:checked").each(function(){
        	newArray.push($(this).val());
      	});
      	// console.log(newArray);

      	if( newArray.includes("2") ) {
        	$('.class_delivery_fee').show(10);
        	$('.class_delivery_fee').addClass('d-md-flex');
    	}
      	else {
        	$('.class_delivery_fee').hide(10);
        	$('.class_delivery_fee').removeClass('d-md-flex');
        }
    }

</script>
@endpush