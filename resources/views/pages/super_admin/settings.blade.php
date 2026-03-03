@extends('layouts.master')
@section('page_title', 'Manage System Settings')
@section('content')

    <!--School Information-->
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title font-weight-semibold">Update School Information</h6>
            {!! Qs::getPanelOptions() !!}
        </div>
        <div class="card-body">
            <form enctype="multipart/form-data" method="post" action="{{ route('settings.update') }}">
                @csrf
                @method('PUT')

                <!-- ROW 1 -->
                <div class="row">
                    <!-- COL 1 -->
                    <div class="col-md-4 border-right border-primary">
                        <div class="form-group">
                            <label class="font-weight-semibold">Name of School <span class="text-danger">*</span></label>
                            <input name="system_name" value="{{ $s['system_name'] }}" required
                                   type="text" class="form-control" placeholder="Name of School">
                        </div>

                        <div class="form-group">
                            <label class="font-weight-semibold">School Acronym</label>
                            <input name="system_title" value="{{ $s['system_title'] }}"
                                   type="text" class="form-control" placeholder="School Acronym">
                        </div>

                        <div class="form-group">
                            <label class="font-weight-semibold">School Address <span class="text-danger">*</span></label>
                            <input required name="address" value="{{ $s['address'] }}"
                                   type="text" class="form-control" placeholder="School Address">
                        </div>
                    </div>

                    <!-- COL 2 -->
                    <div class="col-md-4 border-right border-primary">
                        <div class="form-group">
                            <label class="font-weight-semibold">Current Session <span class="text-danger">*</span></label>
                            <select required name="current_session" class="form-control select-search">
                                <option value=""></option>
                                @php
                                    $currentEthYear = date('Y') - 8;
                                    $startYear = $currentEthYear - 3;
                                    $endYear = $currentEthYear + 1;
                                @endphp
                                @for($y = $startYear; $y <= $endYear; $y++)
                                    <option value="{{ $y }}" {{ ($s['current_session'] == $y) ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-semibold">This Term Ends</label>
                            <input name="term_ends" value="{{ $s['term_ends'] }}"
                                   type="text" class="form-control date-pick">
                        </div>

                        <div class="form-group">
                            <label class="font-weight-semibold">Next Term Begins</label>
                            <input name="term_begins" value="{{ $s['term_begins'] }}"
                                   type="text" class="form-control date-pick">
                        </div>
                    </div>

                    <!-- COL 3 -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="font-weight-semibold">Phone</label>
                            <input name="phone" value="{{ $s['phone'] }}"
                                   type="text" class="form-control" placeholder="Phone">
                        </div>

                        <div class="form-group">
                            <label class="font-weight-semibold">School Email</label>
                            <input name="system_email" value="{{ $s['system_email'] }}"
                                   type="email" class="form-control" placeholder="School Email">
                        </div>
                    </div>

                </div>

                <!-- ROW 2 -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-semibold">Change Logo</label>
                            <div class="mb-2">
                                <img src="{{ $s['logo'] }}" width="100" height="100">
                            </div>
                            <input name="logo" accept="image/*" type="file" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6 text-right align-self-end">
                        <button type="submit" class="btn btn-danger">
                            Submit from <i class="icon-paperplane ml-2"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!--System Settings-->
        <div class="card">
            <div class="card-header header-elements-inline">
                <h6 class="card-title font-weight-semibold">Maintenance & System Commands</h6>
                {!! Qs::getPanelOptions() !!}
            </div>
    
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 border-right border-primary">
                        <h6 class="font-weight-bold text-uppercase font-size-sm text-primary">Optimization</h6>
                        <p class="text-muted small">Clear application cache and compiled views after updates.</p>
                        
                        <div class="list-group list-group-flush">
                            <form action="{{ route('admin.system.run') }}" method="POST" class="mb-2">
                                @csrf
                                <button name="command" value="cache:clear" class="btn btn-light btn-block text-left">
                                    <i class="icon-spinner4 mr-2"></i> Clear App Cache
                                </button>
                            </form>
                            <form action="{{ route('admin.system.run') }}" method="POST" class="mb-2">
                                @csrf
                                <button name="command" value="view:clear" class="btn btn-light btn-block text-left">
                                    <i class="icon-eye-blocked mr-2"></i> Clear View Cache
                                </button>
                            </form>
                            <form action="{{ route('admin.system.run') }}" method="POST" class="mb-2">
                                @csrf
                                <button name="command" value="config:clear" class="btn btn-light btn-block text-left">
                                    <i class="icon-cog mr-2"></i> Clear Config Cache
                                </button>
                            </form>
                            <form action="{{ route('admin.system.run') }}" method="POST" class="mb-2">
                                @csrf
                                <button name="command" value="route:clear" class="btn btn-light btn-block text-left">
                                    <i class="icon-route mr-2"></i> Clear Route Cache
                                </button>
                            </form>
                        </div>
                    </div>
    
                    <div class="col-md-4 border-right border-primary">
                        <h6 class="font-weight-bold text-uppercase font-size-sm text-primary">Production Refresh</h6>
                        <p class="text-muted small">Use these commands on production to boost performance.</p>
                        
                        <div class="list-group list-group-flush">
                            <form action="{{ route('admin.system.run') }}" method="POST" class="mb-2">
                                @csrf
                                <button name="command" value="config:cache" class="btn btn-light btn-block text-left">
                                    <i class="icon-cfg mr-2"></i> Re-Cache Config
                                </button>
                            </form>
                            
                            <form action="{{ route('admin.system.run') }}" method="POST" class="mb-2">
                                @csrf
                                <button name="command" value="storage:link" class="btn btn-light btn-block text-left">
                                    <i class="icon-link mr-2"></i> Link Storage Folder
                                </button>
                            </form>
                        </div>
                    </div>
    
                    <div class="col-md-4">
                        <h6 class="font-weight-bold text-uppercase font-size-sm text-danger">Database Safety</h6>
                        <p class="text-muted small">Immediately trigger a full system backup (DB + Files).</p>
                        
                        <form action="{{ route('admin.system.run') }}" method="POST">
                            @csrf
                            <button name="command" value="backup:run" class="btn btn-danger btn-block py-2">
                                <i class="icon-database-insert mr-2"></i> RUN FULL BACKUP NOW
                            </button>
                        </form>
                        <form action="{{ route('admin.system.run') }}" method="POST" class="mt-2">
                            @csrf
                            <button name="command" value="backup:clean" class="btn btn-outline-danger btn-block btn-sm">
                                <i class="icon-trash mr-2"></i> Clean Old Backups
                            </button>
                        </form>
                    </div>
                </div>
    
                <hr>
    
                <div class="row mt-4">
                    <div class="col-md-12">
                        <h6 class="card-title font-weight-semibold mb-3">
                            <i class="icon-history mr-2"></i> Available Backup Files
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr class="bg-light">
                                        <th>#</th>
                                        <th>File Name</th>
                                        <th>Size</th>
                                        <th>Date Created</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($backups as $key => $b)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td><code class="text-primary font-weight-bold">{{ $b['file_name'] }}</code></td>
                                            <td>{{ $b['file_size'] }}</td>
                                            <td>{{ $b['last_modified'] }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.system.download', $b['file_name']) }}" class="btn btn-info btn-sm">
                                                    <i class="icon-download4 mr-1"></i> Download
                                                </a>
                                                
                                                <form action="{{ route('admin.system.delete', $b['file_name']) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this backup file?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-outline-danger btn-sm ml-1">
                                                        <i class="icon-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No backup files found in storage.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    

    {{--Settings Edit Ends--}}

@endsection
