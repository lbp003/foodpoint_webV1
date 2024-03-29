@if (Route::current()->uri() == 'driver/signup' || Route::current()->uri() == 'driver'  & Route::current()->uri() !== 'login')
<footer class="driver driver-sign-footer" ng-controller="driver_footer">

	<div class="footer-info col-12">
		<div class="copyright py-4 mt-md-3">
			<div class="d-flex flex-wrap justify-content-center">
				<div class="col-12 col-md-4 text-center order-3 order-md-1 mt-4 mt-md-0">
					<!-- <p>© 2018 Trioangle Technologies Inc.</p> -->
					<div class="select_lang mt-3 select"> 
					{!! Form::select('language',$language, (Session::get('language')) ? Session::get('language') : $default_language[0]->value, ['class' => 'language-selector footer-select', 'aria-labelledby' => 'language-selector-label', 'id' => 'driver_language_footer']) !!}
				</div>
				</div>
				<div class="col-12 col-md-3 order-1 order-md-2">
					<ul>
						@if(@$static_pages_changes[0] !='')
							@foreach(@$static_pages_changes[0] as $page_url)
							<li>
								<a class="theme-color" href="{{route('page',$page_url->url)}}">
									{{$page_url->name}}
								</a>
							</li>
							@endforeach
						@endif
						<li><a class="theme-color"  href="{{route('help_page',current_page())}}">{{trans('messages.footer.help')}}</a></li>

					</ul>
				</div>


				@if(@$static_pages_changes[1]!='')
				<div class="col-12 col-md-3 order-2 order-md-2">
					<ul>
						@foreach(@$static_pages_changes[1] as $page_url)
						<li>
							<a class="theme-color" href="{{route('page',$page_url->url)}}">
								{{$page_url->name}}
							</a>
						</li>
						@endforeach
					</ul>
				</div>
				@endif
			</div>
		</div>
	</div>
	 <div class="copyright py-3">
			<div class="col-12 text-center mt-3">
				<p>© 2018 Trioangle Technologies Inc.</p>
			</div>
		</div>
</footer>
@endif

