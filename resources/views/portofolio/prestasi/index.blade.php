@extends('layouts.app')

@section('title', 'Prestasi')
@section('subtitle', 'Prestasi')

@section('content_header')
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Beranda</a></li>
                <li class="breadcrumb-item active">Prestasi</li>
            </ol>
        </nav>
    </div>
@endsection

@section('content')
    <div class="container-fluid">

        <!-- Prestasi -->
        <div class="callout callout-primary shadow-sm">
            <h5>Prestasi</h5>
            <p>Pencapaian dosen dalam lomba atau penghargaan yang menunjukkan reputasi akademik atau profesional.</p>
        </div>

        {{-- DataTable --}}
        <div class="card shadow-sm">
            <div class="card-header bg-primary border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0 text-white">Daftar Prestasi</h3>
                    <div class="card-tools">
                        <a id="exportPdfBtn" class="btn btn-custom-blue me-2"
                            href="{{ route('portofolio.prestasi.export_pdf') }}">
                            <i class="fa-solid fa-file-pdf me-2"></i> Export PDF
                        </a>
                        <a id="exportExcelBtn" class="btn btn-custom-blue me-2"
                            href="{{ route('portofolio.prestasi.export_excel') }}">
                            <i class="fas fa-file-excel me-2"></i> Export Excel
                        </a>
                        @if ($isAdm || $isDos)
                            <button class="btn btn-custom-blue me-2"
                                onclick="modalAction('{{ route('portofolio.prestasi.import') }}')">
                                <i class="fa-solid fa-file-arrow-up me-2"></i> Import Data
                            </button>
                            <button onclick="modalAction('{{ route('portofolio.prestasi.create_ajax') }}')"
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
                        'id' => 'p_prestasi-table',
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
            <!-- Prestasi -->
            <div class="callout callout-primary shadow-sm">
                <h5>Chart</h5>
                <p>Chart berikut menampilkan distribusi tingkat prestasi dan tren prestasi per tahun.</p>
            </div>

            <div class="container-fluid mt-3">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary border-bottom">
                                <h5 class="card-title mb-0 text-white">Distribusi Tingkat Prestasi</h5>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body collapse">
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <canvas id="pieChartTingkatPrestasi"
                                            style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary border-bottom">
                                <h5 class="card-title mb-0 text-white">Tren Prestasi Per Tahun</h5>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body collapse">
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <canvas id="lineChartPrestasiTrend"
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

                    $(document).off('submit', '#formCreatePrestasi, #formEditPrestasi');

                    $(document).on('submit', '#formCreatePrestasi, #formEditPrestasi', function(e) {
                        e.preventDefault();
                        var form = $(this);
                        var formData = new FormData(form[0]);
                        var method = 'POST';
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
                                window.LaravelDataTables["p_prestasi-table"].ajax.reload();
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
                                    var errors = xhr.responseJSON.msgField;
                                    $.each(errors, function(field, messages) {
                                        var input = form.find('[name="' + field + '"]');
                                        input.addClass('is-invalid');
                                        input.next('.invalid-feedback').text(messages[0]);
                                    });
                                } else {
                                    $('#myModal').modal('hide');
                                    window.LaravelDataTables["p_prestasi-table"].ajax.reload();
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
                                        title: response.alert === 'success' ? 'Sukses' :
                                            'Error',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.LaravelDataTables["p_prestasi-table"].ajax
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

        $(document).on('submit', '#formDeletePrestasi', function(e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    $('#myModal').modal('hide');
                    window.LaravelDataTables["p_prestasi-table"].ajax.reload();
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data prestasi berhasil dihapus.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Tidak dapat menghapus data prestasi.'
                    });
                }
            });
        });

        $(document).ready(function() {
            $('#filterStatus, #filterSumberData').change(function() {
                window.LaravelDataTables["p_prestasi-table"].draw();
            });
        });

        $('#p_prestasi-table').on('preXhr.dt', function(e, settings, data) {
            data.filter_status = $('#filterStatus').val();
            data.filter_sumber = $('#filterSumberData').val();
        });

        function updateExportPdfLink() {
            var status = $('#filterStatus').val();
            var sumber = $('#filterSumberData').val();
            var url = new URL("{{ route('portofolio.prestasi.export_pdf') }}", window.location.origin);
            if (status) {
                url.searchParams.set('filter_status', status);
            }
            if (sumber) {
                url.searchParams.set('filter_sumber', sumber);
            }
            $('#exportPdfBtn').attr('href', url.toString());
        }

        function updateExportExcelLink() {
            var status = $('#filterStatus').val();
            var sumber = $('#filterSumberData').val();
            var url = new URL("{{ route('portofolio.prestasi.export_excel') }}", window.location.origin);
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
        const tingkatLabels = @json($tingkatLabels);
        const tingkatData = @json($tingkatData);
        const prestasiTrendLabels = @json($prestasiTrendLabels);
        const prestasiTrendData = @json($prestasiTrendData);

        // Colors for charts
        const chartColors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#E7E9ED', '#76A346', '#D9534F', '#5BC0DE'
        ];

        // Pie Chart - Distribusi Tingkat Prestasi
        const ctxPieTingkat = document.getElementById('pieChartTingkatPrestasi').getContext('2d');
        const pieChartTingkatPrestasi = new Chart(ctxPieTingkat, {
            type: 'pie',
            data: {
                labels: tingkatLabels,
                datasets: [{
                    data: tingkatData,
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

        // Line Chart - Tren Prestasi Per Tahun
        const ctxLineTrend = document.getElementById('lineChartPrestasiTrend').getContext('2d');
        const lineChartPrestasiTrend = new Chart(ctxLineTrend, {
            type: 'line',
            data: {
                labels: prestasiTrendLabels,
                datasets: [{
                    label: 'Jumlah Prestasi',
                    data: prestasiTrendData,
                    borderColor: '#36A2EB',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0,
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    title: {
                        display: false,
                    }
                }
            }
        });
    </script>
@endpush
