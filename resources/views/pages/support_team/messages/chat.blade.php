@extends('layouts.master')
@section('page_title', 'Messages')

@section('content')

{{-- Filter Section --}}
@if(Qs::userIsTeamSA())
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ Request::url() }}" method="GET" class="row align-items-end">
            <div class="form-group px-2">
                <label class="small font-weight-bold text-uppercase">Filter by Class</label>
                <select name="my_class_id" class="form-control select-search" onchange="this.form.submit()">
                    <option value="">All Classes</option>
                    @foreach($my_classes as $c)
                        <option {{ request('my_class_id') == $c->id ? 'selected' : '' }} value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
                
            @if(request('my_class_id'))
            <div class="form-group px-2">
                <label class="small font-weight-bold text-uppercase">Filter by Section</label>
                <select name="section_id" class="form-control select" onchange="this.form.submit()">
                    <option value="">All Sections</option>
                    @foreach($sections->where('my_class_id', request('my_class_id')) as $s)
                        <option {{ request('section_id') == $s->id ? 'selected' : '' }} value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </form>
    </div>
</div>
@endif

<div class="row" style="height: 70vh;">

    {{-- User List Section --}}
    <div class="col-md-4">
        <div class="card shadow-sm mb-0" style="height: 80vh; display: flex; flex-direction: column;">
            
            <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
                <h6 class="mb-0 font-weight-bold"><i class="icon-users mr-2"></i>Conversations</h6>
                <span class="badge badge-pill badge-secondary">{{ $chat_users->count() }}</span>
            </div>
    
            <div class="p-2 border-bottom bg-white">
                <input type="text" id="chatSearch" class="form-control form-control-sm" placeholder="Search parents...">
            </div>
    
            <div class="list-group list-group-flush" style="overflow-y: auto; flex: 1; scrollbar-width: thin;">
                @forelse($chat_users as $user)
                    <a href="{{ route('chat.show', Qs::hash($user->id)) }}" 
                       class="list-group-item list-group-item-action user-chat-item">
                        <div class="d-flex align-items-center">
                            <img src="{{ $user->photo }}" alt="Avatar" class="rounded-circle mr-2" 
                                 style="width: 35px; height: 35px; object-fit: cover; border: 1px solid #ddd;">
                            
                            <div class="w-100">
                                <div class="d-flex justify-content-between">
                                    <span class="font-weight-semibold text-dark">{{ $user->name }}</span>
                                    @if($user->last_interaction)
                                        <small class="text-muted" style="font-size: 0.7rem;">
                                            {{ \Carbon\Carbon::parse($user->last_interaction)->diffForHumans(null, true) }}
                                        </small>
                                    @endif
                                </div>
                                <small class="text-muted d-block text-truncate" style="max-width: 150px;">
                                    {{ ucfirst($user->user_type) }}
                                </small>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="text-center p-4">
                        <p class="text-muted">No users found.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Chat Window Section --}}
    <div class="col-md-8">
        <div id="chat-wrapper" class="h-100">
            
            {{-- 1. Placeholder: Shown when NO user is selected --}}
            <div id="chat-placeholder" class="card shadow-sm h-100 mb-0 d-flex align-items-center justify-content-center bg-light {{ isset($active_user) ? 'd-none' : '' }}">
                <div class="text-center">
                    <i class="icon-bubbles4 icon-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Select a user to start chatting</h5>
                </div>
            </div>

            <!-- {{-- 2. Chat Box: Hidden by default (d-none) unless active_user is set --}}
            <div id="chat-box-main" class="card shadow-sm h-100 mb-0 d-flex flex-column {{ isset($active_user) ? '' : 'd-none' }}">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 font-weight-bold">Chatting with <span id="active-user-name">{{ $active_user->name ?? '' }}</span></h6>
                </div>

                <div class="card-body p-3 flex-grow-1" id="chat-window" style="overflow-y: auto; background-color: #f0f2f5;">
                    <div id="message-container">
                        @if(isset($active_user))
                            @include('pages.support_team.messages.messages_list', ['messages' => $messages])
                        @endif
                    </div>
                </div>

                <div class="card-footer bg-white border-top p-2">
                    <form id="chat-form" autocomplete="off">
                        <div class="input-group">
                            <input type="text" id="chat-input" class="form-control border-0 shadow-none" placeholder="Type a message..." required>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary btn-icon rounded-circle ml-2">
                                    <i class="icon-paperplane"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div> -->

        </div>
    </div>
</div>

<script>
    document.getElementById('chatSearch')?.addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        document.querySelectorAll('.user-chat-item').forEach(function(item) {
            let name = item.querySelector('.font-weight-semibold').textContent.toLowerCase();
            item.style.display = name.includes(value) ? "block" : "none";
        });
    });
</script>

@endsection