@if (Route::current()->uri() == 'driver/login' || Route::current()->uri() == 'driver/login_session' || Route::current()->uri() == 'driver/profile' || Route::current()->uri() == 'driver/payment' || Route::current()->uri() == 'driver/invoice' || Route::current()->uri() == 'driver/trips' || Route::current()->uri() == 'driver/trip_detail' || Route::current()->uri() == 'driver/documents' || Route::current()->uri() == 'driver/daily_payment/{date}'|| Route::current()->uri() == 'driver/detail_payment/{date}'|| Route::current()->uri() == 'driver/vehicle_details' || Route::current()->uri() == 'driver/documents/{id}' || Route::current()->uri() == 'driver/password' ||Route::current()->uri() == 'driver/forgot_password')
<footer class="driver driver-sign-footer login" ng-controller="footer">
	<div class="footer-banner mt-5">
		<svg style="display:block;max-width:100%;margin:0 auto;" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1560 197" height="" width="1560" preserveAspectRatio="xMinYMax meet" data-reactid="216"><defs data-reactid="217"><pattern id="a" data-name="2 3 - black" width="5" height="5" patternTransform="translate(726 27)" patternUnits="userSpaceOnUse" viewBox="0 0 5 5" data-reactid="218"><path fill="none" d="M0 0h5v5H0z" data-reactid="219"></path><path fill="none" d="M0 0h5v5H0z" data-reactid="220"></path><path fill="none" d="M0 0h5v5H0V0z" data-reactid="221"></path><path d="M2 2H0V0h2v2z" data-reactid="222"></path></pattern><pattern id="b" data-name="SVGID 1" width="5" height="5" patternTransform="translate(841.4 310.4)" patternUnits="userSpaceOnUse" viewBox="0 0 5 5" data-reactid="223"><path fill="none" d="M0 0h5v5H0z" data-reactid="224"></path><path fill="none" d="M0 0h5v5H0z" data-reactid="225"></path><path fill="none" d="M0 0h5v5H0V0z" data-reactid="226"></path><path d="M2 2H0V0h2v2z" data-reactid="227"></path></pattern><pattern id="c" data-name="2 3 - black" width="5" height="5" patternTransform="translate(726 30)" patternUnits="userSpaceOnUse" viewBox="0 0 5 5" data-reactid="228"><path fill="none" d="M0 0h5v5H0z" data-reactid="229"></path><path fill="none" d="M0 0h5v5H0z" data-reactid="230"></path><path fill="none" d="M0 0h5v5H0V0z" data-reactid="231"></path><path d="M2 2H0V0h2v2z" data-reactid="232"></path></pattern></defs><path d="M1498.22 54H1361.5a5.5 5.5 0 0 1 0-11h50.6a23.92 23.92 0 0 0 16.9-7 23.92 23.92 0 0 1 16.9-7h19.33a194.76 194.76 0 0 0-100.59-28H1137c-11.9 0-23.2 3.9-32 12a49.47 49.47 0 0 1-32.8 13H969.9c-22.3 0-43.1 7.5-58.9 22s-37.7 23-60 23H720a137.73 137.73 0 0 0-94 37c-26.1 24.4-61.8 38-98.9 38H510c-26.2 0-50.7 10.2-70 28l-26 23h1146a195.13 195.13 0 0 0-61.78-143zM1297 66a17 17 0 1 1 17-17 17 17 0 0 1-17 17zm162 34h-112a5 5 0 0 1 0-10h15.3a30.48 30.48 0 0 0 21.7-9 30.66 30.66 0 0 1 21.7-9h53.3a14 14 0 0 1 0 28z" fill="#3a939a" data-reactid="233"></path><path d="M1536 173a148 148 0 0 0-104-43h-111v67h239zm-613-21h-17v-38a73.89 73.89 0 0 0 17-2v40zm271-80a125.64 125.64 0 0 1 90 37.2V153h-90V72zm-172-1v14l-14-14h14zM574 197h-45c16.4 0 31.9-5.6 45-15v15z" data-reactid="234"></path><path fill="#fff" d="M840 32v9h-10v156h48V42h5V32h-43zm115 120h-67l45 45h22v-45zm-32-61h32v61h-32z" data-reactid="235"></path><path fill="#d6d6d6" d="M928 98h27v2h-27zm0 6h27v2h-27zm0 6h27v2h-27zm0 6h27v2h-27zm0 6h27v2h-27zm0 6h27v2h-27zm0 6h27v2h-27zm0 6h27v2h-27z" data-reactid="236"></path><path fill="#fff" d="M932 85h36v6h-36z" data-reactid="237"></path><path fill="#daea53" d="M932 85l19-19h17v19h-36z" data-reactid="238"></path><path fill="#6cb120" d="M1022 85l-19-19h-35v19h54z" data-reactid="239"></path><path fill="#daea53" d="M979 79v-6h-6l6 6zm13 0v-6h-6l6 6zm13 0v-6h-6l6 6z" data-reactid="240"></path><path fill="#fff" d="M951 63h17v3h-17zm104 107l-55-55h55v55z" data-reactid="241"></path><path fill="#d6d6d6" d="M1045 121h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm16 13h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm16 13h2v7h-2zm-14 1l-1-1h1v1zm8 6h-1l-1-1v-6h2v7z" data-reactid="242"></path><path fill="#6cb120" d="M679 140h21v8h-21zm0-14h21v8h-21zm0-14h21v8h-21zm0-14v8h24l-8-8h-16z" data-reactid="243"></path><path fill="#d6d6d6" d="M679 134h21v6h-21zm0-14h21v6h-21zm0-14h17v6h-17z" data-reactid="244"></path><path fill="#daea53" d="M679 140v8h-55l8-8h47zm0-14v8h-44l8-8h36zm0-14v8h-35l8-8h27zm0-14v8h-25l8-8h17z" data-reactid="245"></path><path fill="#fff" d="M643 134h36v6h-36zm9-14h27v6h-27zm10-14h17v6h-17zm634 87l-40-40h-10v-38h50v78z" data-reactid="246"></path><path fill="#6cb120" d="M1296 107v8h26l-8-8h-18z" data-reactid="247"></path><path fill="#daea53" d="M1296 107v8h-50l8.4-8h41.6z" data-reactid="248"></path><path fill="#d6d6d6" d="M1290 120h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm40 13h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm40 13h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm-494 16h74v35h-74z" data-reactid="249"></path><path fill="url(#a)" d="M756 162h74v35h-74z" data-reactid="250"></path><path d="M756 32v48h-17v6h-25a14 14 0 0 0-14 14v97h56V86h32V0zm212 17h2v14h-2z" fill="#fff" data-reactid="251"></path><path fill="#6cb120" d="M970 49h6v2h-6z" data-reactid="252"></path><path fill="#fff" d="M985 49h2v14h-2z" data-reactid="253"></path><path fill="#6cb120" d="M987 49h6v2h-6z" data-reactid="254"></path><path fill="#fff" d="M1001 49h2v14h-2z" data-reactid="255"></path><path fill="#6cb120" d="M1003 49h6v2h-6z" data-reactid="256"></path><path fill="#d6d6d6" d="M749 167h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm16 13h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm155-28h18V42h-5V32h-18v10h-5v155h55l-45-45z" data-reactid="257"></path><path fill="url(#b)" d="M603 148h41v49h-41z" data-reactid="258"></path><path fill="#d6d6d6" d="M603 148v49h97v-49h-97zM840 18h2v14h-2zm6 0h2v14h-2z" data-reactid="259"></path><path d="M1045 13a13.38 13.38 0 0 0-13 13h26a13.38 13.38 0 0 0-13-13z" fill="#daea53" data-reactid="260"></path><path d="M1045 13v13h13a13.38 13.38 0 0 0-13-13z" fill="#6cb120" data-reactid="261"></path><path fill="#fff" d="M1047 111V29h-2v-3h-20v3h-3v82h25z" data-reactid="262"></path><path fill="#d6d6d6" d="M1041 34h2v7h-2zm0 11h2v7h-2zm0 11h2v7h-2zm0 11h2v7h-2zm0 11h2v7h-2zm-5-44h2v7h-2zm0 11h2v7h-2zm0 11h2v7h-2zm0 11h2v7h-2zm0 11h2v7h-2zm-5-44h2v7h-2zm0 11h2v7h-2zm0 11h2v7h-2zm0 11h2v7h-2zm0 11h2v7h-2zm-5-44h2v7h-2zm0 11h2v7h-2zm0 11h2v7h-2zm0 11h2v7h-2zm0 11h2v7h-2z" data-reactid="263"></path><path fill="#fff" d="M1142 38h26v100h-26z" data-reactid="264"></path><path d="M1194 138h-26V38a26 26 0 0 1 26 26v74z" fill="#d6d6d6" data-reactid="265"></path><path fill="#fff" d="M1123 153h61v44h-61z" data-reactid="266"></path><path fill="#d6d6d6" d="M1184 153h72v44h-72z" data-reactid="267"></path><path d="M1199 166a3 3 0 0 0-3 3v28h6v-28a3 3 0 0 0-3-3zm14 0a3 3 0 0 0-3 3v28h6v-28a3 3 0 0 0-3-3zm14 0a3 3 0 0 0-3 3v28h6v-28a3 3 0 0 0-3-3zm14 0a3 3 0 0 0-3 3v28h6v-28a3 3 0 0 0-3-3z" fill="#6cb120" data-reactid="268"></path><path d="M1145 166a3 3 0 0 0-3 3v28h6v-28a3 3 0 0 0-3-3zm14 0a3 3 0 0 0-3 3v28h6v-28a3 3 0 0 0-3-3zm14 0a3 3 0 0 0-3 3v28h6v-28a3 3 0 0 0-3-3z" fill="#d6d6d6" data-reactid="269"></path><path d="M727 162v-4h5a17.39 17.39 0 0 0 12-5l2-2h10v11h-29z" fill="#daea53" data-reactid="270"></path><path d="M830 162v-4h-4c-4.6 0-9.8-1.8-13-5l-2-2h-55v11h74zm354-9h72a50.17 50.17 0 0 0-36-15 50.83 50.83 0 0 0-36 15z" fill="#6cb120" data-reactid="271"></path><path fill="#d6d6d6" d="M1300 197l-44-44v44h44zM1142 68h26v2h-26zm0 6h26v2h-26zm0 6h26v2h-26zm0 6h26v2h-26zm0 6h26v2h-26zm0 6h26v2h-26zm0 6h26v2h-26zm0 6h26v2h-26z" data-reactid="272"></path><path fill="#6cb120" d="M1168 68h26v2h-26zm0 6h26v2h-26zm0 6h26v2h-26zm0 6h26v2h-26zm0 6h26v2h-26zm0 6h26v2h-26zm0 6h26v2h-26zm0 6h26v2h-26z" data-reactid="273"></path><path d="M1168 41v26h26a26.08 26.08 0 0 0-26-26z" transform="translate(0 -3)" fill="url(#c)" data-reactid="274"></path><path fill="#d6d6d6" d="M1335 162h35v35h-35z" data-reactid="275"></path><path fill="url(#a)" d="M1335 162h35v35h-35z" data-reactid="276"></path><path fill="#daea53" d="M1335 162h-26l13-13 13 13z" data-reactid="277"></path><path fill="#6cb120" d="M1357 149h-35l13 13h35l-13-13zm-493 30v2a5.06 5.06 0 0 1-1.7 3.3l-2 2-.3.3V179h-2v18h2v-5.5a6.29 6.29 0 0 1 1.7-3.8l2-2a7 7 0 0 0 2.3-4.7v-2h-2zm77 7h2v11h-2zm-227 0h2v11h-2z" data-reactid="278"></path><path fill="#d6d6d6" d="M968 63h35v3h-35z" data-reactid="279"></path><path d="M490 197a42.44 42.44 0 0 1-30.5-12.9C449.6 174.2 436 168 422 168h-38c-8 0-15.3-2.3-21-8a31.79 31.79 0 0 0-22-9h-80a41.11 41.11 0 0 1-29-12 41.11 41.11 0 0 0-29-12H100a72.29 72.29 0 0 0-51 21L0 197h490z" data-reactid="280"></path><path d="M1297 27a22 22 0 1 0 22 22 22 22 0 0 0-22-22zm0 39a17 17 0 1 1 17-17 17 17 0 0 1-17 17z" fill="#3a939a" data-reactid="281"></path><path fill="#6cb120" d="M647 151v32h2v-30l-2-2zm8 8v24h2v-22l-2-2zm8 8v16h2v-14l-2-2zm8 8v8h2v-6l-2-2z" data-reactid="282"></path><path d="M603 148h-17.8a11.18 11.18 0 0 0-11.2 11.16V197h29v-49z" fill="#fff" data-reactid="283"></path><path fill="#6cb120" d="M685 151h2v32h-2zm7 0h2v32h-2zM883 48h7v2h-7zm11 0h7v2h-7zm-11 6h7v2h-7zm11 0h7v2h-7zm-11 6h7v2h-7zm11 0h7v2h-7zm-11 6h7v2h-7zm11 0h7v2h-7zm-11 6h7v2h-7zm11 0h7v2h-7zm-11 6h7v2h-7zm11 0h7v2h-7zm-11 6h7v2h-7zm11 0h7v2h-7zm-11 6h7v2h-7zm11 0h7v2h-7zm-11 6h7v2h-7zm11 0h7v2h-7zm-11 6h7v2h-7zm11 0h7v2h-7zm-11 6h7v2h-7zm11 0h7v2h-7zm-11 6h7v2h-7zm11 0h7v2h-7zm-11 6h7v2h-7zm11 0h7v2h-7zm-11 6h7v2h-7zm11 0h7v2h-7zm-11 6h7v2h-7zm11 0h7v2h-7zm-11 6h7v2h-7zm11 0h7v2h-7zm-11 6h7v2h-7zm11 0h7v2h-7zm229-6h19v15h-19z" data-reactid="284"></path><path d="M1220 138h-78v15h42a50.83 50.83 0 0 1 36-15z" fill="#daea53" data-reactid="285"></path><path fill="#d6d6d6" d="M1335 173h-13v-58h-26v82h39v-24z" data-reactid="286"></path><path fill="#6cb120" d="M1316 120h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm16 13h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm16 13h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2z" data-reactid="287"></path><path d="M826 158h4v-44h-12v42.3a20.53 20.53 0 0 0 8 1.7z" data-reactid="288"></path><path d="M813 153a16.56 16.56 0 0 0 4.3 3h.7V0h-30v86h-32v65h55zm-99-60h42v2h-42zm0 6h42v2h-42zm0 6h42v2h-42zm0 6h42v2h-42zm0 6h42v2h-42zm0 6h42v2h-42zm0 6h42v2h-42zm0 6h42v2h-42z" fill="#d6d6d6" data-reactid="289"></path><path fill="#6cb120" d="M756 93h34v2h-34zm0 6h34v2h-34zm0 6h34v2h-34zm0 6h34v2h-34zm0 6h34v2h-34zm0 6h34v2h-34zm0 6h34v2h-34zm0 6h34v2h-34zM796 0h2v137h-2zm6 0h2v137h-2zm6 0h2v137h-2z" data-reactid="290"></path><path fill="#fff" d="M644 148h35v35l-35-35z" data-reactid="291"></path><path fill="#d6d6d6" d="M649 153v-2h-2l2 2zm8 8v-10h-2v8l2 2zm8 8v-18h-2v16l2 2zm8 8v-26h-2v24l2 2z" data-reactid="292"></path><path d="M1123 72h19v66h-19z" data-reactid="293"></path><path fill="#d6d6d6" d="M1062 29v-3h-17v3h2v80l-18-18h-7v-6h-54v6h-13v106h100v-27l-55-55h65V29h-3z" data-reactid="294"></path><path fill="#6cb120" d="M1060 34h2v7h-2zm0 11h2v7h-2zm-5-11h2v7h-2zm0 11h2v7h-2zm-5-11h2v7h-2zm0 11h2v7h-2zm-95 53h74v2h-74zm0 6h74v2h-74zm0 6h74v2h-74zm0 6h45v2h-45zm0 6h45v2h-45zm0 6h45v2h-45zm0 6h45v2h-45zm0 6h45v2h-45z" data-reactid="295"></path><path d="M1024.3 158.6a2 2 0 0 0-3.3 1.4v11l-12.7-12.4a2 2 0 0 0-3.3 1.4v11l-12.7-12.4a2 2 0 0 0-3.3 1.4v11l-12.7-12.4a2 2 0 0 0-3.3 1.4v37h64v-26z" fill="#fff" data-reactid="296"></path><path fill="#d6d6d6" d="M977 177h7v2h-7zm11 0h7v2h-7zm11 0h7v2h-7zm11 0h7v2h-7zm-33 6h7v2h-7zm11 0h7v2h-7zm11 0h7v2h-7zm11 0h7v2h-7zm-33 6h7v2h-7zm11 0h7v2h-7zm11 0h7v2h-7zm11 0h7v2h-7z" data-reactid="297"></path><path fill="#6cb120" d="M1043 177h7v2h-7zm0 6h7v2h-7zm0 6h7v2h-7z" data-reactid="298"></path><path fill="#d6d6d6" d="M1021 147h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2z" data-reactid="299"></path><path fill="#6cb120" d="M1029 147h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2z" data-reactid="300"></path><path fill="#d6d6d6" d="M1013 134h2v7h-2zm-8 0h2v7h-2z" data-reactid="301"></path><path fill="#6cb120" d="M1013 134h2v7h-2zm-8 0h2v7h-2zm0-13v7h2v-6l-1-1h-1z" data-reactid="302"></path><path fill="#d6d6d6" d="M1021 121h2v7h-2zm-8 0h2v7h-2zm10 20l-2-2v-5h2v7z" data-reactid="303"></path><path fill="#6cb120" d="M1039 154h-2v-2l2 2z" data-reactid="304"></path><path fill="url(#a)" d="M603 148h41v49h-41z" data-reactid="305"></path><path fill="#fff" d="M1335 162h-26l26 26v-26z" data-reactid="306"></path><path fill="#daea53" d="M1335 162h-26l13-13 13 13z" data-reactid="307"></path><path fill="#d6d6d6" d="M1329 166h2v7h-2zm-8 0h2v7h-2z" data-reactid="308"></path><path d="M1293 197c6.2 0 13.6-2.6 18-7a28 28 0 0 1 20-8h20a17.84 17.84 0 0 0 12-5c2-2 4.4-4.2 7-5v25h-77z" data-reactid="309"></path><path fill="#6cb120" d="M1023 141h-2v-5l2 2v3z" data-reactid="310"></path><path d="M718.5 186h-10a3.54 3.54 0 0 1-3.5-3.5 3.54 3.54 0 0 1 3.5-3.5h1.1a3.49 3.49 0 0 0 2.4-1 3.49 3.49 0 0 1 2.4-1h4.1a4.48 4.48 0 0 1 4.5 4.46 4.48 4.48 0 0 1-4.46 4.5zM848 175.5a3.54 3.54 0 0 1 3.5-3.5 6 6 0 0 0 4.3-1.8l.2-.2a6.73 6.73 0 0 1 4.8-2h7.7a5.55 5.55 0 0 1 5.5 5.5 5.55 5.55 0 0 1-5.5 5.5h-17a3.54 3.54 0 0 1-3.5-3.5zm88.5 10.5H948a3 3 0 0 0 3-3 3 3 0 0 0-3-3h-.9a4.91 4.91 0 0 1-3.6-1.5 5.07 5.07 0 0 0-3.6-1.5h-3.4a4.4 4.4 0 0 0-4.4 4.4v.1a4.34 4.34 0 0 0 4.18 4.5h.22z" fill="#daea53" data-reactid="311"></path><path fill="#d6d6d6" d="M1007 122l-1-1h1v1z" data-reactid="312"></path><path d="M1099 50h-24a19.93 19.93 0 0 0-20 19.86V197h44V50z" fill="#fff" data-reactid="313"></path><path fill="#d6d6d6" d="M1072 58h2v57h-2zm7 0h2v57h-2zm9 63l-2-2V58h2v63zm7 7l-2-2V58h2v70z" data-reactid="314"></path><path fill="#d6d6d6" d="M1123 153V50h-24v81l-16-16h-28v82h112l-44-44z" data-reactid="315"></path><path fill="#6cb120" d="M1088 151h-2v-33l2 2v31zm7 0h-2v-26l2 2v24zm-17-30h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm16 13h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm16 13h2v7h-2zm-8 0h2v7h-2zm-8 0h2v7h-2zm20 32v2a5.06 5.06 0 0 1-1.7 3.3l-2 2-.3.3V179h-2v18h2v-5a7.43 7.43 0 0 1 1.7-4.3l2-2a7 7 0 0 0 2.3-4.7v-2h-2z" data-reactid="316"></path><path d="M1066 175.6a3.46 3.46 0 0 1 3.5-3.4h1.2a4 4 0 0 0 2.5-1l2.2-2.1a3.74 3.74 0 0 1 2.5-1h8.6a5.57 5.57 0 0 1 5.6 5.5 5.57 5.57 0 0 1-5.6 5.5h-16.9a3.63 3.63 0 0 1-3.6-3.5z" fill="#fff" data-reactid="317"></path><path fill="#6cb120" d="M1106 186h2v11h-2z" data-reactid="318"></path><path d="M1101.5 186h11.5a3 3 0 0 0 3-3 3 3 0 0 0-3-3h-.9a4.91 4.91 0 0 1-3.6-1.5 5.08 5.08 0 0 0-3.6-1.5h-3.4a4.4 4.4 0 0 0-4.4 4.4v.1a4.34 4.34 0 0 0 4.18 4.5h.22z" fill="#fff" data-reactid="319"></path><path fill="#6cb120" d="M1106 58h2v93h-2zm7 0h2v93h-2z" data-reactid="320"></path><path d="M1297 20a32 32 0 1 0 32 32 32 32 0 0 0-32-32zm0 49a17 17 0 1 1 17-17 17 17 0 0 1-17 17z" transform="translate(0 -3)" fill="url(#c)" data-reactid="321"></path></svg>
	</div>

	<div class="footer-info col-12 pt-4">
		<div class="container">
		<div class="d-md-flex">
				<div class="footer-logo col-12 col-md-4 text-md-left">
				<a href="{{route('driver.profile')}}">
					<img src="{{site_setting('1','5')}}"/>
				</a>

				<div class="select_lang mt-3 select">  
					{!! Form::select('language',$language, (Session::get('language')) ? Session::get('language') : $default_language[0]->value, ['class' => 'language-selector footer-select', 'aria-labelledby' => 'language-selector-label', 'id' => 'language_footer']) !!}
				</div>

			</div>

			<div class="col-12 col-md-4 mt-5 mt-md-3">
				<ul>
					@if(@$static_pages_changes[0] !='')
						@foreach(@$static_pages_changes[0] as $page_url)
						<li>
							<a class="theme-color" href="{{route('page',$page_url->url)}}">
								{{$page_url->name}}
							</a>
						</li>
						@endforeach
					@endif
					<li><a class="theme-color"  href="{{route('help_page',current_page())}}">{{trans('messages.footer.help')}}</a></li>

				</ul>
			</div>


			@if(@$static_pages_changes[1]!='')
			<div class="col-12 col-md-4 mt-md-3">
				<ul>
					@foreach(@$static_pages_changes[1] as $page_url )
					<li>
						<a class="theme-color" href="{{route('page',$page_url->url)}}">
							{{$page_url->name}}
						</a>
					</li>
					@endforeach
				</ul>
			</div>
			@endif
		</div>

		<div class="copyright py-3">
			<div class="col-12 text-center mt-3">
				<p>© 2018 Trioangle Technologies Inc.</p>
			</div>
		</div>
	</div>
	</div>
</footer>
@endif