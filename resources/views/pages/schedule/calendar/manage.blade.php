<div class="{{ $selected ? 'card' : 'card card-collapsed' }}">
    <div class="card-header header-elements-inline">
        <h5 class="card-title">Manage Events</h5>
        {!! Qs::getPanelOptions() !!}
    </div>

    <div class="card-body">
        <ul class="nav nav-tabs nav-tabs-highlight">
            <li class="nav-item"><a href="#all-events" class="{{ $selected ? 'nav-link' : 'nav-link active' }}" data-toggle="tab">Manage Events</a></li>
            @if($selected)
            <li class="nav-item"><a href="#edit-event" class="{{ $selected ? 'nav-link active' : 'nav-link' }}" data-toggle="tab"> Edit Event</a></li>
            @endif
        </ul>

        <div class="tab-content">
            {{--All Events--}}
            <div class="{{ $selected ? 'tab-pane fade' : 'tab-pane fade show active' }}" id="all-events">
                <table class="table datatable-button-html5-columns">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($events as $evt)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="break-all break-spaces">{{ $evt->name }}</td>
                            <td class="break-all break-spaces">{{ $evt->description }}</td>
                            <td>{{ $evt->year }}-{{ str_pad($evt->month, 2, '0', STR_PAD_LEFT) }}-{{ str_pad($evt->day, 2, '0', STR_PAD_LEFT) }}</td>
                            <td><span class="badge {{ $evt->status == 'new' ? 'badge-info' : 'badge-success' }}">{{ strtoupper($evt->status) }}</span></td>
                            <td>
                                @if(Qs::userIsTeamSA()) 
                                    <a href="{{ route('users.show', Qs::hash($evt->user_id)) }}">{{ $evt->user->name ?? 'Unknown' }}</a> 
                                @else 
                                    {{ $evt->user->name ?? 'Unknown' }} 
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="list-icons">
                                    <div class="dropdown">
                                        <a class="material-symbols-rounded" href="javascript:;" data-toggle="dropdown">lists</a>
                                        <div class="dropdown-menu dropdown-menu-left">
                                            @if(Qs::userIsTeamSA())
                                            <a href="{{ route('schedule.edit-event', $evt->id) }}" class="dropdown-item">
                                                <i class="material-symbols-rounded">edit</i> Edit
                                            </a>
                                            @endif
                                            @if(Qs::userIsSuperAdmin())
                                            <a href="javascript:;" onclick="confirmDelete({{ $evt->id }})" class="dropdown-item text-danger">
                                                <i class="material-symbols-rounded">delete</i> Delete
                                            </a>
                                            <form method="post" id="item-delete-{{ $evt->id }}" action="{{ route('schedule.delete-event') }}" class="d-none">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $evt->id }}">
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($selected)
            {{--Edit Event--}}
            <div class="{{ $selected ? 'tab-pane fade show active' : 'tab-pane fade' }}" id="edit-event">
                <div class="row">
                    <div class="col-md-10 offset-md-1">
                        <form method="post" action="{{ route('schedule.update-event', $event->id) }}">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-5">
                                    <label class="font-weight-semibold">Name <span class="text-danger">*</span></label>
                                    <input name="name" required type="text" class="form-control" value="{{ $event->name }}" maxlength="50">
                                </div>
                                <div class="form-group col-md-7">
                                    <label class="font-weight-semibold">Description <span class="text-danger">*</span></label>
                                    <input name="description" required type="text" class="form-control" value="{{ $event->description }}" maxlength="150">
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label class="font-weight-semibold">Year <span class="text-danger">*</span></label>
                                    <input name="year" required type="number" min="2000" class="form-control" value="{{ $event->year }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="font-weight-semibold">Month <span class="text-danger">*</span></label>
                                    <input name="month" required type="number" min="1" max="12" class="form-control" value="{{ $event->month }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="font-weight-semibold">Day <span class="text-danger">*</span></label>
                                    <input name="day" required type="number" min="1" max="31" class="form-control" value="{{ $event->day }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label class="font-weight-semibold">Status</label>
                                    <select class="form-control select" name="status">
                                        @foreach(['new', 'pending', 'completed', 'cancelled'] as $st)
                                        <option {{ $event->status == $st ? 'selected' : '' }} value="{{ $st }}">{{ strtoupper($st) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Update Event <i class="material-symbols-rounded ml-2">send</i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this event?')) {
        document.getElementById('item-delete-' + id).submit();
    }
}
</script>