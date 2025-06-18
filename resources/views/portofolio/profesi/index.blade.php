@extends('layouts.app')

@section('title', 'Profesi')
@section('subtitle', 'Profesi')

@section('content_header')
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Beranda</a></li>
                <li class="breadcrumb-item active">Profesi</li>
            </ol>
        </nav>
    </div>
@endsection

@section('content')
    <div class="container-fluid">

        <!-- Profesi -->
        <div class="callout callout-primary shadow-sm">
            <h5>Profesi</h5>
            <p>Peningkatan kemampuan dosen melalui program profesi untuk mendukung kompetensi keilmuan.</p>
        </div>

        {{-- DataTable --}}
        <div class="card shadow-sm">
            <div class="card-header bg-primary border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0 text-white">Daftar Profesi</h3>
                    <div class="card-tools">
                        <a id="exportPdfBtn" class="btn btn-custom-blue me-2"
                            href="{{ route('portofolio.profesi.export_pdf') }}">
                            <i class="fa-solid fa-file-pdf me-2"></i> Export PDF
                        </a>
                        <a id="exportExcelBtn" class="btn btn-custom-blue me-2"
                            href="{{ route('portofolio.profesi.export_excel') }}">
                            <i class="fas fa-file-excel me-2"></i> Export Excel
                        </a>
                        @if ($isAdm || $isDos)
                            <button class="btn btn-custom-blue me-2"
                                onclick="modalAction('{{ route('portofolio.profesi.import') }}')">
                                <i class="fa-solid fa-file-arrow-up me-2"></i> Import Data
                            </button>
                            <button onclick="modalAction('{{ route('portofolio.profesi.create_ajax') }}')"
                                class="btn btn-custom-blue me-2">
                                <i class="fas fa-plus me-2"></i> Tambah Data
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body">

                <div class="row mb-3">
                    <div class="col-6 form-group">
                        <label for="filterSumberData">Filter Sumber Data:</label>
                        <select id="filterSumberData" class="form-control select2" style="width: 100%;" required>
                            <option value="">-- Filter Sumber Data --</option>
                            <option value="p3m">P3M</option>
                            <option value="dosen">Dosen</option>
                        </select>
                    </div>
                    <div class="col-6 form-group">
                        <label for="filterStatus">Filter Status:</label>
                        <select id="filterStatus" class="form-control select2" style="width: 100%;" required>
                            <option value="">-- Filter Status --</option>
                            <option value="tervalidasi">Tervalidasi</option>
                            <option value="perlu validasi">Perlu Validasi</option>
                            <option value="tidak valid">Tidak Valid</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    {{ $dataTable->table([
                        'id' => 'p_profesi-table',
                        'class' => 'table table-hover table-bordered table-striped',
                        'style' => 'width:100%',
                    ]) }}
                </div>
            </div>
        </div>

        {{-- Modal --}}
        <div id="myModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <!-- Konten modal akan diisi secara dinamis -->
                </div>
            </div>
        </div>

        @if ($isAdm || $isAng)
            <!-- Profesi -->
            <div class="callout callout-primary shadow-sm">
                <h5>Chart</h5>
                <p>Chart berikut menampilkan distribusi gelar akademik dan asal perguruan tinggi.
                </p>
            </div>

            <div class="container-fluid mt-3">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary border-bottom">
                                <h5 class="card-title mb-0 text-white">Distribusi Gelar Akademik</h5>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body collapse">
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <canvas id="pieChartGelar1"
                                            style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary border-bottom">
                                <h5 class="card-title mb-0 text-white">Distribusi Asal Perguruan Tinggi</h5>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body collapse">
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <canvas id="barChartPerguruanTinggi"
                                            style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection

