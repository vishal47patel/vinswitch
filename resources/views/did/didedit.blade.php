@extends('layouts.mainLayout.main')
@section('title', 'Did Edit')
@section('content')


<div class="row">

    <div class="col-lg-9 col-xl-9">
        <div class="card">
            <div class="card-body">
                <!-- <ul class="nav nav-pills nav-fill navtab-bg">
                    <li class="nav-item">
                        <a href="#personal" data-bs-toggle="tab" aria-expanded="true" class="nav-link active">
                            Information
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#cred" data-bs-toggle="tab" aria-expanded="false" class="nav-link ">
                            Credential
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#bill" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                            Billing
                        </a>
                    </li>
                </ul> -->
                <div class="tab-content">
                    <div class="tab-pane show active" id="personal">

                        <form class="needs-validation1 was-validated1" novalidate>
                            <h5 class="mb-3 text-uppercase bg-light p-2"><i class="mdi mdi-account-circle me-1"></i> Did Info</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">

                                        <label for="forvendor_id" class="form-label">Vendor</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class=" fab fa-odnoklassniki"></i></span>
                                            <select class="form-control" name="vendor" id="vendor" required>
                                                <option value="">Select</option>

                                                @isset($vendor)
                                                @foreach($vendor as $data)
                                                <option value="{{$data->id}}" @if($did['vendor_id'] == $data->id) selected="selected" @endif >{{$data->vendor_name}}</option>
                                                @endforeach
                                                @endisset

                                            </select>
                                            <div class="invalid-tooltip vendor">
                                                Please select valid Vendor.
                                            </div>

                                        </div>

                                    </div>

                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fornumber" class="form-label">Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="far fa-keyboard"></i></span>
                                            <input type="text" class="form-control" id="number" placeholder="Number" value="{{$did['number']}}" required>
                                            <div class="invalid-tooltip number">
                                                Please select valid Number.
                                            </div>
                                        </div>

                                    </div>
                                </div> <!-- end col -->
                            </div> <!-- end row -->




                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="forrate_center" class="form-label">Rate Center</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-percentage"></i></span>
                                            <input type="text" class="form-control" id="rate_center" placeholder="Rate center" value="{{$did['rate_center']}}" required>
                                            <div class="invalid-tooltip rate_center">
                                                Please select Rate Center.
                                            </div>
                                        </div>
                                    </div>

                                </div>


                            </div> <!-- end row -->




                            <div class="text-end">
                                <span class="btn btn-success waves-effect waves-light mt-2 diddatasubmit"><i class="mdi mdi-content-save"></i> Update</span>
                            </div>
                        </form>

                    </div> <!-- end tab-pane -->
                    <!-- end Information section content -->

                </div> <!-- end tab-content -->
            </div>
        </div> <!-- end card-->

    </div> <!-- end col -->
</div>
<!-- end row-->








<script>
    $(document).ready(function() {
        var base_url = $('#base_url').val();
        var id = $('#id').val();

        // update record
        $("body").on("click", ".diddatasubmit", function(){        
        //   console.log("id = "+id);

            var formData = {
                id: id,
                vendor: $("#vendor").val(),
                number: $("#number").val(),
                rate_center: $("#rate_center").val(),                
                table: "did",
                "_token":"{{ csrf_token() }}"
            };            
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: base_url+'/didedit_update_ajex',
                method: "POST",
                data:formData,
                success: function(result){
                    var selector;
                    $(".border-danger").removeClass("border-danger");
                    $(".invalid-tooltip").hide();
                    if(result.error !=0){     
                            if(result.data == "Something wrong"){
                                toster("danger", "Record", "Failed",result.data);
                            }                   
                            $.each(result.error, function(index, value){         

                                $('#'+index).addClass("border-danger").show();
                                $('.'+index).html(value).show();
                            });
                            
                            
                    }else{
                        $(".border-danger").removeClass("border-danger");
                        $(".invalid-tooltip").hide();
                        if(result.status == 'danger' || result.status == 'fail'){
                            if(result.data == "Record not exist"){
                                toster("danger", "", "","Record not found");
                            }else{
                                toster("danger", "Record", "Failed");
                            }


                        }else{
                            toster("success", "Record", "Updated");
                        }

                    }              
                },           
            }).fail(function(jqXHR,ajaxOptions,thrownError){                      
				authCheck(thrownError);
			});      

        });       
    });
</script>
@endsection