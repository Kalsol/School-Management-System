@extends('layouts.master')
@section('page_title', 'Chat with ' . $active_user->name)

@section('content')
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

    {{-- Main Chat Container --}}
    <div class="col-md-8">
        {{-- Main Chat Container: Using flex-column to stack Header, Body, and Footer --}}
        <div id="chat-box-main" class="card shadow-sm h-100 mb-0 d-flex flex-column"> 
        
        {{-- 1. Header: Modern style with Back button and Avatar --}}
        <div class="card-header bg-white d-flex align-items-center py-1 border-bottom">
            <a href="{{ route('chat.index') }}" class="btn btn-link text-dark p-0 mr-3 px-2 py-1">
                <i class="icon-arrow-left8 icon-xl"></i>
            </a>
            <img src="{{ $active_user->photo }}" alt="" class="rounded-circle mr-3" width="20" height="20">
            <div class="d-flex flex-column">
                <h6 class="mb-0 font-weight-bold" id="active-user-name">{{ $active_user->name }}</h6>
                <small class="text-success">
                    <i class="icon-primitive-dot mr-1"></i> Active Chat
                </small>
            </div>
        </div>

        {{-- 2. Messages Area: flex-grow-1 fills the space between header and footer --}}
        <div class="card-body p-3 flex-grow-1" id="chat-window" style="overflow-y: auto; background-color: #e5ddd5; background-image: url('https://www.transparenttextures.com/patterns/cubes.png');">
            <div id="message-container">
                @include('pages.support_team.messages.messages_list', ['messages' => $messages])
            </div>
        </div>

        {{-- 3. Footer: Modern Pill-style Input Form --}}
        <div class="card-footer bg-white p-3 border-top">
            <form id="chat-form-single" autocomplete="off" onSubmit="return false;">
                <div class="input-group align-items-center">
                    <input type="text" id="chat-input-single" 
                           class="form-control border-0 bg-light rounded-pill px-4 py-2" 
                           placeholder="Write a message..." 
                           style="font-size: 15px; height: 45px;" 
                           required>
                    
                    <div class="input-group-append ml-2">
                        <button type="submit" class="btn btn-primary btn-icon rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                            <i class="icon-paperplane"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>


{{-- Scripts --}}
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.11.3/dist/echo.iife.js"></script>
<script>
$(document).ready(function() {
    const chatWindow = document.getElementById("chat-window");
    const currentActiveId = "{{ $active_user->id }}";

    function scrollToBottom() {
        if (chatWindow) { chatWindow.scrollTop = chatWindow.scrollHeight; }
    }
    scrollToBottom();

    // --- INITIALIZE ECHO MANUALLY ---
    window.Pusher = Pusher;
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: 'f9e3c241ba45fcba1f84',
        cluster: 'ap2',
        forceTLS: true,
        // Since this is Laravel 8, we need to point to the auth endpoint
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }
    });

    // --- AJAX SEND LOGIC (KEEP YOURS) ---
    $('#chat-form-single').on('submit', function(e) {
        e.preventDefault();
        let messageInput = $('#chat-input-single');
        let messageText = messageInput.val();
        if (!messageText.trim()) return;

        $.ajax({
            url: "{{ route('chat.send') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                receiver_id: currentActiveId,
                message: messageText
            },
            success: function(response) {
                $('#message-container').append(`
                    <div class="d-flex mb-3 justify-content-end text-right">
                        <div class="p-2 px-3 shadow-sm bg-primary text-white" style="max-width: 75%; border-radius: 15px 15px 0 15px;">
                            <div style="font-size: 15px;">${messageText}</div>
                            <div class="small mt-1" style="font-size: 10px; opacity: 0.8;">Just now</div>
                        </div>
                    </div>`);
                messageInput.val('');
                scrollToBottom();
            }
        });
    });

    // --- LISTEN FOR REALTIME MESSAGES ---
    window.Echo.private("chat.{{ auth()->id() }}")
        .listen('MessageSent', (e) => {
            console.log("Event Received:", e);
            // Only show message if it's from the person I'm currently talking to
            if (e.message.sender_id == currentActiveId) {
                $('#message-container').append(`
                    <div class="d-flex mb-3 justify-content-start text-left">
                        <div class="p-2 px-3 shadow-sm bg-white text-dark" style="max-width: 75%; border-radius: 15px 15px 15px 0;">
                            <div style="font-size: 15px;">${e.message.message}</div>
                            <div class="small text-muted mt-1" style="font-size: 10px;">Just now</div>
                        </div>
                    </div>`);
                scrollToBottom();
            } else {
                alert("New message from someone else!");
            }
        });
});
</script>

<style>
    /* Ensure the chat window doesn't show standard scrollbars on some browsers */
    #chat-window::-webkit-scrollbar {
        width: 6px;
    }
    #chat-window::-webkit-scrollbar-thumb {
        background-color: rgba(0,0,0,0.1);
        border-radius: 10px;
    }
    /* Simple styling for chat bubbles spacing */
    #message-container {
        display: flex;
        flex-direction: column;
    }
</style>
@endsection