@forelse($messages as $m)
    <div class="d-flex mb-3 {{ $m->sender_id == auth()->id() ? 'justify-content-end text-right' : 'justify-content-start text-left' }}">
        <div class="p-2 px-3 shadow-sm {{ $m->sender_id == auth()->id() ? 'bg-primary text-white' : 'bg-white text-dark' }}" 
             style="max-width: 70%; border-radius: 15px; border-bottom-{{ $m->sender_id == auth()->id() ? 'right' : 'left' }}-radius: 2px;">
            <div>{{ $m->message }}</div>
            <div class="small mt-1 opacity-70" style="font-size: 0.65rem;">
                {{ $m->created_at->diffForHumans() }}
            </div>
        </div>
    </div>
@empty
    <div class="text-center my-5">
        <div class="p-4">
            <i class="icon-bubble-dots4 icon-3x text-muted opacity-20"></i>
            <h5 class="text-muted mt-3">No messages yet</h5>
            <p class="text-muted small">Send a message to start your conversation with {{ $active_user->name ?? 'this user' }}.</p>
        </div>
    </div>
@endforelse