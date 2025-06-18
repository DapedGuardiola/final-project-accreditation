<form id="formEditKriteria" method="POST"
    action="{{ route('kriteria.update_ajax', ['no_kriteria' => $kriteria->no_kriteria, 'id_user' => $kriteria->id_user]) }}">
    @csrf
    @method('PUT')
    <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Edit Kriteria</h5>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="form-group">
            <label for="no_kriteria">No Kriteria</label>
            <input type="text" class="form-control" id="no_kriteria" name="no_kriteria"
                value="Kriteria {{ $kriteria->no_kriteria }}" readonly>
        </div>
        <div class="form-group">
            <label for="judul">Judul Kriteria</label>
            <input type="text" class="form-control" id="judul" name="judul" value="{{ $judul }}"
                required>
        </div>
        <div class="mb-3">
            <label for="id_user" class="form-label">Nama User (Maksimal 2 User)</label>
            <div class="input-group">
                <select class="form-select" id="id_user" name="id_user"
                    style="width: calc(98% - 40px); margin-right: 13px;">
                    <option value="">Pilih User</option>

                </select>
                <button type="button" class="btn btn-primary" id="addNewUser">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            <div id="selectedUsers" class="mt-2"></div>
            <div class="invalid-feedback" id="error_id_user"></div>
        </div>
        <div class="selected-users-container mb-3">
        </div>
        <div class="alert alert-info mt-2"><i class="fas fa-info-circle"></i> Selalu tekan <strong>update</strong>
            ketika ada perubahan judul atau user</div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times me-1"></i>
                Batal</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update</button>
        </div>
</form>

<script>
    $(document).ready(function() {
        $.ajax({
            url: '{{ route('kriteria.get_users') }}',
            type: 'GET',
            success: function(response) {
                let options = '<option value="">Pilih User</option>';
                if (response && response.length > 0) {
                    response.forEach(function(profile_user) {
                        options +=
                            `<option value="${profile_user.id_user}">${profile_user.nama_lengkap}</option>`;
                    });
                }
                if (response.status === false) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal...',
                        text: response.message,
                    });
                }
                $('#id_user').html(options);
            },
            error: function(xhr, status, error) {
                console.error('Error loading users:', error);
                $('#id_user').html('<option value="">Gagal memuat user</option>');
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal...',
                    text: 'Terjadi kesalahan saat memuat daftar user.',
                });
            }
        });

        let selectedUsers = {!! json_encode(
            $selectedUsers->map(function ($u) {
                return ['id' => $u->id_user, 'name' => $u->nama_lengkap];
            }),
        ) !!};

        // Render initial selected users on page load
        renderSelectedUsers();

        $('#addNewUser').on('click', function() {
            const userId = $('#id_user').val();
            const userName = $('#id_user option:selected').text();

            if (!userId) {
                $('#id_user').val('');
                $('#id_user').focus();
                return;
            }

            // Check for duplicate user
            if (selectedUsers.some(u => u.id === userId)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal...',
                    text: 'User sudah dipilih.',
                });
                return;
            }

            // Check max 2 users
            if (selectedUsers.length >= 2) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal...',
                    text: 'Maksimal 2 user.',
                });
                return;
            }

            selectedUsers.push({
                id: userId,
                name: userName
            });
            renderSelectedUsers();
            $('#id_user').val('');
        });

        function renderSelectedUsers() {
            let html = '';
            selectedUsers.forEach((user, idx) => {
                html += `
                    <div class="input-group mb-1">
                        <input type="text" class="form-control rounded" value="${user.name}" readonly style="width: calc(80% - 40px); margin-right: 14px;">
                        <input type="hidden" name="selected_users[]" value="${user.id}">
                        <button type="button" class="btn btn-danger removeUser" data-id="${user.id}" style="margin-right: 2px;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;
            });
            $('#selectedUsers').html(html);
        }

        $('#selectedUsers').on('click', '.removeUser', function() {
            const userId = $(this).data('id').toString();
            const noKriteria = $('#no_kriteria').val().replace('Kriteria ', '').trim();

            // Removed confirmation dialog as per user request

            $.ajax({
                url: `/manage-kriteria/delete_user_ajax/${noKriteria}/${userId}`,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil...',
                            text: response.message,
                        });
                        // Reload user list from server after deletion
                        $.ajax({
                            url: `/manage-kriteria/get-users-by-kriteria/${noKriteria}`,
                            type: 'GET',
                            success: function(response) {
                                if (response && response.length > 0) {
                                    selectedUsers = response.map(function(
                                        user) {
                                        return {
                                            id: user.id,
                                            name: user.name
                                        };
                                    });
                                } else {
                                    selectedUsers = [];
                                }
                                renderSelectedUsers();
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal...',
                                    text: 'Gagal memuat daftar user setelah penghapusan.',
                                });
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal...',
                            text: 'Gagal menghapus user: ' + response.message,
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal...',
                        text: 'Terjadi kesalahan saat menghapus user.',
                    });
                }
            });
        });

        $('#formEditKriteria').off('submit').on('submit', function(e) {
            if ($('#id_user').val() !== '') {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal...',
                    text: 'Klik tombol tambah (+) untuk memasukkan user ke daftar!',
                });
                e.preventDefault();
                return false;
            }
            setTimeout(function() {
                selectedUsers = [];
                renderSelectedUsers();
            }, 500);
        });

        $(document).on('hidden.bs.modal', '.modal', function() {
            selectedUsers = [];
            renderSelectedUsers();
        });
    });
</script>
