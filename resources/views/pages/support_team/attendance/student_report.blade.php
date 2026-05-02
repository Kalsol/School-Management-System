@extends('layouts.master')
@section('page_title', 'Student Attendance Report')
@section('content')

<div class="card">
    <div class="card-header header-elements-inline">
        <h5 class="card-title"><i class="icon-filter3 mr-2"></i> Attendance Report</h5>
        <div class="header-elements">
            <form action="{{ route('attendance.notify_absentees') }}" method="post">
                @csrf
                <button type="submit" class="btn btn-success btn-sm font-weight-bold">
                    <i class="icon-bell8 mr-1"></i> SEND ABSENT ALERTS
                </button>
            </form>
        </div>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('attendance.student_report') }}">
            <div class="row">
                <div class="col-md-10">
                    <fieldset>
                        <div class="row">
                            {{-- Class Select --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="my_class_id" class="col-form-label font-weight-bold">Class:</label>
                                    <select required onchange="getClassData(this.value)" id="my_class_id" name="my_class_id" class="form-control select">
                                        <option value="">Select Class</option>
                                        @foreach($my_classes as $c)
                                        <option {{ ($selected && $my_class_id == $c->id) ? 'selected' : '' }} value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Section Select --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="section_id" class="col-form-label font-weight-bold">Section:</label>
                                    <select required id="section_id" name="section_id" class="form-control select">
                                        @if($selected)
                                        @foreach($sections->where('my_class_id', $my_class_id) as $s)
                                        <option {{ $section_id == $s->id ? 'selected' : '' }} value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            {{-- Date Select --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date" class="col-form-label font-weight-bold">Date:</label>
                                    <input type="date" id="date" name="date" class="form-control" value="{{ $date ?? date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>

                <div class="col-md-2 mt-4">
                    <div class="text-right mt-1">
                        <button type="submit" class="btn btn-primary">Generate <i class="icon-paperplane ml-2"></i></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white header-elements-inline">
        <h6 class="card-title font-weight-bold">
            Attendance Logs for <span class="text-primary">{{ \Carbon\Carbon::parse($date)->format('d F, Y') }}</span>
        </h6>
    </div>

    <div class="table-responsive">
        <table class="table datatable-button-html5-columns table-hover">
            <thead class="bg-light">
                <tr>
                    <th>S/N</th>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>ADM No</th>
                    <th>Time In</th>
                    <th>Lateness</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $at)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <img src="{{ $at->user->photo }}" class="rounded-circle border border-light" width="38" height="38" onerror="this.src='{{ asset('assets/images/user.png') }}'">
                    </td>
                    <td>
                        <div class="font-weight-semibold text-dark">{{ $at->user->name }}</div>
                    </td>
                    <td><span class="text-muted small">{{ $at->student_record->adm_no ?? 'N/A' }}</span></td>
                    <td>{{ $at->time_in ? \Carbon\Carbon::parse($at->time_in)->format('h:i A') : 'N/A' }}</td>
                    <td>
                        @if($at->minutes_late > 0)
                        <span class="badge badge-light-danger text-danger font-weight-semibold">{{ $at->minutes_late }} mins late</span>
                        @else
                        <span class="text-success small font-weight-bold"><i class="icon-checkmark4 mr-1"></i>On Time</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($at->status == 'Present')
                        <span class="badge badge-pill badge-success px-3">Present</span>
                        @elseif($at->status == 'Late')
                        <span class="badge badge-pill badge-warning px-3">Late</span>
                        @else
                        <span class="badge badge-pill badge-danger px-3">Absent</span>
                        @endif

                        @if($at->is_excused)
                        <br><span class="badge badge-flat border-success text-success mt-1" title="Approved by Admin">Excused</span>
                        @endif
                    </td>

                    <td class="text-center">
                        <div class="list-icons">
                            {{-- SHOW BUTTON ONLY FOR ABSENT/LATE WITH REMARKS --}}
                            @if(in_array($at->status, ['Absent', 'Late']) && $at->remarks && !$at->is_excused)
                            <button type="button" class="btn btn-sm btn-outline-primary mr-2 text-white" data-toggle="modal" data-target="#excuseModal{{ $at->id }}">
                                <i class="icon-bubble-dots4 mr-1"></i> View Excuse
                            </button>
                            @endif

                            <div class="dropdown">
                                <div class="dropdown-menu dropdown-menu-right">
                                    @if($at->is_excused)
                                    <div class="dropdown-divider"></div>
                                    <a href="#" class="dropdown-item text-muted disabled"><i class="icon-info22"></i> Already Excused</a>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- DYNAMIC MODAL --}}
                        <div class="modal fade" id="excuseModal{{ $at->id }}" tabindex="-1" role="dialog">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary">
                                        <h6 class="modal-title text-white">Review Excuse: {{ $at->user->name }}</h6>
                                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body text-left">
                                        <p class="font-weight-bold mb-1 text-muted">Parent's Reason:</p>
                                        <div class="p-3 bg-light border-left-primary border-left-3 rounded mb-3">
                                            "{{ $at->remarks }}"
                                        </div>

                                        <!-- Inside the Admin Modal Body -->
                                        <div class="form-group border-bottom pb-2">
                                            <label class="font-weight-bold text-muted uppercase small">Parent's Evidence:</label>
                                            <div class="mt-1">
                                                @if($at->evidence)
                                                @php
                                                $file_ext = strtolower(pathinfo($at->evidence, PATHINFO_EXTENSION));
                                                $file_path = asset('storage/uploads/attendance_evidence/' . $at->evidence);
                                                @endphp

                                                @if(in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif']))
                                                {{-- If it's an image, show a thumbnail that opens the full image --}}
                                                <a href="{{ $file_path }}" target="_blank">
                                                    <img src="{{ $file_path }}" alt="Evidence" class="img-thumbnail" style="max-height: 150px;">
                                                    <div class="small text-primary mt-1"><i class="icon-zoomin3"></i> Click to enlarge</div>
                                                </a>
                                                @else
                                                {{-- If it's a PDF or other file, show a download/view button --}}
                                                <a href="{{ $file_path }}" target="_blank" class="btn btn-outline-info btn-sm">
                                                    <i class="icon-file-pdf mr-1"></i> View PDF Document
                                                </a>
                                                @endif
                                                @else
                                                <span class="text-muted italic">No evidence uploaded by parent.</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="font-weight-bold text-muted">Admin Response / Feedback:</label>
                                            <textarea id="admin_resp_{{ $at->id }}" class="form-control" rows="3" placeholder="Optional: Provide feedback to parent..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 justify-content-center">
                                        <!-- Inside the modal-footer of the loop -->
                                        <button onclick="handleExcuse({{ $at->id }}, 'approve')" class="btn btn-success">
                                            Approve
                                        </button>

                                        <button onclick="handleExcuse({{ $at->id }}, 'reject')" class="btn btn-danger">
                                            Reject
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    function handleExcuse(id, action) {
        let responseText = $('#admin_resp_' + id).val();

        // Generate the URL using the route name and a placeholder
        let baseUrl = "{{ route('attendance.update_excuse', ':id') }}";
        let finalUrl = baseUrl.replace(':id', id);

        console.log("Final URL for AJAX:", finalUrl); // Debugging line to check the final URL

        $.ajax({
            url: finalUrl, // This will now be 100% correct
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                action: action,
                admin_response: responseText
            },
            success: function(response) {
                if (response.ok) {
                    new PNotify({
                        text: response.msg,
                        type: 'success',
                        addclass: 'bg-success border-success'
                    });
                    $('#excuseModal' + id).modal('hide');
                    setTimeout(() => location.reload(), 1200);
                }
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON ? xhr.responseJSON.msg : 'Server Error';
                alert('Error: ' + errorMsg);
            }
        });
    }
</script>
@endsection