@extends('layouts.master')
@section('page_title', 'Send Announcements')
@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-primary text-white">
        <h6 class="card-title">Create Real-Time Announcement</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('announcement.send') }}" method="POST">
            @csrf
            <div class="row">
                {{-- Target Selection --}}
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="font-weight-bold">Send To:</label>
                        <select name="target" id="target_type" class="form-control select">
                            <option value="all_parents">All Parents</option>
                            <option value="class">Specific Class</option>
                            <option value="parent">Specific Parent</option>
                        </select>
                    </div>
                </div>

                {{-- Dynamic Filter: Class (Hidden by default) --}}
                <div class="col-md-4 d-none" id="class_select">
                    <div class="form-group">
                        <label class="font-weight-bold">Select Class:</label>
                        <select name="class_id" class="form-control select">
                            @foreach($my_classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Dynamic Filter: Parent (Hidden by default) --}}
                <div class="col-md-4 d-none" id="parent_select">
                    <div class="form-group">
                        <label class="font-weight-bold">Search Parent:</label>
                        <select name="receiver_id" class="form-control select-search">
                            @foreach($parents as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Title</label>
                <input type="text" name="title" class="form-control" placeholder="Urgent: School Holiday" required>
            </div>

            <div class="form-group">
                <label class="font-weight-bold">Message Content</label>
                <textarea name="message" rows="4" class="form-control" placeholder="Type your announcement here..." required></textarea>
            </div>

            <button type="submit" class="btn btn-primary shadow-sm"><i class="icon-paperplane mr-2"></i> Broadcast Now</button>
        </form>
    </div>
</div>

<script>
    // Logic to toggle inputs based on selection
    $('#target_type').change(function(){
        let val = $(this).val();
        $('#class_select, #parent_select').addClass('d-none');
        if(val === 'class') $('#class_select').removeClass('d-none');
        if(val === 'parent') $('#parent_select').removeClass('d-none');
    });
</script>
@endsection