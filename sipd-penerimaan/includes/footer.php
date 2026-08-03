</div><!-- /main-content -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function(){
  $('table.data-table').DataTable({ language: {
    "search":"Cari:","lengthMenu":"Tampil _MENU_","info":"Menampilkan _START_-_END_ dari _TOTAL_",
    "zeroRecords":"Data tidak ditemukan","paginate":{"previous":"&laquo;","next":"&raquo;"}
  }});
});
</script>
</body>
</html>
