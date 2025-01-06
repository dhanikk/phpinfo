@extends('phpinfo::layouts.app')

@section('phpinfo::content')
<div class="container mt-5">
    <h1 class="text-center">PHP Information</h1>
    <div class="row d-flex mb-3">
            <div class="col-md-6 flex-fill">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">PHP Core Information</div>
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
                                        <td>PHP Version</td>
                                        <td>{{isset($phpinfo['php_version']) ? $phpinfo['php_version'] : ''}}</td>
                                    </tr>
                                    <tr>
                                        <td>Server OS</td>
                                        <td>{{isset($phpinfo['server_os']) ? $phpinfo['server_os'] : ''}}</td>
                                    </tr>
                                    <tr>
                                        <td>Server IP</td>
                                        <td>{{isset($phpinfo['server_ip']) ? $phpinfo['server_ip'] : ''}}</td>
                                    </tr>
                                    <tr>
                                        <td>php.ini Location</td>
                                        <td>{{isset($phpinfo['php_ini']) ? $phpinfo['php_ini'] : ''}}</td>
                                    </tr>
                                    <tr>
                                        <td>php.ini Scan Files</td>
                                        <td>{{isset($phpinfo['php_ini_scanned_files']) ? $phpinfo['php_ini_scanned_files'] : ''}}</td>
                                    </tr>
                                    <tr>
                                        <td>PHP Server API</td>
                                        <td>{{isset($phpinfo['php_api']) ? $phpinfo['php_api'] : ''}}</td>
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
                        <div class="card-title">
                            <div>Available Extensions</div>
                        </div>
                        <div>
                            <input type="text" name="search_extension" id="search_extension" class="form-control form-control-sm" placeholder="Search Extension" />
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="card-text table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">Name</th>
                                        <th scope="col">Is Loaded</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($phpinfo['all_available_extensions'] as $infokey => $extension)
                                        <tr class="available_extension" data-extension="{{ $extension }}">
                                            <td>
                                                {{ $extension }} <i class="bi bi-info-circle-fill fs-6"  tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="{{\Config::get('app.modules.'.$extension)}}"></i>
                                            </td>
                                            <td>
                                                @if (is_string($extension) && extension_loaded($extension))
                                                    <i class="bi bi-check-circle-fill text-success"></i>
                                                @else
                                                    <i class="bi bi-x-circle-fill text-danger"></i>
                                                @endif
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

    <div class="row mb-3">
        <div class="col-md-3 flex-fill">
            <div class="card">
                <div class="card-header d-flex justify-content-between">
                    <div class="card-title">PHP Configuration Directives</div>
                    <div>
                        <input type="text" name="search_php_directives" id="search_php_directives" class="form-control form-control-sm" placeholder="Search Directive" />
                    </div>
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
                                <tr class="configuration_directive" data-directive="{{isset($phpinfo['memory_limit']) ? $phpinfo['memory_limit'] : ''}}" data-directivelabel="Memory Limit">
                                    <td>Memory Limit</td>
                                    <td>{{isset($phpinfo['memory_limit']) ? $phpinfo['memory_limit'] : ''}}</td>
                                </tr>
                                <tr class="configuration_directive" data-directive="{{isset($phpinfo['max_execution_time']) ? $phpinfo['max_execution_time'] : ''}}" data-directivelabel="Max Execution Time">
                                    <td>Max Execution Time</td>
                                    <td>{{isset($phpinfo['max_execution_time']) ? $phpinfo['max_execution_time'] : ''}}</td>
                                </tr>
                                <tr class="configuration_directive" data-directive="{{isset($phpinfo['upload_max_filesize']) ? $phpinfo['upload_max_filesize'] : ''}}" data-directivelabel="Upload Max Filesize">
                                    <td>Upload Max Filesize</td>
                                    <td>{{isset($phpinfo['upload_max_filesize']) ? $phpinfo['upload_max_filesize'] : ''}}</td>
                                </tr>
                                <tr class="configuration_directive" data-directive="{{isset($phpinfo['post_max_size']) ? $phpinfo['post_max_size'] : ''}}" data-directivelabel="Post Max Size">
                                    <td>Post Max Size</td>
                                    <td>{{isset($phpinfo['post_max_size']) ? $phpinfo['post_max_size'] : ''}}</td>
                                </tr>
                                <tr class="configuration_directive" data-directive="{{isset($phpinfo['php_error_reporting']) ? $phpinfo['php_error_reporting'] : ''}}" data-directivelabel="Error Reporting">
                                    <td>Error Reporting</td>
                                    <td>{{isset($phpinfo['php_error_reporting']) ? $phpinfo['php_error_reporting'] : ''}}</td>
                                </tr>
                                <tr class="configuration_directive" data-directive="{{isset($phpinfo['display_errors']) ? $phpinfo['display_errors'] : ''}}" data-directivelabel="Display errors">
                                    <td>Display errors</td>
                                    <td>{{isset($phpinfo['display_errors']) ? $phpinfo['display_errors'] : ''}}</td>
                                </tr>
                                <tr class="configuration_directive" data-directive="{{isset($phpinfo['log_errors']) ? $phpinfo['log_errors'] : ''}}" data-directivelabel="Error Log">
                                    <td>Log errors</td>
                                    <td>{{isset($phpinfo['log_errors']) ? $phpinfo['log_errors'] : ''}}</td>
                                </tr>
                                <tr class="configuration_directive" data-directive="{{isset($phpinfo['error_log']) ? $phpinfo['error_log'] : ''}}" data-directivelabel="Log errors">
                                    <td>Error Log</td>
                                    <td>{{isset($phpinfo['error_log']) ? $phpinfo['error_log'] : ''}}</td>
                                </tr>
                                <tr class="configuration_directive" data-directive="{{isset($phpinfo['max_file_uploads']) ? $phpinfo['max_file_uploads'] : ''}}" data-directivelabel="Max File Uploads">
                                    <td>Max File Uploads</td>
                                    <td>{{isset($phpinfo['max_file_uploads']) ? $phpinfo['max_file_uploads'] : ''}}</td>
                                </tr>
                                <tr class="configuration_directive" data-directive="{{isset($phpinfo['max_input_vars']) ? $phpinfo['max_input_vars'] : ''}}" data-directivelabel="Max input vars">
                                    <td>Max input vars</td>
                                    <td>{{isset($phpinfo['max_input_vars']) ? $phpinfo['max_input_vars'] : ''}}</td>
                                </tr>
                                <tr class="configuration_directive" data-directive="{{isset($phpinfo['default_timezone']) ? $phpinfo['default_timezone'] : ''}}" data-directivelabel="Default Timezone">
                                    <td>Default Timezone</td>
                                    <td>{{isset($phpinfo['default_timezone']) ? $phpinfo['default_timezone'] : ''}}</td>
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
                    <div class="card-title">Available Modules</div>
                    <div>
                        <input type="text" name="search_modules" id="search_modules" class="form-control form-control-sm" placeholder="Search Module" />
                    </div>
                </div>
                <div class="card-body">
                    <div class="card-text table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Is Loaded</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (explode(", ",$phpinfo['php_modules']) as $key => $module)
                                    <tr class="php_modules" data-module="{{isset($module) ? $module : ''}}">
                                        <td>
                                            {{ $module }} <i class="bi bi-info-circle-fill fs-6"  tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="{{\Config::get('app.modules.'.$module)}}"></i>
                                        </td>
                                        <td>
                                            @if (is_string($module))
                                                <i class="bi bi-check-circle-fill text-success"></i>
                                            @else
                                                <i class="bi bi-x-circle-fill text-danger"></i>
                                            @endif
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

    {{-- <div class="row mb-3">
        <div class="col-md-3 flex-fill">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Disk Space</div>
                </div>
                <div class="card-body">
                    <div class="card-text table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th scope="col"></th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Available Disk Space / Total Disk Space</td>
                                    <td>{{isset($phpinfo['available_disk_space']) ? $phpinfo['available_disk_space'] : ''}} / {{isset($phpinfo['total_disk_space']) ? $phpinfo['total_disk_space'] : ''}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    {{-- <div>
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th scope="col" class="col-md-2">Parameter</th>
                    <th scope="col">Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($phpinfo as $key => $info)
                    <tr>
                        <td>{{ ucfirst(str_replace('_', ' ', $key)) }}</td>
                        @if (is_array($info))
                            <td>
                                <span class="me-2">
                                    @foreach ($info as $infokey => $extension)

                                        @if (is_string($infokey))
                                                <span class="fw-bold">{{ strtoupper(str_replace('_', ' ', $infokey)) }}:</span>
                                        @endif
                                        @if (is_string($extension))
                                            <i class="bi bi-check-circle-fill text-success"></i> {{ $extension }}
                                        @elseif (is_bool($extension))
                                            {{ ($extension) ? 'True' : 'False' }}
                                        @elseif (is_array($extension))
                                            <table class="table table-bordered table-hover">
                                                @foreach ($extension as $subkey => $subvalue)

                                                <tr>
                                                    @if (is_string($subkey))
                                                    <td>{{ ucfirst(str_replace('_', ' ', $subkey)) }}</td>
                                                    @endif
                                                    @if (is_string($subvalue))
                                                    <td>{{ $subvalue }}</td>
                                                    @endif
                                                </tr>
                                                @endforeach
                                            </table>
                                        @endif
                                    </span>
                                @endforeach
                            </td>
                        @else
                            <td>{{ $info }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div> --}}
</div>
@endsection
@section('phpinfo::script')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById('search_extension').addEventListener("keyup", function(e) {
                let value = this.value;
                if(value == ""){
                    document.querySelectorAll(".available_extension").forEach(function(extensionElement) {
                        extensionElement.classList.remove("d-none");
                    });
                } else {
                    document.querySelectorAll(".available_extension").forEach(function(extensionElement) {
                        if (extensionElement.dataset.extension.toLowerCase().includes(value.toLowerCase())) {
                            extensionElement.classList.remove("d-none");
                        } else {
                            extensionElement.classList.add("d-none");
                        }
                    });
                }
            });


            document.getElementById('search_php_directives').addEventListener("keyup", function(e) {
                let value = this.value;
                if(value == ""){
                    document.querySelectorAll(".configuration_directive").forEach(function(extensionElement) {
                        extensionElement.classList.remove("d-none");
                    });
                } else {
                    document.querySelectorAll(".configuration_directive").forEach(function(extensionElement) {
                        if (extensionElement.dataset.directive.toLowerCase().includes(value.toLowerCase()) || extensionElement.dataset.directivelabel.toLowerCase().includes(value.toLowerCase())) {
                            extensionElement.classList.remove("d-none");
                        } else {
                            extensionElement.classList.add("d-none");
                        }
                    });
                }
            });

            document.getElementById('search_modules').addEventListener("keyup", function(e) {
                let value = this.value;
                if(value == ""){
                    document.querySelectorAll(".php_modules").forEach(function(extensionElement) {
                        extensionElement.classList.remove("d-none");
                    });
                } else {
                    document.querySelectorAll(".php_modules").forEach(function(extensionElement) {
                        if (extensionElement.dataset.module.toLowerCase().includes(value.toLowerCase())) {
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
