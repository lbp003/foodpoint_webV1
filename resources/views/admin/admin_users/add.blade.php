@extends('admin.template')
@section('main')
<div class="content">
	<div class="container-fluid">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header card-header-rose card-header-text">
					<div class="card-text">
						<h4 class="card-title"> Add Admin User </h4>
					</div>
				</div>
				<div class="card-body">
					{!! Form::open(['url' => route('admin.create_admin'), 'class' => 'form-horizontal','id'=>'edit_admin_form']) !!}
					<div class="row">
						<label class="col-sm-2 col-form-label">@lang('admin_messages.name')<span class="required text-danger">*</span></label>
						<div class="col-sm-10">
							<div class="form-group">
								{!! Form::text('username','', ['class' => 'form-control','size'=>'3x3', 'id' => 'input_username']) !!}
								<span class="text-danger">{{ $errors->first('username') }}</span>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-sm-2 col-form-label">@lang('admin_messages.email')<span class="required text-danger">*</span></label>
						<div class="col-sm-10">
							<div class="form-group">
								{!! Form::text('email','', ['class' => 'form-control','size'=>'3x3', 'id' => 'input_email']) !!}
								<span class="text-danger">{{ $errors->first('email') }}</span>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-sm-2 col-form-label">@lang('admin_messages.password')<span class="required text-danger">*</span></label>
						<div class="col-sm-10">
							<div class="form-group">
								{!! Form::text('password','', ['class' => 'form-control','size'=>'3x3', 'id' => 'input_password']) !!}
								<span class="text-danger">{{ $errors->first('password') }}</span>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-sm-2 col-form-label">@lang('admin_messages.role')<span class="required text-danger">*</span></label>
						<div class="col-sm-10">
							<div class="form-group">
								{!! Form::select('role', $roles, '', ['class' => 'form-control', 'id' => 'input_role', 'placeholder' => 'Select']) !!}
								<span class="text-danger">{{ $errors->first('role') }}</span>
							</div>
						</div>
					</div>
					<div class="row">
						<label class="col-sm-2 col-form-label">@lang('admin_messages.status')<span class="required text-danger">*</span></label>
						<div class="col-sm-10">
							<div class="form-group">
								{!! Form::select('status', array('1' => 'Active','0' => 'Inactive'), '1', ['class' => 'form-control', 'id' => 'input_status']) !!}
								<span class="text-danger">{{ $errors->first('role') }}</span>
							</div>
						</div>
					</div>
					<div class="card-footer">
						<div class="ml-auto">
							<button class="btn btn-fill btn-rose btn-wd" type="submit"  value="edit_admin">
							@lang('admin_messages.submit')
							</button>
						</div>
						<div class="clearfix"></div>
					</div>
					{!! Form::close() !!}
				</div>
			</div>
		</div>
	</div>
</div>
@endsection