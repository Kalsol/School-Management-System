<script> 

    function getLGA(state_id){

        var url = "{{ route('get_lga', [':id']) }}";
        url = url.replace(':id', state_id);
        var lga = $('#lga_id');

        $.ajax({
            dataType: 'json',
            url: url,
            success: function (resp) {
                //console.log(resp);
                lga.empty();
                $.each(resp, function (i, data) {
                    lga.append($('<option>', {
                        value: data.id,
                        text: data.name
                    }));
                });

            }
        })
    }
    
    function getStates(nationality_id){
        var url = "{{ route('get_states', [':id']) }}";
        url = url.replace(':id', nationality_id);
        var state = $('#state_id');

        $.ajax({
            dataType: 'json',
            url: url,
            success: function (resp) {
                //console.log(resp);
                state.empty();
                $.each(resp, function (i, data) {
                    state.append($('<option>', {
                        value: data.id,
                        text: data.name
                    }));
                });

            }
        })
    }

    function getClassData(class_id) {
        if(!class_id) return; // Exit if no class is selected
    
        // Point to the combined route (using get_class_subjects since it returns both)
        var url = "{{ route('get_class_subjects', [':id']) }}";
        url = url.replace(':id', class_id);
        
        var sectionSelect = $('#section_id');
        var subjectSelect = $('#subject_id');
    
        $.ajax({
            dataType: 'json',
            url: url,
            method: 'GET',
            beforeSend: function() {
                // Optional: Show a loading state
                sectionSelect.html('<option>Loading...</option>');
                subjectSelect.html('<option>Loading...</option>');
            },
            success: function (resp) {
                // Clear existing options
                sectionSelect.empty();
                subjectSelect.empty();
    
                // Populate Sections
                if (resp.sections && resp.sections.length > 0) {
                    $.each(resp.sections, function (i, data) {
                        sectionSelect.append($('<option>', {
                            value: data.id,
                            text: data.name
                        }));
                    });
                } else {
                    sectionSelect.append('<option value="">No Sections Found</option>');
                }
    
                // Populate Subjects
                if (resp.subjects && resp.subjects.length > 0) {
                    $.each(resp.subjects, function (i, data) {
                        subjectSelect.append($('<option>', {
                            value: data.id,
                            text: data.name
                        }));
                    });
                } else {
                    subjectSelect.append('<option value="">No Subjects Found</option>');
                }
            },
            error: function (xhr) {
                // Debugging: This will show the actual error in the console
                console.error("Error fetching class data:", xhr.responseText);
                flash({msg : 'Failed to fetch class data', type : 'danger'});
            }
        });
    }
    
    function getClassSections(class_id, destination){
            var url = "{{ route('get_class_sections', [':id']) }}";
            url = url.replace(':id', class_id);
            var section = destination ? $(destination) : $('#section_id');
    
            $.ajax({
                dataType: 'json',
                url: url,
                success: function (resp) {
                    //console.log(resp);
                    section.empty();
                    $.each(resp, function (i, data) {
                        section.append($('<option>', {
                            value: data.id,
                            text: data.name
                        }));
                    });
    
                }
            })
        }
    
    
        function onUserTypeChange(selectElement) {
        // 1. Get the selected option
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        
        // 2. Get the name from the data attribute
        const userTypeName = selectedOption.getAttribute('data-name');
        
        // 3. Use the name
        console.log("Selected Name:", userTypeName);
        
        if(userTypeName === 'Parent') {
            $('#employment-date').prop('hidden', true);
            // do something specific for parents
        } else {
            $('#employment-date').prop('hidden', false);
            // do something specific for students
        }
    }
    
    function onChangeTermWriteTitle(termId) {
        // Implement your logic here
            
        var termText = '';
        if(termId === '1') {
            termText = 'First Term';
        } else if(termId === '2') {
            termText = 'Second Term';
        }
        var session = "{{ Qs::getSetting('current_session') }}";
        var generatedName = termText + ' ' + 'Exam and Assessment Marking for' + ' ' + session;
        $('#marking_name').val(generatedName);
        $('#marking_name').prop('readonly', true);
    }
    
    function confirmPermanentDelete(id) 
    {
        Modal.fire({
            title: "Are you sure?",
            text: "This item will be permanently deleted.",
            icon: "warning",
            confirmButtonText: "Sure, Delete Permanently",
            customClass: {
                confirmButton: "bg-danger",
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $('form#item-delete-'+id).submit();
            } 
        });
    }
    
    /**
     *-------------------------------------------------------------
     * Handle notices
     *-------------------------------------------------------------
     */
    function updateNoticeStatus(el)
    {
        console.log(el);
        var badge = $(el).parents("div.notices").siblings(".card-header").children(".card-title").children(".badge");
        // Remove class 'unviewed', turn off 'click' event handler, and remove iteration indicator.
        $("button#" + el.id).removeClass("unviewed").off("click").siblings(".iteration").remove();
        // Update Badge
        badge.text((badge.text() == 0) ? 0 : badge.text() - 1);
    }
    
    $(document).on("click", ".notices button.unviewed", function(e)
    {
        console.log('clicked');
        setNoticeAsViwed(this);
    });

    function setNoticeAsViwed(el)
    {
        var url = "{{ route('notices.set_viewed') }}";
        $.ajax({
            dataType: 'json',
            method: "post",
            data: {
                "id": el.id},
            url: url,
            success: function (resp) {
               return (resp.ok === true) ? updateNoticeStatus(el) : false;
            },
        });
    }

    $(document).on('click', '.pagination a', function(event)
    {
        event.preventDefault();
        $('li').removeClass('active');
        $(this).parent('li').addClass('active');  
        getNoticesData($(this).attr('href'));
    });

    function getNoticesData(url)
    {
        var status = url.includes('unviewed') ? 'unviewed' : 'viewed';
        const notices = $('.notices').find('#' + status);

        notices.empty().append(`<div class="notices-loading">${noticesLoadingSkin(4, status)}</div>`);

        $.ajax(
        {
            url: url,
            type: "get",
            datatype: "html"
        }).done(function(data) {
            notices.replaceWith(data);
            window.location.hash = url;
        }).fail(function(jqXHR, ajaxOptions, thrownError) {
            flash({msg: "Sorry, something went wrong.", type: 'error'});
        });
    }

    // Handle haschange when the user changed it manually in the address bar
    $(window).on('hashchange', function() 
    {
        if (window.location.hash) {
            var url = window.location.hash.replace('#', '');
            getNoticesData(url);
        }
    });

    function noticesLoadingSkin(duplicate_times, status) 
    {
        let template = "";
        // Starting codes - two opening div's reserved
        var a = `<div class="card m-0 border-bottom-0 text-muted">
                    <div class="card-header position-relative">
                        <span class="float-left pr-10 status-styled">` + capitalize(status) + `</span><i class="text-muted float-right name skeleton"></i>
                    </div>
                    <div class="card-body p-1">
                        <div id="accordion-">`;
        // With iteration indicator
        var bb = `       <div class="card mb-1">
                                <div class="card-header">
                                    <h5 class="mb-0 d-flex">
                                    <span class="text-muted iteration skeleton mr-1"></span>
                                    <button class="btn btn-link w-100 pl-1 p-0 border-left-1 border-left-info">
                                        <span class="float-left pr-10 title skeleton"></span><i class="text-muted float-right time skeleton"></i>
                                    </button>
                                </h5>
                            </div>
                        </div>`;
        // Without iteration indicator
        var cc = `  <div class="card mb-1">
                        <div class="card-header">
                            <h5 class="mb-0 d-flex">
                                <button class="btn btn-link w-100 pl-1 p-0 border-left-1 border-left-info">
                                    <span class="float-left pr-10 title skeleton"></span><i class="text-muted float-right time skeleton"></i>
                                </button>
                            </h5>
                        </div>
                    </div>`;
        // Closing codes - two closing div's for the two opening div's in variable 'a' above
        var d = `       <div class="position-relative pt-2">
                                <span class="float-right">
                                    <nav>
                                        <ul class="pagination">
                                            <li class="page-item disabled"><span class="page-link skeleton">‹</span></li>
                                            <li class="page-item disabled"><span class="page-link skeleton"></span></li>
                                            <li class="page-item disabled"><a class="page-link skeleton"></a></li>
                                            <li class="page-item disabled"><a class="page-link skeleton">›</a></li>
                                        </ul>
                                    </nav>
                                </span>
                                <span class="float-left showing skeleton"></span>
                            </div>
                        </div>
                    </div>
                </div>`;
            
        var b = c = "";
        for (let i = 0; i < duplicate_times; i++) {
            b += bb;
            c += cc;
        }
        
        // With iteration indicator - unviewed notices loading skeleton
        if (status === "unviewed")
            template += a + b + d;
        // Without iteration indicator - viewed notices loading skeleton
        else 
            template += a + c + d;

        return template;
    }

    {{--Notifications--}}

    @if (session('pop_error'))
    pop({msg : "{{ session('pop_error') }}", type : "error"});
    @endif

    @if (session('pop_warning'))
    pop({msg : "{{ session('pop_warning') }}", type : "warning"});
    @endif

    @if (session('pop_success'))
    pop({msg : "{{ session('pop_success') }}", type : 'success', title: 'GREAT!!'});
    @endif

    @if (session('flash_info'))
      flash({msg : "{{ session('flash_info') }}", type : 'info'});
    @endif

    @if (session('flash_success'))
      flash({msg : "{{ session('flash_success') }}", type : 'success'});
    @endif

    @if (session('flash_warning'))
      flash({msg : "{{ session('flash_warning') }}", type : 'warning'});
    @endif

     @if (session('flash_error') || session('flash_danger'))
      flash({msg : "{{ session('flash_error') ?: session('flash_danger') }}", type : 'danger'});
    @endif

    {{--End Notifications--}}

    
    
    function pop(data){
        swal({
            title: data.title ? data.title : 'Oops...',
            text: data.msg,
            icon: data.type
        });
    }

    function flash(data){
        new PNotify({
            text: data.msg,
            type: data.type,
            hide : data.type !== "danger"
        });
    }

    function confirmDelete(id) {
        swal({
            title: "Are you sure?",
            text: "Once deleted, you will not be able to recover this item!",
            icon: "warning",
            buttons: true,
            dangerMode: true
        }).then(function(willDelete){
            if (willDelete) {
             $('form#item-delete-'+id).submit();
            }
        });
    }

    function confirmReset(id) {
        swal({
            title: "Are you sure?",
            text: "This will reset this item to default state",
            icon: "warning",
            buttons: true,
            dangerMode: true
        }).then(function(willDelete){
            if (willDelete) {
             $('form#item-reset-'+id).submit();
            }
        });
    }

    $('form#ajax-reg').on('submit', function(ev){
        ev.preventDefault();
        submitForm($(this), 'store');
        $('#ajax-reg-t-0').get(0).click();
    });

    $('form.ajax-pay').on('submit', function(ev){
        ev.preventDefault();
        submitForm($(this), 'store');

//        Retrieve IDS
        var form_id = $(this).attr('id');
        var td_amt = $('td#amt-'+form_id);
        var td_amt_paid = $('td#amt_paid-'+form_id);
        var td_bal = $('td#bal-'+form_id);
        var input = $('#val-'+form_id);

        // Get Values
        var amt = parseInt(td_amt.data('amount'));
        var amt_paid = parseInt(td_amt_paid.data('amount'));
        var amt_input = parseInt(input.val());

//        Update Values
        amt_paid = amt_paid + amt_input;
        var bal = amt - amt_paid;

        td_bal.text(''+bal);
        td_amt_paid.text(''+amt_paid).data('amount', ''+amt_paid);
        input.attr('max', bal);
        bal < 1 ? $('#'+form_id).fadeOut('slow').remove() : '';
    });

    $('form.ajax-store').on('submit', function(ev){
        ev.preventDefault();
        submitForm($(this), 'store');
        var div = $(this).data('reload');
        div ? reloadDiv(div) : '';
    });

    $('form.ajax-update').on('submit', function(ev){
        ev.preventDefault();
        submitForm($(this));
        var div = $(this).data('reload');
        div ? reloadDiv(div) : '';
    });

    $('.download-receipt').on('click', function(ev){
        ev.preventDefault();
        $.get($(this).attr('href'));
        flash({msg : '{{ 'Download in Progress' }}', type : 'info'});
    });

    function reloadDiv(div){
        var url = window.location.href;
        url = url + ' '+ div;
        $(div).load( url );
    }

    function submitForm(form, formType){
        var btn = form.find('button[type=submit]');
        disableBtn(btn);
        var ajaxOptions = {
            url:form.attr('action'),
            type:'POST',
            cache:false,
            processData:false,
            dataType:'json',
            contentType:false,
            data:new FormData(form[0])
        };
        var req = $.ajax(ajaxOptions);
        req.done(function(resp){
            resp.ok && resp.msg
               ? flash({msg:resp.msg, type:'success'})
               : flash({msg:resp.msg, type:'danger'});
            hideAjaxAlert();
            enableBtn(btn);
            formType == 'store' ? clearForm(form) : '';
            scrollTo('body');
            return resp;
        });
        req.fail(function(e){
            if (e.status == 422){
                var errors = e.responseJSON.errors;
                displayAjaxErr(errors);
            }
           if(e.status == 500){
               displayAjaxErr([e.status + ' ' + e.statusText + ' Please Check for Duplicate entry or Contact School Administrator/IT Personnel'])
           }
            if(e.status == 404){
               displayAjaxErr([e.status + ' ' + e.statusText + ' - Requested Resource or Record Not Found'])
           }
            enableBtn(btn);
            return e.status;
        });
    }

    function disableBtn(btn){
        var btnText = btn.data('text') ? btn.data('text') : 'Submitting';
        btn.prop('disabled', true).html('<i class="icon-spinner mr-2 spinner"></i>' + btnText);
    }

    function enableBtn(btn){
        var btnText = btn.data('text') ? btn.data('text') : 'Submit Form';
        btn.prop('disabled', false).html(btnText + '<i class="icon-paperplane ml-2"></i>');
    }

    function displayAjaxErr(errors){
        $('#ajax-alert').show().html(' <div class="alert alert-danger border-0 alert-dismissible" id="ajax-msg"><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>');
        $.each(errors, function(k, v){
            $('#ajax-msg').append('<span><i class="icon-arrow-right5"></i> '+ v +'</span><br/>');
        });
        scrollTo('body');
    }

    function scrollTo(el){
        $('html, body').animate({
            scrollTop:$(el).offset().top
        }, 2000);
    }

    function hideAjaxAlert(){
        $('#ajax-alert').hide();
    }

    function clearForm(form){
        form.find('.select, .select-search').val([]).select2({ placeholder: 'Select...'});
        form[0].reset();
    }



</script>