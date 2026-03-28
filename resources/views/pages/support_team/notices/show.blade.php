{{-- Unviewed Section --}}
@if(isset($unviewed_notices) && $unviewed_notices->isNotEmpty())
<div class="card m-0 border-0" id="unviewed" data-pagination-meta="{{ $unviewed_notices->count() }}">
    @foreach($unviewed_notices->groupBy('user.name') as $name => $values)
    <div class="card-header">
        <span class="float-left pr-10 status-styled">Unviewed</span><i class="float-right status-styled">Posted by {{ ucwords($name) }}</i>
    </div>
    <div class="card-body p-1">
        @foreach($values as $untc)
        <div id="accordion-{{ $untc->id }}">
            <div class="card mb-1 border-0">
                <div class="card-header" id="headingOne-{{ $untc->id }}">
                    <h5 class="mb-0 d-flex">
                        <span class="pr-1 text-muted iteration font-size-xs">{{ $loop->iteration }}</span>
                        <button id="{{ $untc->id }}" onclick="setNoticeAsViwed(this)" class="btn w-100 text-left p-1 unviewed" data-toggle="collapse" data-target="#collapseOne-{{ $untc->id }}">
                            <span class="float-left pr-10 title">{{ $untc->title }}</span>
                            <small class=" float-right">posted {{ $untc->created_at->diffForHumans() }}</small>
                        </button>
                    </h5> 
                </div>
                <div id="collapseOne-{{ $untc->id }}" class="collapse" data-parent="#accordion-{{ $untc->id }}">
                    <div class="card-body">
                        <span class="pl-3">*</span>
                        {{ $untc->body }}
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endforeach
</div>
@endif

@if(!$is_ajax_req) <hr> @endif

{{-- Viewed Section --}}
@if(isset($viewed_notices) && $viewed_notices->isNotEmpty())
<div class="card m-0 border-0" id="viewed">
    @foreach($viewed_notices->groupBy('user.name') as $name => $values)
    <div class="card-header bg-light">
        <span class="float-left pr-10 status-styled">Viewed</span><i class="float-right status-styled">Posted by {{ ucwords($name) }}</i>
    </div>
    <div class="card-body p-1">
        @foreach($values as $vntc)
        <div id="accordion-v-{{ $vntc->id }}">
            <div class="card mb-1 border-0">
                <div class="card-header">
                    <button class="btn w-100 text-left p-1" data-toggle="collapse" data-target="#collapseV-{{ $vntc->id }}">
                        {{ $vntc->title }} <small class="float-right">{{ $vntc->created_at->diffForHumans() }}</small>
                    </button>
                </div>
                <div id="collapseV-{{ $vntc->id }}" class="collapse" data-parent="#accordion-v-{{ $vntc->id }}">
                    <div class="card-body">
                        <span class="pl-3">*</span>
                        {{ $vntc->body }}
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endforeach
</div>
@endif