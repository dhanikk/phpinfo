@extends('phpinfo::layouts.app')

@section('phpinfo::content')
<div class="container mt-5">
    <h1 class="text-center">Database Information</h1>
    <div class="row d-flex mb-3">
        <div class="col-md-6 flex-fill">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">{{isset($dbinfo['database_connection']) ? ucfirst($dbinfo['database_connection']) : 'Database'}} Core Information</div>
                </div>
                <div class="card-body">
                    <div class="card-text table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                {{-- <tr>
                                    <th scope="col"></th>
                                    <th scope="col"></th>
                                </tr> --}}
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Database Connection</td>
                                    <td>{{isset($dbinfo['database_connection']) ? $dbinfo['database_connection'] : ''}}</td>
                                </tr>
                                <tr>
                                    <td>Database version</td>
                                    <td>{{isset($dbinfo['database_version']) ? $dbinfo['database_version'] : ''}}</td>
                                </tr>
                                <tr>
                                    <td>Database Name</td>
                                    <td>{{isset($dbinfo['database_name']) ? $dbinfo['database_name'] : ''}}</td>
                                </tr>
                                <tr>
                                    <td>Database Host</td>
                                    <td>{{isset($dbinfo['database_host']) ? $dbinfo['database_host'] : ''}}</td>
                                </tr>
                                <tr>
                                    <td>Database Charset</td>
                                    <td>{{isset($dbinfo['database_characterset']) ? $dbinfo['database_characterset']->charset : ''}}</td>
                                </tr>
                                <tr>
                                    <td>Protocol Version</td>
                                    <td>{{isset($dbinfo['protocol_version']) ? $dbinfo['protocol_version'] : ''}}</td>
                                </tr>
                                <tr>
                                    <td>Database Collation</td>
                                    <td>{{isset($dbinfo['collation']) ? $dbinfo['collation'] : ''}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 flex-fill">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="card-title">Database Tables</div>
                    <div>
                        <input type="text" name="search_tables" id="search_tables" class="form-control form-control-sm" placeholder="Search" />
                    </div>
                </div>
                <div class="card-body">
                    <div class="card-text table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Size (MB)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dbinfo['tables'] as $key => $table)
                                    <tr class="db_tables" data-table="{{isset($table) ? $table->table_name : ''}}" data-size="{{isset($table) ? $table->size : ''}}">
                                        <td>
                                            {{ $table->table_name }}
                                        </td>
                                        <td>
                                            {{ $table->size }} MB
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-3 flex-fill">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="card-title">Query Analysis&nbsp;&nbsp;<span class="font-weight-bold">{{ !empty(\Cache::get('query_logs') && count(\Cache::get('query_logs', [])) > 0) ? 'Total Queries: ('.count(\Cache::get('query_logs')).')': ''}}</span></div>
                    <div>
                        
                        <input type="text" name="sql_tables" id="sql_tables" class="form-control form-control-sm" placeholder="Search" />
                    </div>
                </div>
                <div class="card-body">
                    <div class="card-text table-responsive">
                        <table class="table table-bordered table-hover small">
                            <thead>
                                <tr>
                                    <th>SQL Query</th>
                                    <th>Execution Time (ms)</th>
                                    <th>Model</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(\Cache::get('query_logs', []) as $index => $log)
                                    <tr>
                                        <td>{{ $log['sql'] }}</td>
                                        <td>{{ $log['time'] }} {!! (isset($log['threshold']) && !is_null($log['threshold'])) ? '<span class="badge rounded-pill bg-danger float-end">' .ucfirst($log['threshold']).'</span>' : '' !!}</td>
                                        <td>{{ isset($log['model']) ? $log['model'] : '' }}</td>
                                        <td>{{ $log['timestamp'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No query logs found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('phpinfo::script')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById('search_tables').addEventListener("keyup", function(e) {
                let value = this.value;
                if(value == ""){
                    document.querySelectorAll(".db_tables").forEach(function(extensionElement) {
                        extensionElement.classList.remove("d-none");
                    });
                } else {
                    document.querySelectorAll(".db_tables").forEach(function(extensionElement) {
                        if (extensionElement.dataset.table.toLowerCase().includes(value.toLowerCase()) || extensionElement.dataset.size.toLowerCase().includes(value.toLowerCase())) {
                            extensionElement.classList.remove("d-none");
                        } else {
                            extensionElement.classList.add("d-none");
                        }
                    });
                }
            });
            
        });
    </script>
@endsection
