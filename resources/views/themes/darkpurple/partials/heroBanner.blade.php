
<!-- home section -->
@if(isset($templates['hero'][0]) && $hero = $templates['hero'][0])
    @push('style')
        <style>
            .home-section {
                height: 100vh;
                background: url({{getFile(config('location.content.path').@$hero->templateMedia()->background_image)}});
                background-size: cover;
                background-position: center;
                position: relative;
                z-index: 1;
                overflow-x: hidden;
            }
            #google_translate_element {
                margin-bottom: 18px;
            }
            #google_translate_element .goog-te-gadget {
                font-family: inherit;
                font-size: 0;
            }
            #google_translate_element .goog-te-gadget .goog-te-combo {
                font-size: 14px;
                padding: 6px 10px;
                border-radius: 4px;
                border: 1px solid rgba(255, 255, 255, 0.4);
                background: rgba(255, 255, 255, 0.08);
                color: #fff;
            }
            .goog-te-banner-frame { display: none !important; }
            body { top: 0 !important; }
        </style>
    @endpush
    <section class="home-section">
        <div class="overlay h-100">
            <div class="container h-100">
                <div class="row h-100 align-items-center">
                    <div class="col-lg-6">
                        <div class="text-box">
                            <div id="google_translate_element"></div>
                            <h1>@lang(@$hero['description']->title) @lang(@$hero['description']->sub_title)</h1>
                            <p>@lang(@$hero['description']->short_description)</p>
                            <a class="btn-custom mt-4" href="{{@$hero->templateMedia()->button_link}}" target="_blank">
                                @lang(@$hero['description']->button_name)
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 d-none d-lg-block">
                        <div class="img-box">
                            <img src="{{getFile(config('location.content.path').@$hero->templateMedia()->image)}}" alt="@lang('hero image')" class="img-fluid img-1" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif


@push('script')
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                autoDisplay: false
            }, 'google_translate_element');
        }
    </script>
    <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
@endpush
