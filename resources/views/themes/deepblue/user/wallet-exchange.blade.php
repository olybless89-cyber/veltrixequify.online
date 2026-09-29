@extends($theme.'layouts.user')
@section('title', __($page_title))

@section('content')
<div class="container-fluid">
    <div class="main row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">@lang('Wallet Exchange & Internal Transfer')</h3>
            </div>

            <!-- Balance Overview Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-sm-12">
                    <div class="card bg-dark text-white border-0 shadow-sm p-3 rounded" style="background: rgba(255, 255, 255, 0.05); border-left: 4px solid #ff5400 !important;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small">@lang('Main Balance (Deposit Wallet)')</span>
                                <h4 class="mb-0 font-weight-bold text-white mt-1">{{ config('basic.currency_symbol') }}{{ getAmount($user->balance) }}</h4>
                            </div>
                            <div class="rounded-circle p-3" style="background: rgba(255, 84, 0, 0.15); color: #ff5400;">
                                <i class="fa fa-wallet fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-sm-12">
                    <div class="card bg-dark text-white border-0 shadow-sm p-3 rounded" style="background: rgba(255, 255, 255, 0.05); border-left: 4px solid #28a745 !important;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-muted small">@lang('Interest Balance (Profit Wallet)')</span>
                                <h4 class="mb-0 font-weight-bold text-white mt-1">{{ config('basic.currency_symbol') }}{{ getAmount($user->interest_balance) }}</h4>
                            </div>
                            <div class="rounded-circle p-3" style="background: rgba(40, 167, 69, 0.15); color: #28a745;">
                                <i class="fa fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exchange Form -->
            <div class="search-bar">
                <form action="{{ route('user.wallet.exchange') }}" method="post">
                    @csrf
                    <div class="row g-3">
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="input-box">
                                <label class="darkblue-text-bold">@lang('From Wallet')</label>
                                <select name="from_wallet" id="from_wallet" class="form-control" required onchange="handleWalletChange('from')">
                                    <option value="" disabled selected class="text-white">@lang('Select Source Wallet')</option>
                                    <option value="interest_balance" class="text-white" {{ old('from_wallet') == 'interest_balance' ? 'selected' : '' }}>
                                        @lang('Interest Balance') ({{ config('basic.currency_symbol') }}{{ getAmount($user->interest_balance) }})
                                    </option>
                                    <option value="balance" class="text-white" {{ old('from_wallet') == 'balance' ? 'selected' : '' }}>
                                        @lang('Main Balance') ({{ config('basic.currency_symbol') }}{{ getAmount($user->balance) }})
                                    </option>
                                </select>
                                @error('from_wallet')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="input-box">
                                <label class="darkblue-text-bold">@lang('To Wallet')</label>
                                <select name="to_wallet" id="to_wallet" class="form-control" required onchange="handleWalletChange('to')">
                                    <option value="" disabled selected class="text-white">@lang('Select Destination Wallet')</option>
                                    <option value="balance" class="text-white" {{ old('to_wallet') == 'balance' ? 'selected' : '' }}>
                                        @lang('Main Balance') ({{ config('basic.currency_symbol') }}{{ getAmount($user->balance) }})
                                    </option>
                                    <option value="interest_balance" class="text-white" {{ old('to_wallet') == 'interest_balance' ? 'selected' : '' }}>
                                        @lang('Interest Balance') ({{ config('basic.currency_symbol') }}{{ getAmount($user->interest_balance) }})
                                    </option>
                                </select>
                                @error('to_wallet')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="input-box">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="amount" class="darkblue-text-bold mb-0">@lang('Exchange Amount')</label>
                                    <a href="javascript:void(0)" onclick="setMaxAmount()" class="text-warning small font-weight-bold">
                                        <i class="fa fa-arrow-circle-up"></i> @lang('Max Available')
                                    </a>
                                </div>
                                <input
                                    type="text"
                                    id="amount"
                                    class="form-control"
                                    name="amount"
                                    value="{{ old('amount') }}"
                                    placeholder="@lang('0.00')"
                                    required
                                    onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')"
                                />
                                @error('amount')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <div class="input-box">
                                <label for="password" class="darkblue-text-bold">@lang('Your Account Password')</label>
                                <input
                                    type="password"
                                    id="password"
                                    class="form-control"
                                    name="password"
                                    placeholder="@lang('Enter password to confirm')"
                                    required
                                />
                                @error('password')
                                <div class="error text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12 mt-4">
                            <div class="alert alert-dark" style="background: rgba(255, 255, 255, 0.05); border-left: 3px solid #ff5400;">
                                <i class="fa fa-info-circle text-warning mr-2"></i>
                                @lang('Tip: Converting your Interest Balance to Main Balance allows you to instantly reinvest in higher yield investment packages!')
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-4 col-sm-12">
                            <button class="btn-custom w-100" type="submit">
                                <i class="fa fa-sync-alt mr-2"></i> @lang('Exchange Now')
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    'use strict';
    const mainBalance = {{ (float)$user->balance }};
    const interestBalance = {{ (float)$user->interest_balance }};

    function handleWalletChange(changed) {
        const fromSelect = document.getElementById('from_wallet');
        const toSelect = document.getElementById('to_wallet');

        if (changed === 'from') {
            if (fromSelect.value === 'balance') {
                toSelect.value = 'interest_balance';
            } else if (fromSelect.value === 'interest_balance') {
                toSelect.value = 'balance';
            }
        } else {
            if (toSelect.value === 'balance') {
                fromSelect.value = 'interest_balance';
            } else if (toSelect.value === 'interest_balance') {
                fromSelect.value = 'balance';
            }
        }
    }

    function setMaxAmount() {
        const fromSelect = document.getElementById('from_wallet').value;
        const amountInput = document.getElementById('amount');
        if (fromSelect === 'interest_balance') {
            amountInput.value = interestBalance.toFixed(2);
        } else if (fromSelect === 'balance') {
            amountInput.value = mainBalance.toFixed(2);
        } else {
            document.getElementById('from_wallet').value = 'interest_balance';
            document.getElementById('to_wallet').value = 'balance';
            amountInput.value = interestBalance.toFixed(2);
        }
    }
</script>
@endpush
