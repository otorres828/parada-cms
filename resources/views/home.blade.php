{{-- --- SEO & Metadata Configuration --- --}}
{{-- These sections inject data into the head of the layout.master --}}

@section('title', 'Parada')

@section('meta-title', 'Parada')

@section('meta-description', 'Parada')

@section('meta-keywords', 'Parada')

@section('header-assets')

    @parent

    <script type="text/javascript" src="{{ asset('assets/js/jquery-3.6.0.min.js') }}"></script>

@endsection

<x-layout.site>

    <div class="font-body-md text-body-md antialiased">

        <x-site.home.header/>

        <x-site.home.hero/>

        <x-site.home.footer/>

    </div>

</x-layout.site>

@section('footer-assets')
    @parent

@endsection
