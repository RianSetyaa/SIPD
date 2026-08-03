</div><!-- tutup main-content -->
<?php if (is_logged_in()): ?>
<div class="footer-text">
    &copy; <?= date('Y') ?> SIPD Penatausahaan &mdash; Aplikasi Pembelajaran Mahasiswa
</div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
    // Inisialisasi DataTable untuk tabel dengan class .datatable
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.datatable').forEach(function(table) {
            $(table).DataTable({
                "language": {
                    "lengthMenu": "Tampilkan _MENU_ data per halaman",
                    "zeroRecords": "Tidak ada data ditemukan",
                    "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    "infoEmpty": "Tidak ada data",
                    "infoFiltered": "(difilter dari _MAX_ total data)",
                    "search": "Cari:",
                    "paginate": { "first": "Awal", "last": "Akhir", "next": "Berikut", "previous": "Sebelum" }
                }
            });
        });
    });
</script>
</body>
</html>
