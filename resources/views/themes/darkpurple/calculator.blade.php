@extends($theme.'layouts.app')
@section('title', trans('Investment Profit Calculator'))

@section('content')
    <!-- Banner / Breadcrumb Area -->
    <section class="banner-section" style="padding: 100px 0 60px; background: rgba(10, 5, 25, 0.95); text-align: center;">
        <div class="container">
            <h1 class="text-white font-weight-bold">@lang('Profit Calculator')</h1>
            <p class="text-muted">@lang('Forecast your ROI and returns across all available investment tiers')</p>
        </div>
    </section>

    <!-- Interactive Calculator -->
    @include($theme.'sections.calculate-profit')

    <!-- Investment Plans Showcase -->
    @include($theme.'sections.investment')

    <!-- Why Choose Us -->
    @include($theme.'sections.why-chose-us')

    <!-- FAQ -->
    @include($theme.'sections.faq')
@endsection
