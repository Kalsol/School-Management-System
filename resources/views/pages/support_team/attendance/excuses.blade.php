@extends('layouts.master')
@section('page_title', 'Student Attendance Report')
@section('content')
<div class="card">
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
                                    <select required id="section_id" name="section_id" data-placeholder="Select Class First" class="form-control select">
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
                        <button type="submit" class="btn btn-primary">
                            Generate <i class="icon-paperplane ml-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Data Table Section --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white header-elements-inline">
        <h6 class="card-title font-weight-bold">
            Attendance Logs for <span class="text-primary">{{ \Carbon\Carbon::parse($date)->format('d F, Y') }}</span>
        </h6>
        <div class="header-elements">
            <span class="badge badge-flat border-grey text-grey-800">{{ count($attendances) }} Records Found</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table datatable-button-html5-columns table-hover">
            <thead class="bg-light">
                <tr>
                    <th class="font-weight-bold">S/N</th>
                    <th class="font-weight-bold">Photo</th>
                    <th class="font-weight-bold">Name</th>
                    <th class="font-weight-bold">ADM No</th>
                    <th class="font-weight-bold">Time In</th>
                    <th class="font-weight-bold">Lateness</th>
                    <th class="font-weight-bold text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $at)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <img src="{{ $at->user->photo }}" class="rounded-circle border border-light" width="38" height="38" alt="photo" onerror="this.src='{{ asset('assets/images/user.png') }}'">
                    </td>
                    <td>
                        <div class="font-weight-semibold text-dark">{{ $at->user->name }}</div>
                    </td>
                    <td><span class="text-muted small">{{ $at->student_record->adm_no ?? 'N/A' }}</span></td>
                    <td>{{ \Carbon\Carbon::parse($at->time_in)->format('h:i A') }}</td>
                    <td>
                        @if($at->minutes_late > 0)
                        <span class="badge badge-light-danger text-danger font-weight-semibold">
                            {{ $at->minutes_late }} mins late
                        </span>
                        @else
                        <span class="text-success small font-weight-bold"><i class="icon-checkmark4 mr-1"></i>On Time</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="list-icons">
                            <div class="dropdown">
                                <a href="#" class="list-icons-item" data-toggle="dropdown">
                                    <i class="icon-menu9"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a href="" class="dropdown-item"><i class="icon-eye"></i> View Info</a>
                                    @if(Qs::userIsTeamSA())
                                    <a href="" class="dropdown-item"><i class="icon-pencil"></i> Edit</a>
                                    <a href="" class="dropdown-item"><i class="icon-lock"></i> Reset password</a>
                                    @endif
                                    <a href="#" class="dropdown-item"><i class="icon-check"></i> Marksheet</a>

                                    {{--Delete--}}
                                    {{-- @if(Qs::userIsSuperAdmin())
                                    <a id="{{ Qs::hash($sr->user->id) }}" onclick="confirmDelete(this.id)" href="#" class="dropdown-item"><i class="icon-trash"></i> Delete</a>
                                    <form method="post" id="item-delete-{{ Qs::hash($sr->user->id) }}" action="{{ route('students.destroy', Qs::hash($sr->user->id)) }}" class="hidden">@csrf @method('delete')</form>
                                    @endif
                                    --}}

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
@endsection