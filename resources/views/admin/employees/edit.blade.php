@extends('layouts.admin.app')

@section('content')
<!-- Main content -->
<section class="content">
    @include('layouts.errors-and-messages')
    <div class="box">
        <form action="{{ route('admin.employees.update', $employee->id) }}" method="post" class="form">
            <div class="box-body">
                <div class="row">
                    {{ csrf_field() }}
                    <input type="hidden" name="_method" value="put">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" placeholder="Name" class="form-control" value="{!! $employee->name ?: old('name')  !!}">
                        </div>
                        <div class="form-group">
                            <label for="email">Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-addon">@</span>
                                <input type="text" name="email" id="email" placeholder="Email" class="form-control" value="{!! $employee->email ?: old('email')  !!}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="password" placeholder="xxxxx" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="status">Status </label>
                            <select name="status" id="status" class="form-control">
                                <option value="0" @if($employee->status == 0) selected="selected" @endif>Disable</option>
                                <option value="1" @if($employee->status == 1) selected="selected" @endif>Enable</option>
                            </select>
                        </div>
                        @include('admin.shared.store-to-assign-select',['channelsWithoutEmployee' => $channelsWithoutEmployee])
                        @include('admin.shared.store-assigned-table-list', ['employeeChannels' => $employeeChannels, 'employee' => $employee])

                    </div>
                    <div class="col-md-4">
                        <label for="roles">Roles</label>
                        @include('admin.shared.roles', ['allRoles' => $allRoles])
                    </div>
                </div>
            </div>

            <!-- /.box-body -->
            <div class="box-footer">
                <div class="btn-group">
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-default btn-sm">Back</a>
                    <button type="submit" class="btn btn-primary btn-sm">Update</button>
                </div>
            </div>
        </form>
    </div>
    <!-- /.box -->

</section>
<!-- /.content -->
@endsection