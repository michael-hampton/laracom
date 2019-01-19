<!-- Default box -->
@if($courier_rates)
<div class="box">
    <div class="box-body">
        <h2> <i class="fa fa-truck"></i> Couriers</h2>
            <form id='editCourierRateForm'>
                @foreach ($courier_rates as $courier_rate)
                <div class='inline-form'>
                    <div class="form-group col-lg-3">
                        <label for="courier" class="sr-only">Courier</label>
                        
                        <input type='hidden' class='channel' name='rates[$courier_rate->id][courier]'>
                        
                        <select name="rates[$courier_rate->id][courier]" id="courier" class="form-control select2">
                            <option value="">--Select--</option>
                            @foreach($couriers as $courier)
                            <option @if($courier_rate->courier == $courier->id) selected="selected" @endif value="{{ $courier->id }}">{{ $courier->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-lg-2">
                        <label class='sr-only' for="range_from">Range From</label>
                        <div class="input-group">
                            <input type="text" name="rates[$courier_rate->id][range_from]" id="range_from" placeholder="Range From" class="form-control" value="{{ $courier_rate->range_from }}">
                        </div>
                    </div>

                    <div class="form-group col-lg-2">
                        <label class='sr-only' for="range_to">Range To</label>
                        <div class="input-group">
                            <input type="text" name="rates[$courier_rate->id][range_to]" id="range_to" placeholder="Range To" class="form-control" value="{{ $courier_rate->range_to }}">
                        </div>
                    </div>

                    <div class="form-group col-lg-2">
                        <label class='sr-only' for="cost">Cost</label>
                        <div class="input-group">
                            <input type="text" name="rates[$courier_rate->id][cost]" id="cost" placeholder="Cost" class="form-control" value="{{ $courier_rate->cost }}">
                        </div>
                    </div>

                    <div class="form-group col-lg-3">
                        <label class='sr-only' for="country">Country </label>
                        <select name="rates[$courier_rate->id][country]" id="country" class="form-control">
                            @foreach($countries as $country)
                            <option @if($courier_rate->country == $country->id) selected='selected' @endif value="{{ $country->id }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
                </form>
                
                <button type='button' class='btn btn-primary' id='updateCourierRates'></button>
    </div>
    <!-- /.box-body -->
</div>
<!-- /.box -->
@endif
