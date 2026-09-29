<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManagePlan;
use App\Models\Configure;
use Illuminate\Support\Facades\Auth;

class ChatbotController extends Controller
{
    /**
     * Handle chatbot message and return a response.
     */
    public function respond(Request $request)
    {
        $userMessage = strtolower(trim($request->input('message', '')));

        if (empty($userMessage)) {
            return response()->json(['reply' => "I didn't catch that. Could you ask again? 😊"]);
        }

        $reply = $this->resolveReply($userMessage);

        return response()->json([
            'reply' => $reply,
            'user'  => Auth::check() ? Auth::user()->fullname : null,
        ]);
    }

    /**
     * Match the user message to a reply using keyword rules.
     */
    protected function resolveReply(string $message): string
    {
        $basic = (object) config('basic');
        $site  = $basic->site_title ?? 'Matrix HYIP';
        $currency = $basic->currency_symbol ?? '$';

        // ── Greetings ──────────────────────────────────────────────────────────
        if ($this->matches($message, ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening', 'howdy'])) {
            $greet = Auth::check() ? 'Welcome back, ' . Auth::user()->firstname . '!' : 'Welcome!';
            return "{$greet} 👋 I'm the {$site} Assistant. I can help you with:\n\n"
                . "• 💰 Investment plans & profits\n"
                . "• 📥 Deposits & withdrawals\n"
                . "• 👤 Account & KYC\n"
                . "• 🔗 Referrals & bonuses\n"
                . "• 🎫 Support tickets\n\n"
                . "What can I help you with today?";
        }

        // ── Investment plans ───────────────────────────────────────────────────
        if ($this->matches($message, ['invest', 'plan', 'package', 'roi', 'return', 'profit', 'earn', 'yield'])) {
            $plans = ManagePlan::where('status', 1)->get();
            if ($plans->isEmpty()) {
                return "Our investment plans are being updated. Please check the **Plans** page for the latest offerings.";
            }
            $planList = $plans->map(function ($p) use ($currency) {
                $rate   = $p->profit . ($p->profit_type == 1 ? '%' : ' ' . $currency);
                $period = $p->schedule . 'h cycle';
                $cap    = $p->is_capital_back ? '✅ Capital returned' : '❌ Capital included in profit';
                return "• **{$p->name}**: {$rate} per {$period} | Min: {$currency}{$p->minimum_amount} | {$cap}";
            })->implode("\n");

            return "📊 **Current Investment Plans:**\n\n{$planList}\n\n"
                . "👉 Use our [Profit Calculator](" . route('calculator') . ") to forecast your exact returns before investing!";
        }

        // ── Calculator ─────────────────────────────────────────────────────────
        if ($this->matches($message, ['calculator', 'calculate', 'forecast', 'simulate', 'estimate'])) {
            return "📈 **Profit Calculator**\n\n"
                . "Use our interactive calculator to simulate your returns:\n"
                . "👉 " . route('calculator') . "\n\n"
                . "Just select a plan and enter your investment amount — you'll instantly see per-cycle earnings, total return, and net ROI %.";
        }

        // ── Deposit & Funding ──────────────────────────────────────────────────
        if ($this->matches($message, ['deposit', 'add fund', 'fund', 'top up', 'topup', 'recharge', 'payment method'])) {
            $methods = "We support multiple payment gateways including cryptocurrencies and local banking options.";
            return "💳 **Making a Deposit:**\n\n"
                . "1. Log in to your account\n"
                . "2. Go to **Add Fund** in your dashboard\n"
                . "3. Choose your preferred payment gateway\n"
                . "4. Enter the amount and complete payment\n\n"
                . "{$methods}\n\n"
                . "Deposits are typically confirmed within minutes. Need help? Open a support ticket.";
        }

        // ── Withdrawal / Payout ────────────────────────────────────────────────
        if ($this->matches($message, ['withdraw', 'payout', 'cashout', 'cash out', 'take money', 'get money'])) {
            return "💸 **Withdrawal Process:**\n\n"
                . "1. Go to **Payout** in your dashboard\n"
                . "2. Select your registered payout method\n"
                . "3. Enter the amount and submit\n"
                . "4. Admin processes payouts typically within **24–48 hours**\n\n"
                . "🔐 Make sure your KYC is verified for faster processing.\n"
                . "⚠️ Minimum withdrawal amounts vary by method — check the Payout page.";
        }

        // ── Wallet Exchange ────────────────────────────────────────────────────
        if ($this->matches($message, ['wallet exchange', 'exchange wallet', 'transfer wallet', 'interest to main', 'main to interest', 'internal transfer'])) {
            return "🔄 **Wallet Exchange (Internal Transfer):**\n\n"
                . "You can transfer funds between your:\n"
                . "• **Main Balance** (Deposit Wallet) → **Interest Balance**\n"
                . "• **Interest Balance** (Profit Wallet) → **Main Balance**\n\n"
                . "This is useful to **reinvest your profits** directly without going through external gateways.\n"
                . "👉 Find it at: Dashboard → Wallet Exchange";
        }

        // ── Registration ───────────────────────────────────────────────────────
        if ($this->matches($message, ['register', 'sign up', 'create account', 'open account', 'join'])) {
            return "📝 **Creating Your Account:**\n\n"
                . "1. Click **Register** on the homepage\n"
                . "2. Fill in your details (name, email, username, password)\n"
                . "3. Enter a referral code if you have one\n"
                . "4. Verify your email address\n"
                . "5. Complete KYC if required\n\n"
                . "Registration is free and takes less than 2 minutes! 🚀";
        }

        // ── KYC / Verification ─────────────────────────────────────────────────
        if ($this->matches($message, ['kyc', 'verify', 'verification', 'identity', 'document', 'id proof'])) {
            return "🔍 **KYC Verification:**\n\n"
                . "KYC (Know Your Customer) verification may be required to:\n"
                . "• Make deposits or withdrawals\n"
                . "• Access full platform features\n\n"
                . "**To complete KYC:**\n"
                . "1. Go to your **Profile** page\n"
                . "2. Click **Identity Verification**\n"
                . "3. Upload the required documents\n"
                . "4. Wait for admin approval (usually within 24 hours)";
        }

        // ── Referral / Affiliate ───────────────────────────────────────────────
        if ($this->matches($message, ['referral', 'affiliate', 'refer', 'invite', 'commission', 'bonus'])) {
            return "🔗 **Referral Program:**\n\n"
                . "Earn commissions by inviting others!\n\n"
                . "• Find your unique referral link on the **Referral** page in your dashboard\n"
                . "• Share it with friends\n"
                . "• Earn **deposit bonuses** and **investment bonuses** when they transact\n\n"
                . "💡 Commission rates are set by admin and may vary. Check the Referral page for your current bonus amounts.";
        }

        // ── Support Ticket ─────────────────────────────────────────────────────
        if ($this->matches($message, ['support', 'ticket', 'help', 'problem', 'issue', 'contact', 'complaint', 'stuck'])) {
            $ticketLink = Auth::check() ? route('user.ticket.list') : route('login');
            return "🎫 **Support Tickets:**\n\n"
                . "Our support team is ready to help you!\n\n"
                . "1. Log in to your account\n"
                . "2. Go to **Support Tickets**\n"
                . "3. Click **New Ticket**\n"
                . "4. Describe your issue and submit\n\n"
                . "Our team typically responds within **24 hours**. 🕐\n"
                . "👉 " . $ticketLink;
        }

        // ── Security / 2FA ─────────────────────────────────────────────────────
        if ($this->matches($message, ['security', '2fa', 'two factor', 'google auth', 'protect account', 'authenticator'])) {
            return "🔐 **Account Security:**\n\n"
                . "We strongly recommend enabling **Two-Factor Authentication (2FA)**:\n\n"
                . "1. Go to **Dashboard** → **2-Step Security**\n"
                . "2. Download Google Authenticator on your phone\n"
                . "3. Scan the QR code shown\n"
                . "4. Enter the 6-digit code to confirm\n\n"
                . "2FA adds an extra layer of protection against unauthorized access. 🛡️";
        }

        // ── Password ───────────────────────────────────────────────────────────
        if ($this->matches($message, ['password', 'forgot password', 'reset password', 'change password'])) {
            return "🔑 **Password Help:**\n\n"
                . "**Forgot password?** → Use the 'Forgot Password' link on the login page. We'll email you a reset link.\n\n"
                . "**Change password?** → Go to Profile → Change Password, enter your current and new password.\n\n"
                . "📧 Make sure your email is correct to receive reset emails.";
        }

        // ── Minimum / Maximum amounts ──────────────────────────────────────────
        if ($this->matches($message, ['minimum', 'maximum', 'min', 'max', 'how much', 'amount limit'])) {
            $plans = ManagePlan::where('status', 1)->get();
            if ($plans->isEmpty()) {
                return "Please check the **Plans** page for current minimum and maximum investment amounts.";
            }
            $summary = $plans->map(function ($p) use ($currency) {
                return "• **{$p->name}**: Min {$currency}{$p->minimum_amount} — Max {$currency}{$p->maximum_amount}";
            })->implode("\n");
            return "💵 **Investment Limits:**\n\n{$summary}\n\n"
                . "Fixed-amount plans require an exact deposit. Others accept any amount within the range.";
        }

        // ── Lifetime plans ─────────────────────────────────────────────────────
        if ($this->matches($message, ['lifetime', 'forever', 'unlimited', 'no end'])) {
            $lifetimePlans = ManagePlan::where('status', 1)->where('is_lifetime', 1)->get();
            if ($lifetimePlans->isEmpty()) {
                return "We don't currently have lifetime (unlimited-cycle) plans. Check our Plans page for available options.";
            }
            $names = $lifetimePlans->pluck('name')->implode(', ');
            return "♾️ **Lifetime Plans** pay profits indefinitely until you withdraw your capital:\n\n• {$names}\n\n"
                . "These plans keep generating returns every cycle without expiring!";
        }

        // ── About the platform ─────────────────────────────────────────────────
        if ($this->matches($message, ['about', 'who are you', 'what is this', 'what is ' . strtolower($site), 'platform', 'company'])) {
            return "🏢 **About {$site}:**\n\n"
                . "{$site} is a trusted HYIP investment platform that allows you to grow your wealth through diversified, professionally managed investment plans.\n\n"
                . "✅ Transparent profit cycles\n"
                . "✅ Multiple payment gateways\n"
                . "✅ Referral affiliate program\n"
                . "✅ 24/7 automated profit payouts\n"
                . "✅ Dedicated support team";
        }

        // ── Transaction History ────────────────────────────────────────────────
        if ($this->matches($message, ['transaction', 'history', 'statement', 'record', 'log'])) {
            return "📋 **Transaction History:**\n\n"
                . "View all your financial activity:\n"
                . "• Dashboard → **Transaction History** (all credits/debits)\n"
                . "• Dashboard → **Fund History** (deposits only)\n"
                . "• Dashboard → **Payout History** (withdrawals only)\n"
                . "• Dashboard → **Invest History** (investment plans)\n\n"
                . "You can also filter by date and transaction ID.";
        }

        // ── Profit / ROI questions ─────────────────────────────────────────────
        if ($this->matches($message, ['when', 'how long', 'pay out', 'cycle', 'schedule', 'interval', 'frequency'])) {
            return "⏱️ **Profit Distribution:**\n\n"
                . "Profits are distributed automatically based on each plan's schedule:\n"
                . "• Plans run in **hourly cycles** (e.g., every 24h, 48h, etc.)\n"
                . "• Your profits are credited to your **Interest Balance**\n"
                . "• You can withdraw from Interest Balance or exchange to Main Balance\n\n"
                . "Use the Profit Calculator to see your plan's exact schedule!";
        }

        // ── FAQ ────────────────────────────────────────────────────────────────
        if ($this->matches($message, ['faq', 'question', 'common', 'frequently asked'])) {
            return "❓ **Frequently Asked Questions:**\n\n"
                . "Visit our FAQ page for detailed answers: " . route('faq') . "\n\n"
                . "Or ask me directly — I know most of the answers! 😄";
        }

        // ── Default fallback ───────────────────────────────────────────────────
        return "🤖 I'm not sure I understand that. Here's what I can help with:\n\n"
            . "• Type **plans** — to see investment plans\n"
            . "• Type **deposit** — deposit help\n"
            . "• Type **withdraw** — withdrawal process\n"
            . "• Type **referral** — referral/affiliate info\n"
            . "• Type **kyc** — verification help\n"
            . "• Type **support** — to open a ticket\n"
            . "• Type **calculator** — profit calculator\n\n"
            . "Or visit our [FAQ page](" . route('faq') . ") for more answers. 💬";
    }

    /**
     * Check if message contains any of the given keywords.
     */
    protected function matches(string $message, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }
        return false;
    }
}
