<!-- Calculate Profit Section -->
<section class="calculate-profit-section py-5" style="background: rgba(18, 11, 41, 0.95);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center mb-5">
                <div class="header-text">
                    <h5 class="text-uppercase" style="color: #ff5400; letter-spacing: 2px;">@lang('Calculate Your Return')</h5>
                    <h2 class="text-white font-weight-bold">@lang('Investment Profit Calculator')</h2>
                    <p class="text-muted">@lang('Select your preferred investment plan and enter your desired capital to calculate your projected return.')</p>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card p-4 p-md-5 border-0 shadow-lg" style="background: rgba(255, 255, 255, 0.04); border-radius: 20px; backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.08);">
                    <div class="row g-4 align-items-center">
                        <!-- Inputs -->
                        <div class="col-lg-6">
                            <div class="mb-4">
                                <label class="form-label text-white font-weight-bold mb-2">@lang('Select Investment Plan')</label>
                                <select id="calc_plan_id" class="form-control" style="background: rgba(255, 255, 255, 0.08); color: #fff; border: 1px solid rgba(255, 255, 255, 0.15); height: 50px; border-radius: 10px;" onchange="updateCalcData()">
                                    @if(isset($plans))
                                        @foreach($plans as $p)
                                            <option value="{{ $p->id }}" 
                                                    data-fixed="{{ $p->fixed_amount }}" 
                                                    data-min="{{ $p->minimum_amount }}" 
                                                    data-max="{{ $p->maximum_amount }}"
                                                    data-profit="{{ $p->profit }}"
                                                    data-profittype="{{ $p->profit_type }}"
                                                    data-lifetime="{{ $p->is_lifetime }}"
                                                    data-repeatable="{{ $p->repeatable }}"
                                                    data-capitalback="{{ $p->is_capital_back }}"
                                                    data-schedule="{{ $p->schedule }}"
                                                    class="text-dark">
                                                {{ $p->name }} ({{ $p->price }})
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label text-white font-weight-bold mb-0">@lang('Investment Amount') ({{ config('basic.currency_symbol', '$') }})</label>
                                    <small id="calc_limit_text" class="text-warning"></small>
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text border-0" style="background: #ff5400; color: #fff; border-radius: 10px 0 0 10px; font-weight: bold;">
                                        {{ config('basic.currency_symbol', '$') }}
                                    </span>
                                    <input type="number" 
                                           id="calc_amount" 
                                           class="form-control text-white" 
                                           style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); height: 50px; border-radius: 0 10px 10px 0;" 
                                           placeholder="100.00" 
                                           value="100" 
                                           oninput="calculateReturns()">
                                </div>
                            </div>

                            <div class="mt-4">
                                <a href="{{ route('user.payment') }}" class="btn-custom w-100 text-center d-block py-3 text-white font-weight-bold" style="border-radius: 10px; text-decoration: none;">
                                    <i class="fa fa-rocket mr-2"></i> @lang('Invest in this Plan Now')
                                </a>
                            </div>
                        </div>

                        <!-- Results Card -->
                        <div class="col-lg-6">
                            <div class="p-4 rounded-3" style="background: linear-gradient(135deg, rgba(255, 84, 0, 0.15), rgba(103, 119, 239, 0.15)); border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 15px;">
                                <h5 class="text-white mb-4 pb-2 border-bottom border-secondary d-flex justify-content-between">
                                    <span>@lang('Projected Earnings')</span>
                                    <span id="calc_roi_badge" class="badge" style="background: #ff5400;">0% ROI</span>
                                </h5>

                                <div class="d-flex justify-content-between mb-3 text-white">
                                    <span class="text-muted">@lang('Profit Rate'):</span>
                                    <span id="calc_profit_rate" class="font-weight-bold text-white">0%</span>
                                </div>

                                <div class="d-flex justify-content-between mb-3 text-white">
                                    <span class="text-muted">@lang('Profit Per Cycle'):</span>
                                    <span id="calc_profit_cycle" class="font-weight-bold text-success">{{ config('basic.currency_symbol') }}0.00</span>
                                </div>

                                <div class="d-flex justify-content-between mb-3 text-white">
                                    <span class="text-muted">@lang('Repeatable Cycles'):</span>
                                    <span id="calc_cycles" class="font-weight-bold text-white">0 times</span>
                                </div>

                                <div class="d-flex justify-content-between mb-3 text-white">
                                    <span class="text-muted">@lang('Capital Returned'):</span>
                                    <span id="calc_capital_back" class="badge badge-success">@lang('Yes')</span>
                                </div>

                                <hr style="border-color: rgba(255, 255, 255, 0.15);">

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <small class="text-muted text-uppercase d-block">@lang('Total Gross Return')</small>
                                        <h3 id="calc_total_return" class="text-white font-weight-bold mb-0" style="color: #ff5400 !important;">
                                            {{ config('basic.currency_symbol') }}0.00
                                        </h3>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted text-uppercase d-block">@lang('Net Profit')</small>
                                        <h4 id="calc_net_profit" class="text-success font-weight-bold mb-0">
                                            +{{ config('basic.currency_symbol') }}0.00
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('script')
<script>
    'use strict';
    const currency = "{{ config('basic.currency_symbol', '$') }}";

    function updateCalcData() {
        const select = document.getElementById('calc_plan_id');
        if (!select || !select.options.length) return;
        const selected = select.options[select.selectedIndex];
        const fixed = parseFloat(selected.dataset.fixed || 0);
        const min = parseFloat(selected.dataset.min || 0);
        const max = parseFloat(selected.dataset.max || 0);
        const amountInput = document.getElementById('calc_amount');
        const limitText = document.getElementById('calc_limit_text');

        if (fixed > 0) {
            amountInput.value = fixed.toFixed(2);
            amountInput.readOnly = true;
            limitText.innerText = "@lang('Fixed'): " + currency + fixed;
        } else {
            amountInput.readOnly = false;
            amountInput.value = min > 0 ? min.toFixed(2) : 100;
            limitText.innerText = "@lang('Limit'): " + currency + min + " - " + currency + max;
        }
        calculateReturns();
    }

    function calculateReturns() {
        const select = document.getElementById('calc_plan_id');
        if (!select || !select.options.length) return;
        const selected = select.options[select.selectedIndex];

        const amount = parseFloat(document.getElementById('calc_amount').value) || 0;
        const profit = parseFloat(selected.dataset.profit || 0);
        const profitType = parseInt(selected.dataset.profittype || 1);
        const isLifetime = parseInt(selected.dataset.lifetime || 0);
        const repeatable = parseInt(selected.dataset.repeatable || 1);
        const capitalBack = parseInt(selected.dataset.capitalback || 0);

        let perCycle = (profitType === 1) ? (amount * profit / 100) : profit;
        let totalProfit = (isLifetime === 1) ? perCycle * 365 : (perCycle * repeatable);
        let capitalReturn = (capitalBack === 1) ? amount : 0;
        let grossReturn = totalProfit + capitalReturn;
        let netProfit = totalProfit;
        let roiPercent = (amount > 0) ? ((totalProfit / amount) * 100).toFixed(1) : 0;

        document.getElementById('calc_profit_rate').innerText = profit + (profitType === 1 ? '%' : ' ' + currency);
        document.getElementById('calc_profit_cycle').innerText = currency + perCycle.toFixed(2);
        document.getElementById('calc_cycles').innerText = (isLifetime === 1) ? "@lang('Lifetime (365 days est.)')" : (repeatable + " @lang('times')");
        document.getElementById('calc_capital_back').innerText = (capitalBack === 1) ? "@lang('Yes')" : "@lang('No')";
        document.getElementById('calc_capital_back').className = (capitalBack === 1) ? "badge bg-success" : "badge bg-secondary";

        document.getElementById('calc_roi_badge').innerText = roiPercent + "% ROI";
        document.getElementById('calc_total_return').innerText = currency + grossReturn.toFixed(2);
        document.getElementById('calc_net_profit').innerText = "+" + currency + netProfit.toFixed(2);
    }

    document.addEventListener("DOMContentLoaded", function () {
        updateCalcData();
    });
</script>
@endpush
