<div>

    <div class="spinner fullpage-loader d-none">

        <div class="spinner-border text-light" role="status" style="width: 50px; height:50px;">
            <div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin shadow-[0_0_15px_rgba(255,182,139,0.3)]"></div>
        </div>

    </div>

    <script>
        function spinner() {
            $('.spinner').removeClass('d-none');
        }
        function unspinner() {
            $('.spinner').addClass('d-none');
        }
    </script>
</div>
