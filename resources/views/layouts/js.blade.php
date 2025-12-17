<!-- CoreUI and necessary plugins-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>
<script src="vendors/simplebar/js/simplebar.min.js"></script>
<script>
    const header = document.querySelector('header.header');

    document.addEventListener('scroll', () => {
        if (header) {
            header.classList.toggle('shadow-sm', document.documentElement.scrollTop > 0);
        }
    });
</script>
<!-- Plugins and scripts required by this view-->
<script src="vendors/chart.js/js/chart.umd.js"></script>
<script src="vendors/@coreui/chartjs/js/coreui-chartjs.js"></script>
<script src="vendors/@coreui/utils/js/index.js"></script>
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<!-- <script src="js/main.js"></script> -->
@stack('scripts')
<script></script>