@push('scripts')
    {!! $dataTable->scripts() !!}

    {{-- Modal --}}
    <script>
        function modalAction(url) {
            $.get(url)
                .done(function(response) {
                    $('#myModal .modal-content').html(response);
                    $('#myModal').modal('show');

                    $(document).off('submit', '#formCreateProfesi, #formEditProfesi');

                    $(document).on('submit', '#formCreateProfesi, #formEditProfesi', function(e) {
                        e.preventDefault();
                        var form = $(this);
                        var formData = new FormData(form[0]);
                        // Always use POST method for AJAX to handle file uploads and method spoofing
                        var method = 'POST';
                        // Append _method field if present in the form
                        var methodInput = form.find('input[name="_method"]');
                        if (methodInput.length) {
                            formData.append('_method', methodInput.val());
                        }
                        $.ajax({
                            url: form.attr('action'),
                            method: method,
                            data: formData,
                            processData: false,
                            contentType: false,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(res) {
                                $('#myModal').modal('hide');
                                window.LaravelDataTables["p_profesi-table"].ajax.reload();
                                if (res.alert && res.message) {
                                    Swal.fire({
                                        icon: res.alert,
                                        title: res.alert === 'success' ? 'Sukses' : 'Error',
                                        text: res.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                }
                            },
                            error: function(xhr) {
                                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON
                                    .msgField) {
                                    // Validation error
                                    var errors = xhr.responseJSON.msgField;
                                    $.each(errors, function(field, messages) {
                                        var input = form.find('[name="' + field + '"]');
                                        input.addClass('is-invalid');
                                        input.next('.invalid-feedback').text(messages[0]);
                                    });
                                } else {
                                    $('#myModal').modal('hide');
                                    window.LaravelDataTables["p_profesi-table"].ajax.reload();
                                    if (xhr.responseJSON && xhr.responseJSON.alert && xhr
                                        .responseJSON.message) {
                                        Swal.fire({
                                            icon: xhr.responseJSON.alert,
                                            title: xhr.responseJSON.alert === 'success' ?
                                                'Sukses' : 'Error',
                                            text: xhr.responseJSON.message,
                                            timer: 2000,
                                            showConfirmButton: false
                                        });
                                    } else {
                                        Swal.fire('Error!', 'Gagal menyimpan data.', 'error');
                                    }
                                }
                            }
                        });
                    });

                    $(document).off('submit', '#form-import');

                    // Handle import form submit
                    $(document).on('submit', '#form-import', function(e) {
                        e.preventDefault();
                        var form = $(this);
                        var formData = new FormData(form[0]);
                        var submitBtn = form.find('button[type="submit"]');

                        submitBtn.prop('disabled', true).html(
                            '<i class="fas fa-spinner fa-spin me-2"></i> Memproses...');

                        $.ajax({
                            url: form.attr('action'),
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                $('#myModal').modal('hide');
                                if (response.alert && response.message) {
                                    Swal.fire({
                                        icon: response.alert,
                                        title: response.alert === 'success' ?
                                            'Sukses' : 'Error',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.LaravelDataTables["p_profesi-table"].ajax
                                            .reload();
                                    });
                                }
                            },
                            error: function(xhr) {
                                $('#myModal').modal('hide');
                                if (xhr.responseJSON && xhr.responseJSON.alert && xhr.responseJSON
                                    .message) {
                                    Swal.fire({
                                        icon: xhr.responseJSON.alert,
                                        title: xhr.responseJSON.alert === 'success' ?
                                            'Sukses' : 'Error',
                                        text: xhr.responseJSON.message,
                                        showConfirmButton: true
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: xhr.responseJSON.message
                                    });
                                }
                            },
                            complete: function() {
                                submitBtn.prop('disabled', false).html(
                                    '<i class="fas fa-upload me-2"></i> Upload');
                            }
                        });
                    });
                })
                .fail(function(xhr) {
                    Swal.fire('Error!', 'Gagal memuat form: ' + xhr.statusText, 'error');
                });
        }

        $(document).on('submit', '#formDeleteProfesi', function(e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    $('#myModal').modal('hide');
                    window.LaravelDataTables["p_profesi-table"].ajax.reload();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data profesi berhasil dihapus.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Tidak dapat menghapus data profesi.'
                    });
                }
            });
        });

        $(document).ready(function() {
            $('#filterStatus, #filterSumberData').change(function() {
                window.LaravelDataTables["p_profesi-table"].draw();
            });
        });

        // Kirim parameter filter ke server saat DataTable ajax request
        $('#p_profesi-table').on('preXhr.dt', function(e, settings, data) {
            data.filter_status = $('#filterStatus').val();
            data.filter_sumber = $('#filterSumberData').val();
        });

        // Update export PDF link with current filter values
        function updateExportPdfLink() {
            var status = $('#filterStatus').val();
            var sumber = $('#filterSumberData').val();
            var url = new URL("{{ route('portofolio.profesi.export_pdf') }}", window.location.origin);
            if (status) {
                url.searchParams.set('filter_status', status);
            }
            if (sumber) {
                url.searchParams.set('filter_sumber', sumber);
            }
            $('#exportPdfBtn').attr('href', url.toString());
        }

        // Update export Excel link with current filter values
        function updateExportExcelLink() {
            var status = $('#filterStatus').val();
            var sumber = $('#filterSumberData').val();
            var url = new URL("{{ route('portofolio.profesi.export_excel') }}", window.location.origin);
            if (status) {
                url.searchParams.set('filter_status', status);
            }
            if (sumber) {
                url.searchParams.set('filter_sumber', sumber);
            }
            $('#exportExcelBtn').attr('href', url.toString());
        }

        $(document).ready(function() {
            updateExportPdfLink();
            updateExportExcelLink();
            $('#filterStatus, #filterSumberData').change(function() {
                updateExportPdfLink();
                updateExportExcelLink();
            });
        });
    </script>

    {{-- Chart.js --}}
    <script>
        // Prepare chart data from PHP variables
        const gelarLabels = @json($gelarLabels);
        const gelarData = @json($gelarData);
        const perguruanTinggiLabels = @json($perguruanTinggiLabels);
        const perguruanTinggiData = @json($perguruanTinggiData);

        // Colors for charts
        const chartColors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#E7E9ED', '#76A346', '#D9534F', '#5BC0DE'
        ];

        // Pie Chart 1 - Distribusi Gelar Akademik
        const ctxPie1 = document.getElementById('pieChartGelar1').getContext('2d');
        const pieChartGelar1 = new Chart(ctxPie1, {
            type: 'pie',
            data: {
                labels: gelarLabels,
                datasets: [{
                    data: gelarData,
                    backgroundColor: chartColors,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    title: {
                        display: false,
                    }
                }
            }
        });

        // Bar Chart - Asal Perguruan Tinggi
        const ctxBar = document.getElementById('barChartPerguruanTinggi').getContext('2d');
        const barChartPerguruanTinggi = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: perguruanTinggiLabels,
                datasets: [{
                    label: 'Jumlah',
                    data: perguruanTinggiData,
                    backgroundColor: chartColors,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45,
                        }
                    },
                    y: {
                        beginAtZero: true,
                        precision: 0,
                    }
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    title: {
                        display: false,
                    }
                }
            }
        });
    </script>
@endpush
